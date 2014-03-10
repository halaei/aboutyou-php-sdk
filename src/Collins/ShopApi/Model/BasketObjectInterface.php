<?php
namespace Collins\ShopApi\Model;

interface BasketObjectInterface
{
    /**
     * Returns the ID of the basket item
     *
     * @return int
     */
    public function getId();
    
    
    
    /**
     * Returns true if this item is a variant;
     * Returns false if it's a set of variants.
     * 
     * @return bool
     */
    public function isVariant();
    
} 