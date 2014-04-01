<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
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