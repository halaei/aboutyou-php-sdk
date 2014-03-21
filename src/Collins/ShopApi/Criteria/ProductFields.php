<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Criteria;


class ProductFields
{
    /*
     * id and name is set per default
     */
    const IS_ACTIVE         = "active";
    const BRAND             = "brand_id";
    const DESCRIPTION_LONG  = "description_long";
    const DESCRIPTION_SHORT = "description_short";
    const DEFAULT_VARIANT   = "default_variant";
    const VARIANTS          = "variants";
    const MIN_PRICE         = "min_price";
    const MAX_PRICE         = "max_price";
    const IS_SALE           = "sale";
    const DEFAULT_IMAGE     = "default_image";
    const ATTRIBUTES_MERGED = "attributes_merged";
    const CATEGORIES        = "categories";
}