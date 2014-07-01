<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 2:43 PM
 */
namespace Tk;

class DbPointer{

    protected static $_pdo;

    private function __construct(){}

    public static function getInstance() {
        if (null === self::$_pdo) {
            $dbConfig = parse_ini_file( __DIR__ . '/../../config/dbconf.ini');
            self::$_pdo = new \PDO('mysql:host=' . $dbConfig['db_location'] . ';dbname=' . $dbConfig['def_db'],
                                    $dbConfig['user'], $dbConfig['passwd']);
        }
        return self::$_pdo;
    }
}