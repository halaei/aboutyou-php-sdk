<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Product
{
    /** @var integer */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $descritptionShort;

    /** @var string */
    public $descriptionLong;

    public function getAttributes()
    {

    }

    public function fetchCategories()
    {

    }

    public function getDefaultImage()
    {

    }
} 