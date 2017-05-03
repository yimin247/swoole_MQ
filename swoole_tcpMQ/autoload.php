<?php
/*
* File Name: autoload.php
* Author: ym
* mail: yimin247@163.com
* Created Time:2017-05-02 11:50:48 AM CST
*/
if (!function_exists('classAutoLoader')) {

    function classAutoLoader($class)
    {

        $classFile = __DIR__ . '/' . $class . '.php';

        if (is_file($classFile) && !class_exists($class)) {
            require_once($classFile);
        }
    }
}

spl_autoload_register('classAutoLoader');
