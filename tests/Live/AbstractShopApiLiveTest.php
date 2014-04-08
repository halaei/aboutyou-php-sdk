<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi;
use Collins\ShopApi\Criteria;

abstract class AbstractShopApiLiveTest extends \Collins\ShopApi\Test\ShopSdkTest
{
    private $api;
    private $config;
    
    /**
     * @return []
     */
    protected function getConfig()
    {
        if (!isset($this->config)) {            
            $path = dirname(__FILE__) . '/config/config.ini';
 
            if (!file_exists($path)) {
                throw new \ErrorException('You need to create a config file in config/config.ini');
            }
            
            $this->config = parse_ini_file('config/config.ini');            
        }
        
        return $this->config;
    }
    
    /**
     * @return ShopApi 
     */
    protected function getShopApi()
    {
        $config = $this->getConfig();
        
        if(!isset($this->api)) {
            $this->api = new ShopApi($config['user'], $config['password']);
            $this->api->setApiEndpoint($config['endpoint']);
        }
        
        return $this->api;
    }
    
    /**
     * @return String
     */
    protected function getSessionId()
    {
        $config = $this->getConfig();
        
        return $config['session_id'];
    }
    
    /**
     * @param int $limit
     * @return Products[]
     */
    public function getProducts($limit = 1)
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit($limit);
        $criteria->selectProductFields(array(\Collins\ShopApi\Criteria\ProductFields::DEFAULT_VARIANT));        
        $result = $api->fetchProductSearch($criteria);
        
        return $result->getProducts();
    }
  
    /**
     * @return \Collins\ShopApi\Criteria\ProductSearchCriteria
     */
    protected function getSearchCriteria()
    {
        $criteria = new Criteria\ProductSearchCriteria("123456");
        
        return $criteria;
    }
    
    protected function getVariantId()
    {
        $config = $this->getConfig();
        
        return $config['variant_id'];
    }
    
    protected function getProductId()
    {
        $config = $this->getConfig();
        
        return (int) $config['product_id'];        
    }
}
