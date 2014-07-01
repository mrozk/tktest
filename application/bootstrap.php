<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 11:51 AM
 */
namespace Tk;

spl_autoload_register(function ( $className ) {
    if(strpos($className, __NAMESPACE__) === 0) {
        $classPath = implode(DIRECTORY_SEPARATOR, explode('\\', $className));

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . $classPath . ".php";

        if (is_readable($filename) && file_exists($filename))
        {
            require_once $filename;
        }
        // Тут не должно быть ошибок чтоб работали другие автолоадеры
    }
});