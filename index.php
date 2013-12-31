<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

/**
*
* Require composer autoloader
*/

require './vendor/autoload.php';

/**
* Require config file
* @return Array config values
*/

$config = require './config/config.php';

/**
* Create an instance of Slim with custom view
* and set the configurations from config file
*/

$app = new Slim\Slim(array('view' => new Textpress\View(),'mode' => 'production'));

/**
* Create an object of Textpress and pass the object of Slim to it.
*/
$textpress = new Textpress\Textpress($app, $config);

/**
* Finally run Textpress
*/
$textpress->run();