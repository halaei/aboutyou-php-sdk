<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

use Collins\ShopApi\Model\Facet;

class FacetFactory implements ModelFactoryInterface
{
    public function createFromJson($jsonObject)
    {
        return new Facet(
            $jsonObject->facet_id,
            $jsonObject->name,
            isset($jsonObject->value) ? $jsonObject->value : null,
            $jsonObject->id,
            $jsonObject->group_name,
            isset($jsonObject->options) ? $jsonObject->options : null
        );
    }
} 