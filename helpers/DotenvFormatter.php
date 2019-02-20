<?php namespace geoffry304\enveditor\helpers;

use geoffry304\enveditor\interfaces\DotenvFormatter as DotenvFormatterInterface;
use geoffry304\enveditor\exceptions\InvalidValueException;


class DotenvFormatter implements DotenvFormatterInterface
{

    public function formatKey($key)
    {
        return trim(str_replace(array('export ', '\'', '"', ' '), '', $key));
    }


    public function formatValue($value, $forceQuotes = false)
    {
        if (!$forceQuotes && !preg_match('/[#\s"\'\\\\]|\\\\n/', $value)) {
            return $value;
        }

        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\"', $value);
        $value = "\"{$value}\"";

        return $value;
    }


    public function formatSetterLine($key, $value = null, $comment = null, $export = false)
    {
        $forceQuotes = (strlen($comment) > 0 && strlen(trim($value)) == 0);
        $value = $this->formatValue($value, $forceQuotes);
        $key = $this->formatKey($key);
        $comment = $this->formatComment($comment);
        $export = $export ? 'export ' : '';

        $line = "{$export}{$key}={$value}{$comment}";

        return $line;
    }


    public function formatComment($comment)
    {
        $comment = trim($comment, '# ');

        return (strlen($comment) > 0) ? " # {$comment}" : "";
    }


    public function normaliseKey($key)
    {
        return $this->formatKey($key);
    }


    public function normaliseValue($value, $quote = '')
    {
        if (strlen($quote) == 0) {
            return trim($value);
        }

        $value = str_replace("\\$quote", $quote, $value);
        $value = str_replace('\\\\', '\\', $value);
        return $value;
    }


    public function normaliseComment($comment)
    {
        return trim($comment, '# ');
    }


    public function parseLine($line)
    {
        $output = [
            'type' => null,
            'export' => null,
            'key' => null,
            'value' => null,
            'comment' => null,
        ];

        if ($this->isEmpty($line)) {
            $output['type'] = 'empty';
        } elseif ($this->isComment($line)) {
            $output['type'] = 'comment';
            $output['comment'] = $this->normaliseComment($line);
        } elseif ($this->looksLikeSetter($line)) {
            list($key, $data) = array_map('trim', explode('=', $line, 2));
            $export = $this->isExportKey($key);
            $key = $this->normaliseKey($key);
            $data = trim($data);

            if (!$data && $data !== '0') {
                $value = '';
                $comment = '';
            } else {
                if ($this->beginsWithAQuote($data)) { // data starts with a quote
                    $quote = $data[0];
                    $regexPattern = sprintf(
                        '/^
                        %1$s          # match a quote at the start of the data
                        (             # capturing sub-pattern used
                         (?:          # we do not need to capture this
                          [^%1$s\\\\] # any character other than a quote or backslash
                          |\\\\\\\\   # or two backslashes together
                          |\\\\%1$s   # or an escaped quote e.g \"
                         )*           # as many characters that match the previous rules
                        )             # end of the capturing sub-pattern
                        %1$s          # and the closing quote
                        (.*)$         # and discard any string after the closing quote
                        /mx',
                        $quote
                    );

                    $value = preg_replace($regexPattern, '$1', $data);
                    $extant = preg_replace($regexPattern, '$2', $data);

                    $value = $this->normaliseValue($value, $quote);
                    $comment = ($this->isComment($extant)) ? $this->normaliseComment($extant) : '';
                } else {
                    $parts = explode(' #', $data, 2);

                    $value = $this->normaliseValue($parts[0]);
                    $comment = (isset($parts[1])) ? $this->normaliseComment($parts[1]) : '';

                    // Unquoted values cannot contain whitespace
                    if (preg_match('/\s+/', $value) > 0) {
                        throw new InvalidValueException('Dotenv values containing spaces must be surrounded by quotes.');
                    }
                }
            }

            $output['type'] = 'setter';
            $output['export'] = $export;
            $output['key'] = $key;
            $output['value'] = $value;
            $output['comment'] = $comment;
        } else {
            $output['type'] = 'unknown';
        }

        return $output;
    }


    protected function isEmpty($line)
    {
        return strlen(trim($line)) == 0;
    }


    protected function isComment($line)
    {
        return strpos(ltrim($line), '#') === 0;
    }


    protected function looksLikeSetter($line)
    {
        return strpos($line, '=') !== false && strpos($line, '=') !== 0;
    }


    protected function isExportKey($key)
    {
        $pattern = '/^export\h.*$/';
        if (preg_match($pattern, trim($key))) {
            return true;
        }
        return false;
    }


    protected function beginsWithAQuote($data)
    {
        return strpbrk($data[0], '"\'') !== false;
    }
}
