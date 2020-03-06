<?php
/**
 * Created by PhpStorm.
 * User: leeyifiei
 * Date: 2020/3/6
 * Time: 12:03 PM
 */

use qianfan\consul\client;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';
require_once(dirname(__FILE__) . '/../../vendor/yiisoft/yii2/Yii.php');

define(YII_DEBUG, true);
@(Yii::$app->charset = 'UTF-8');

$application = new yii\web\Application([
    'id' => 'example',
    'basePath' => dirname(__FILE__),
    'runtimePath' => dirname(__FILE__) . '/runtime',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@runtime/cache'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
//                  'categories' => ['yii\db\*'],
                    'logVars' => [],
                    'logFile' => '@runtime/log/app.log'
                ],
            ]
        ]
    ]
]);

$sdk = new client([
    'options' => [],
    'cacheComponent' => 'cache'
]);


list($url,) = $sdk->discover('imgsize');
var_dump($url);