<?php
namespace Collins\ShopApi\Model;

/**
 * @deprecated
 */
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
    protected $additional_data = array();
    
    /**
     *
     * @var Basket
     */
    protected $basket = null;
    
    protected $tax = null;
    protected $total_net = null;
    protected $total_vat = null;
    
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
        return $this->additional_data;
    }
    
    public function getTax()
    {
        return $this->tax;
    }
    
    public function getTotalNet()
    {
        return $this->total_net;
    }
    
    public function getTotalVat()
    {
        return $this->total_vat;
    }
}