<?php

use Maslosoft\Mangan\Mangan;

Mangan::fly()->dbName = 'mangan-yii-test';

require __DIR__ . '/../vendor/yiisoft/yii/framework/yiilite.php';

// Un-register Yii autoloaders, as they will fail in many cases
spl_autoload_unregister(array(Yii::class, 'autoload'));
spl_autoload_unregister(array(YiiBase::class, 'autoload'));

// Register Yii autoloader again, but to load only class map
spl_autoload_register(static function($className)
{
	return YiiBase::autoload($className, true);
});