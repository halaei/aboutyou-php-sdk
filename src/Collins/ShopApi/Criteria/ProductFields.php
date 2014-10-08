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
    const IS_ACTIVE              = "active";
    const BRAND                  = "brand_id";
    const DESCRIPTION_LONG       = "description_long";
    const DESCRIPTION_SHORT      = "description_short";
    const DEFAULT_VARIANT        = "default_variant";
    const VARIANTS               = "variants";
    const MIN_PRICE              = "min_price";
    const MAX_PRICE              = "max_price";
    const IS_SALE                = "sale";
    const DEFAULT_IMAGE          = "default_image";
    const ATTRIBUTES_MERGED      = "attributes_merged";
    const CATEGORIES             = "categories";
    const INACTIVE_VARIANTS      = "inactive_variants";
    const MAX_SAVINGS            = "max_savings";
    const MAX_SAVINGS_PERCENTAGE = "max_savings_percentage";
    const tags                   = "tags";

    public static function filterFields(array $fields)
    {
        $fields = array_values(array_unique($fields));

        // this simplify parsing on (pre)fetching facets
        if (
            !in_array(self::ATTRIBUTES_MERGED, $fields) &&
            self::requiresFacets($fields)
        ) {
            $fields[] = ProductFields::ATTRIBUTES_MERGED;
        }

        return $fields;
    }

    public static function requiresFacets(array $fields)
    {
        $requiredFacetFields = array_intersect(array(
            self::BRAND,
            self::VARIANTS,
            self::DEFAULT_VARIANT,
            self::ATTRIBUTES_MERGED,
        ), $fields);

        return count($requiredFacetFields);
    }

    public static function requiresCategories(array $fields)
    {
        return in_array(self::CATEGORIES, $fields);
    }
}