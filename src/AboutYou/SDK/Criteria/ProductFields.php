<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Criteria;


class ProductFields
{
    /*
     * id and name is set per default
     */
    const IS_ACTIVE              = 'active';
    const BRAND                  = 'brand_id';
    const BULLET_POINTS          = 'bullet_points';
    const DESCRIPTION_LONG       = 'description_long';
    const DESCRIPTION_SHORT      = 'description_short';
    const DEFAULT_VARIANT        = 'default_variant';
    const VARIANTS               = 'variants';
    const MIN_PRICE              = 'min_price';
    const MAX_PRICE              = 'max_price';
    const IS_SALE                = 'sale';
    const DEFAULT_IMAGE          = 'default_image';
    const ATTRIBUTES_MERGED      = 'attributes_merged';
    const CATEGORIES             = 'categories';
    const INACTIVE_VARIANTS      = 'inactive_variants';
    const MAX_SAVINGS            = 'max_savings';
    const MAX_SAVINGS_PERCENTAGE = 'max_savings_percentage';
    const TAGS                   = 'tags';
    const STYLES                 = 'styles';

    public static function filterFields(array $fields)
    {
        $fields = array_unique($fields);

        // styles are not yet supported by the API
        $index = array_search(self::STYLES, $fields);
        if ($index !== false) {
            unset($fields[$index]);
        }

        $fields = array_values($fields);

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