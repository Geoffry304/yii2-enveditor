# yii2-enveditor

[![Latest Version](https://img.shields.io/github/tag/geoffry304/yii2-enveditor.svg?style=flat-square&label=release)](https://github.com/geoffry304/yii2-enveditor/tags)
[![Total Downloads](https://img.shields.io/packagist/dt/geoffry304/yii2-enveditor.svg?style=flat-square)](https://packagist.org/packages/geoffry304/yii2-enveditor)

## Module and component to edit env file ##

### Installation ###

The preferred way to install **yii2-enveditor** is through [Composer](https://getcomposer.org/). Either add the following to the require section of your `composer.json` file:

`"geoffry304/yii2-enveditor": "*"` 

Or run:

`$ php composer.phar require geoffry304/yii2-enveditor "*"` 

You can manually install **yii2-enveditor** by [downloading the source in ZIP-format](https://github.com/geoffry304/yii2-enveditor/archive/master.zip).


Update the config file
```php
// app/config/web.php
return [
'components' => [
        'env' => [
            'class' => '\geoffry304\enveditor\components\EnvComponent',
            'autoBackup' => true,
            'backupPath' => "backups",
        ],
    ],
    'modules' => [
        'enveditor' => [
            'class' => '\geoffry304\enveditor\Module',
            'allowedIds' => "1,2,3"
        ],
    ],
];
```

 
## Options
  
**Module** Has the following options to modify it's behaviour:

- **allowedIds**: The userids who have access to the module, multiple ids with comma separated.

**Component** Has the following options to modify it's behaviour:

- **filePath**: The path to the .env file. Default to the basepath/.env
- **autoBackup**: Autobackup to true or false. Default to true
- **backupPath**: The folder where the backups need to be stored. Default to backups


## Special thanks to [JackieDo](https://github.com/JackieDo/Laravel-Dotenv-Editor)
Used his Laravel code to make it work in Yii2.



