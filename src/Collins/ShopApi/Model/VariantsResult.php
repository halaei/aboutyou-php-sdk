<?php

namespace Collins\ShopApi\Model;


class VariantsResult
{
    /** @var Variant[] */
    protected $variants = array();
    /** @var array */
    protected $errors = array();

    
    /**
     * 
     * @param \stdClass $jsonObject
     * @param \Collins\ShopApi\Model\ModelFactoryInterface $factory
     * 
     * @return static
     */
    public static function create($variants, $errors, $productSearchResult)
    {        
        $variantsResult = new static();                
        $variantsResult->errors = $errors;
        
        if ($productSearchResult === false || count($variants) === 0) {
            // no variant was found
            return $variantsResult;
        }
                        
        // get products from product-search
        $products = $productSearchResult->getProducts();

        foreach ($variants as $variantId => $productId) {
            if (isset($products[$productId]) === false) {
                // product was not delivered                
                $variantsResult->errors[] = $variantId;
                continue;
            }
            
            $product = $products[$productId];
            $variant = $product->getVariantById($variantId);

            if ($variant instanceof Variant) {
                $variantsResult->variants[$variantId] = $variant;                
            }
        }            
        
                        
        return $variantsResult;
    }
    
    /**
     * @return bool
     */
    public function hasVariantsFound()
    {
        return count($this->variants) > 0;
    }

    /**
     * @return bool
     */
    public function hasVariantsNotFound()
    {
        return count($this->errors) > 0;
    }
    
    /**
     * @return Variant[]
     */
    public function getVariantsFound()
    {
        return $this->variants;
    }
    
    /**
     * @param int $id
     * 
     * @return Variant|null
     */
    public function getVariantById($id)
    {
        $result = null;
        
        if (isset($this->variants[$id])) {
            $result = $this->variants[$id];
        }
        
        return $result;
    }
         
    /**
     * @return int[]
     */
    public function getVariantsNotFound()
    {
        return $this->errors;
    }
}

