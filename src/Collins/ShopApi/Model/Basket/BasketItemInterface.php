<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;


interface BasketItemInterface
{
    /**
     * The ID of this basket item or set. You can choose this ID by yourself to identify
     * your item later.
     *
     * @return string
     */
    public function getId();

    /**
     * Get the total price in euro cent
     *
     * @return integer
     */
    public function getTotalPrice();

    /**
     * Get the total net price in euro cent
     *
     * @return integer
     */
    public function getTotalNet();

    /**
     * Get the total value added tax in euro cent
     *
     * @return integer
     */
    public function getTotalVat();

    /**
     * The unique key of this basket item.
     * This is used to collect similar items or sets together.
     *
     * @return string
     */
    public function getUniqueKey();

    /**
     * Indicates, if the item as some error, for example the quantity has reached
     *
     * @return boolean
     */
    public function hasErrors();
}