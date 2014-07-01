<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 4:16 PM
 */

namespace Tk\ApiCompanies;


class ApiCompany2 implements ApiCompany {
    function getData( $url, $params ){
        return array('id' => 2, 'name' => 'company2', 'time' => '1дн', 'price' => '150');
    }
} 