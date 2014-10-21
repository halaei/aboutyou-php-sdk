<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Model;

class Order
{
    /** @var string */
    protected $id;

    /** @var Basket */
    protected $basket;

    public function __construct($id, Basket $basket)
    {
        $this->id     = $id;
        $this->basket = $basket;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Basket
     */
    public function getBasket()
    {
        return $this->basket;
    }
} 