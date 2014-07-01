<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 7:26 PM
 */

namespace Tk\LocalCompanies;


class LocalCompany3 implements LocalCompany{
    public function someMagickMethod($params, $coof){
        return array('id' => 2, 'name' => 'company3', 'time' => '1дн', 'price' => '170');
    }
} 