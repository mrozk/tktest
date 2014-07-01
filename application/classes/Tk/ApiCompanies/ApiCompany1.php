<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 4:16 PM
 */

namespace Tk\ApiCompanies;


class ApiCompany1 implements ApiCompany {
    function getData( $url, $params ){
        return array('id' => 1, 'name' => 'company1', 'time' => '2дн', 'price' => '100');
    }
} 