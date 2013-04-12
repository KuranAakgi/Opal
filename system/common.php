<?php

//include files
define('DIRECTORY_ROOT', dirname(__FILE__));
$includePath = array();

//load config files
foreach (glob(DIRECTORY_ROOT . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.cfg.php') as $filename)
    include($filename);

//load aspect files
foreach (glob(DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'Aspect' . DIRECTORY_SEPARATOR . '*.ast.php') as $filename)
    include($filename);

if(isset($config['include'])){
    foreach ($config['include'] as $path) {
        foreach (glob(DIRECTORY_ROOT . DIRECTORY_SEPARATOR . $path) as $filename){
            include($filename);
        }
    }
}

//build $includePath[ which include abstract classes and models
$includePath[] = DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'AbstractClass';
$includePath[] = DIRECTORY_ROOT . DIRECTORY_SEPARATOR . 'model';

//set auto include path
set_include_path(implode(PATH_SEPARATOR, $includePath));

renewAllModelObject();

//autoload a class definition file by magic function
function __autoload($class_name) {
    include_once $class_name . '.cls.php';
}

