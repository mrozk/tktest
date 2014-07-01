<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 4:13 PM
 */

namespace Tk\ApiCompanies;


interface ApiCompany {
    // Возвращаем api ответ в удобном для нас виде
    function getData($url, $params);
} 