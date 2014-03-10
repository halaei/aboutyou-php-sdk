<?php
namespace Collins\ShopApi\Model;

class BasketVariant extends BasketObject implements BasketObjectInterface
{
    protected $variant_id = null;
    protected $product_id = null;
    protected $totalPrice = null;
    
    protected $additionalData = array();

    public function fromJson($jsonObject)
    { 
        $this->id = isset($jsonObject->id) ? $jsonObject->id : null;
        $this->variant_id = $jsonObject->variant_id;
        $this->product_id = $jsonObject->product_id;
        $this->totalPrice = $jsonObject->total_price;
        $this->additionalData = isset($jsonObject->additional_data) ? $jsonObject->additional_data : array();
    }
    
    public function getVariantId()
    {
        return $this->variant_id;
    }
    
    public function getProductId()
    {
        return $this->product_id;
    }
    
    public function getTotalPrice()
    {
        return $this->totalPrice/100;
    }
    
    public function isVariant() 
    {
        return true;
    }
    
    public function getProduct()
    {
        $products = $this->basket->getProducts();

        if(isset($products[$this->product_id])) {
            return $products[$this->product_id];
        }
        
        return null;
    }
    
    public function getVariant()
    {
        $product = $this->getProduct();
        if($product) {
            foreach($product->getVariants() as $variant) {
                if($variant->getId() == $this->variant_id) {
                    return $variant;
                }
            }
        }
        
        return null;
    }
    
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}