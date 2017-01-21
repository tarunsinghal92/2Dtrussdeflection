<?php

//config variables
require_once __DIR__ . '/config.inc';

//common class
require_once __DIR__ . '/../libraries/common/common.class.php';

//include all models
foreach(glob(__DIR__ . "/../model/*.php") as $file){
    require_once $file;
}

//include all controllers
foreach(glob(__DIR__ . "/../controller/*.php") as $file){
    require_once $file;
}

//get from vendor folder
require_once(__DIR__ . '/../vendor/autoload.php');


?>
