<?php
namespace Collins\ShopApi\Model;

abstract class BasketObject extends AbstractModel
{
    /**
     *
     * @var string
     */
    protected $id = null;
    
    /**
     *
     * @var array
     */
    protected $addtionalData = array();
    
    /**
     *
     * @var Basket
     */
    protected $basket = null;
    
    /**
     * @param object        $jsonObject  json as object tree
     * @param Category|null $parent
     */
    public function __construct($jsonObject, $basket)
    {
        $this->basket = $basket;
        $this->fromJson($jsonObject);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}