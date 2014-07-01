<?php
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 7/1/14
 * Time: 12:37 PM
 */
namespace Tk\Commands;

use Tk\DbPointer;



class CompanyTkCommand {

    /**
     * продолжительность кеша в минутах
     * @var int
     */
    public $cache_expired = 60;

    // Добавляем всю фильтрацию предусмотренную предметной областью
    function validateParams( ){
        if( isset($_REQUEST['width']) && isset($_REQUEST['height']) && isset($_REQUEST['lenght'])
            && isset($_REQUEST['weigth']) && isset($_REQUEST['cityfrom']) && isset($_REQUEST['cityto'])){
            $params['width'] = $_REQUEST['width'];
            $params['height'] = $_REQUEST['height'];
            $params['lenght'] = $_REQUEST['lenght'];
            $params['weigth'] = $_REQUEST['weigth'];
            $params['cityFrom'] = $_REQUEST['cityfrom'];
            $params['cityTo'] = $_REQUEST['cityto'];
            return $params;
        }
        else{
            throw new \Exception('some params are empty');
        }

    }
    // Рабочая лошадка
    public function action_index(){
        $params = $this->validateParams();
        $sig = $this->getRequestSig($params);

        $cache_content = $this->renderCache($sig);
        if( $cache_content === null ){
            $resultListApi = array();
            $resultListLocal = array();
            // Получаем компании удаленных апи
            $resultListApi = $this->getApiCompanies($params);

            // Получаем локальные компании
            $resultListLocal = $this->getLocalCompanies($params);
            /*
            $citiesForSig = array($params['cityFrom'], $params['cityTo']);;
            asort($citiesForSig);
            $citySig = implode(':', $citiesForSig);
            $resultListLocal = $this->getCitySig( $citySig );
            if( $resultListLocal === null ){
                $resultListLocal = $this->getLocalCompanies($params);
                $this->saveToCityCache( $citySig, $resultListLocal);
                echo 'bad';
            }
            */
            $result = array_merge($resultListApi, $resultListLocal);
            $this->saveToCache( $sig, $result );
            print_r($result);
        }else{
            $result = unserialize($cache_content);
            print_r($result);
        }
    }
    public function getCitySig( $sig ){
        $pdo = DbPointer::getInstance();
        $query = 'SELECT content FROM city_cache WHERE sig = :sig';
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':sig', $sig );
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_OBJ);
        if( count( $result ) ){
            return unserialize( $result[0]->content );
        }
        else{
            return null;
        }
    }

    public function saveToCityCache( $sig, $data ){
        $pdo = DbPointer::getInstance();
        $query = 'INSERT INTO city_cache ( sig,content,expired ) VALUES (:sig, :content, DATE_ADD( NOW(), INTERVAL ' . $this->cache_expired . ' MINUTE ))';
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':sig', $sig );
        $content = serialize($data);
        $sth->bindParam( ':content', $content);
        $sth->execute();
    }

    public function saveToCache( $sig, $data ){
        $pdo = DbPointer::getInstance();
        $query = 'INSERT INTO cache ( sig,content,expired ) VALUES (:sig, :content, DATE_ADD( NOW(), INTERVAL ' . $this->cache_expired . ' MINUTE ))';
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':sig', $sig );
        $content = serialize($data);
        $sth->bindParam( ':content', $content);
        $sth->execute();
    }

    public function createMemoryTable(){
        /*
        $pdo = DbPointer::getInstance();
        $query = 'CREATE TABLE  `xxxx`
                 (`id` INT, INDEX USING HASH (id),
                  `payment_variant` varchar(255) DEFAULT NULL
                 )
                  ENGINE = MEMORY;';
        $sth = $pdo->prepare( $query );
        $sth->execute();
        echo 'ozk';

        $query = "INSERT INTO xxxx ( payment_variant ) VALUES( :payment_variant)";
        $sth = $pdo->prepare( $query );
        $payment_variant = 'xxx';
        $sth->bindParam( ':payment_variant', $payment_variant  );
        $sth->execute();

        $query = 'SELECT * FROM xxxx';
        $sth = $pdo->prepare( $query );
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_OBJ);
        print_r($result );
        */
    }

    // Получаем содержимое кеша
    public function renderCache( $sig ){
        $pdo = DbPointer::getInstance();
        $query = 'SELECT content FROM cache WHERE sig = :sig';
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':sig', $sig );
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_OBJ);
        if( count( $result ) ){
            return $result[0]->content;
        }
        else{
            return null;
        }

    }
   /**
    * В зависимости от осбенностей апи каждой тк идет
    * парсинг и возвращается единый вормат
    */
    public function apiRender( $url, $params, $class ){
        $class = 'Tk\ApiCompanies\\' . $class;
        if( class_exists( $class )){
            $resolver = new $class();
            return $resolver->getData($url, $params);
        }else{
            throw new \Exception('Не найден класс ' . $class . ' для api запроса к ТК');
        }
    }

    /**
     * @param $params
     */
    public function getApiCompanies( $params )
    {
        $resultList = array();
        $companiesConf = parse_ini_file(__DIR__ . '/../../../config/companies.ini', true);
        if( count($companiesConf) )
        {
            foreach ($companiesConf as $item) {
                $data = $this->apiRender($item['apiurl'], $params, $item['apiclass']);
                $resultList[] = $data;
            }
        }
        return $resultList;
    }

    /**
     * Для получения списка стоимости для компаний необходимо выбрать из именно те компании которые
     * есть в городах
     *
     * @param $params
     * @return array
     */
    public function  getLocalCompanies( $params ){
        $pdo = DbPointer::getInstance();

        $query = 'SELECT c.id, c.name, c.magic_coof, c.company_id
                        FROM companies AS c
                        LEFT JOIN companies_to_cities AS cc ON cc.company_id = c.company_id
                  WHERE cc.city_id=:city_id1 OR cc.city_id=:city_id2
                  ';
        $sth = $pdo->prepare( $query );
        $sth->bindParam( ':city_id1', $params['cityFrom'] );
        $sth->bindParam( ':city_id2', $params['cityTo'] );
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $tempConteiner = array();
        $validValues = array();
        if( count($result) ){
            foreach($result as $item){
               if( in_array($item['id'], $tempConteiner) ){
                   $validValues[] = $item;
               }else{
                   $tempConteiner[]  = $item['id'];
               }
            }
        }
        unset($result);
        unset($tempConteiner);
        if( count( $validValues ) ){
            $classes = parse_ini_file(__DIR__ . '/../../../config/localcompanies.ini', true);
            foreach($validValues as $item){
                $className = 'Tk\LocalCompanies\\' . $classes[$item['company_id']]['apiclass'];
                if( class_exists( $className )){
                    $resolver = new $className();
                    $result[] = $resolver->someMagickMethod($params, $item);
                }else{
                    //throw new \Exception('Не найден класс ' . $class . ' для api запроса к ТК');
                }
            }
        }

        return $result;
    }
    /**
     * @param $params
     * @return string
     */
    public function getRequestSig($params)
    {
        $citiesForSig = array($params['cityFrom'], $params['cityTo']);
        $paramsForSig = array($params['width'], $params['height'], $params['lenght']);
        asort($citiesForSig);
        asort($paramsForSig);
        $sig = implode(':', $citiesForSig) . ':' . implode(':', $paramsForSig) . ':' . $params['weigth'];
        return $sig;
    }

}