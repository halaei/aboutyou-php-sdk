<?php
namespace Collins\ShopApi\Model;

/**
 * @deprecated
 */
class BasketVariant extends BasketObject implements BasketObjectInterface
{
    protected $variant_id = null;
    protected $product_id = null;
    protected $total_price = null;
    
    protected $additional_data = array();

    public function fromJson($jsonObject)
    { 
        $this->id = isset($jsonObject->id) ? $jsonObject->id : null;
        $this->variant_id = $jsonObject->variant_id;
        $this->product_id = $jsonObject->product_id;
        $this->total_price = $jsonObject->total_price;
        $this->tax = $jsonObject->tax;
        $this->total_net = $jsonObject->total_net;
        $this->total_vat = $jsonObject->total_net;
        $this->additional_data = isset($jsonObject->additional_data) ? $jsonObject->additional_data : array();
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
        return $this->total_price/100;
    }
    
    public function getTax()
    {
        return $this->tax;
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
    
    /**
     * Returns true if all the properties but the ID match.
     * 
     * @param \Collins\ShopApi\Model\BasketVariant $basketVariant BasketVariant to compare with
     */
    public function isEqual(BasketObject $basketVariant)
    {
        return $this->getUniqueKey() == $basketVariant->getUniqueKey();
    }
    
    public function getUniqueKey()
    {
        $properties = array(
            'variant_id',
            'additional_data'
        );
        
        $key = '';
        foreach($properties as $property) {
            $key .= json_encode($this->$property);
        }
        
        return md5($key);
    }
}