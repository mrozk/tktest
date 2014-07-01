<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 11:51 AM
 */
use Tk\TkFactory\TkFactory;
use Tk\DbPointer;
define('tk', 1);

header("Content-Type: text/html; charset=utf-8");

require_once ('application/bootstrap.php');

try{

    $app = new TkFactory();
    $commResolver = $app->getCommand();

    if( !isset( $_REQUEST['task'] ) ){
        $task = 'index';
    }else{
        $task = $_REQUEST['task'];
    }
    $methodName = 'action_' . $task;
    if(  method_exists($commResolver, $methodName) ){
        $commResolver->$methodName();
    }else{
        throw new Exception('action not found');
    }

}catch (\Exception $e){
    //echo json_encode(array('response' => '0', 'msg' => $e->getMessage()));
    echo $e->getMessage();
}
