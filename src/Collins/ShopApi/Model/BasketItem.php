<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

class BasketItem extends BasketVariantItem implements BasketItemInterface
{
    protected $id;

    public function __construct(\stdClass $jsonObject, $products)
    {
        $this->id = $jsonObject->id;
        parent::__construct($jsonObject, $products);
    }

    public function getId()
    {
        return $this->getId();
    }
}