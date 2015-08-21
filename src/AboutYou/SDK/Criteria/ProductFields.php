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
    const ATTRIBUTES_MERGED      = 'attributes_merged';
    const BRAND                  = 'brand_id';
    const BULLET_POINTS          = 'bullet_points';
    const DEFAULT_IMAGE          = 'default_image';
    const CATEGORIES             = 'categories';
    const DEFAULT_VARIANT        = 'default_variant';
    const DESCRIPTION_LONG       = 'description_long';
    const DESCRIPTION_SHORT      = 'description_short';
    const FIRST_PUBLICATION_DATE = 'new_in_since_date';
    const IMAGES                 = 'images';
    const INACTIVE_VARIANTS      = 'inactive_variants';
    const IS_ACTIVE              = 'active';
    const IS_SALE                = 'sale';
    const MIN_PRICE              = 'min_price';
    const MAX_PRICE              = 'max_price';
    const MAX_SAVINGS            = 'max_savings';
    const MAX_SAVINGS_PERCENTAGE = 'max_savings_percentage';
    const STYLE_KEY              = 'style_key';
    const STYLES                 = 'styles';
    const PRODUCT_ATTRIBUTES     = 'product_attributes';
    const TAGS                   = 'tags';
    const VARIANTS               = 'variants';

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

        if (!in_array(self::FIRST_PUBLICATION_DATE, $fields)) {
            $fields[] = self::FIRST_PUBLICATION_DATE;
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