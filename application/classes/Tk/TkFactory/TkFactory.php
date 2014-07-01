<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 12:16 PM
 */
namespace Tk\TkFactory;


use Tk\Commands\CompanyTkCommand;

class TkFactory{

    private $command;

    public function __construct( ){
        if(!isset($_REQUEST['command'])){
            $this->command = 'company';
        }else{
            $this->command = $_REQUEST['command'];
        }

    }

    public function getCommand(){
        if( $this->command ){
            return new CompanyTkCommand();
        }
        throw new \Exception('Команда не найденав');
    }
}