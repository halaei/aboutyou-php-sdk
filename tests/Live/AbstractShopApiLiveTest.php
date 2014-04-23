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
    protected function getShopApi(
        ResultFactoryInterface $resultFactory = null,
        LoggerInterface $logger = null,
        $facetManagerCache = null
    )
    {
        $config = $this->getConfig();
        
        if (!isset($this->api)) {
            $this->api = new ShopApi($config['user'], $config['password'], $config['endpoint'], $resultFactory, $logger, $facetManagerCache);
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
     * @param int $offset
     * @return ShopApi\Model\Product
     */
    public function getProduct($offset = 1)
    {
        if ($offset < 1) {
            $offset = 1;
        }
        
        $api = $this->getShopApi();
        
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(1, $offset);
        $criteria->selectProductFields(array(\Collins\ShopApi\Criteria\ProductFields::DEFAULT_VARIANT));        
        
        $result = $api->fetchProductSearch($criteria);
        $products = $result->getProducts();
        
        return $products[0];
    }    
  
    /**
     * @return \Collins\ShopApi\Criteria\ProductSearchCriteria
     */
    protected function getSearchCriteria()
    {
        $criteria = new Criteria\ProductSearchCriteria('123456');
        
        return $criteria;
    }
    
    protected function getVariantId($index)
    {
        $product = $this->getProduct($index);
        
        return $product->getDefaultVariant()->getId();
    }
    
    protected function getProductId()
    {        
        return (int) $this->getProduct()->getId();
    }
}
