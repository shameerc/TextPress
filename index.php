<?php

/**
* Required necessary files
*/
 ini_set('display_errors',true);
 error_reporting(E_ALL);
require 'Slim/Slim.php';
require 'lib/Textpress.php';
require 'lib/View.php';

/**
* Require config file
* @return Array config values
*/
$config = require 'config/config.php';

/**
* Create an instance of Slim with custom view
* and set the configurations from config file
*/

$app = new Slim(array('view' => 'View','mode' => 'development'));

$app->config($config);

/**
* Create an object of Textpress and pass the object of Slim to it.
*/
$textpress = new Textpress($app);

/**
* Finally run Textpress
*/
$textpress->run();