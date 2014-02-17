<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi;

class ProductSearchFilter implements CriteriaInterface
{
    const FILTER_SALE          = 'sale';
    const FILTER_CATEGORY_IDS  = 'categories';
    const FILTER_PRICE         = 'prices';
    const FILTER_SEARCHWORD    = 'searchword';
    const FILTER_ATTRIBUTES    = 'facets';

    /** @var filter */
    protected $filter = [];

    public static function create()
    {
        return new self();
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return ProductSearchFilter
     */
    public function set($key, $value)
    {
        $this->filter[$key] = $value;

        return $this;
    }

    /**
     * @param boolean|null $sale
     *    true => only sale products
     *    false => no sale products
     *    null => both (default)
     *
     * @return ProductSearchFilter
     */
    public function addIsSale($sale)
    {
        if (!is_bool($sale)) {
            $sale = null;
        }

        return $this->set(self::FILTER_SALE, $sale);
    }

    /**
     * @param string $searchword
     *
     * @return ProductSearchFilter
     */
    public function addSearchword($searchword)
    {
        return $this->set(self::FILTER_SEARCHWORD, $searchword);
    }

    /**
     * @param array $categoryIds array of integer
     *
     * @return ProductSearchFilter
     */
    public function addCategories(array $categoryIds)
    {
        return $this->set(self::FILTER_CATEGORY_IDS, $categoryIds);
    }

    /**
     * @param array $attributes  array of array with group id and attribute ids
     *   for example [0 => [264]]: search for products with the brand "TOM TAILER"
     *
     * TODO: allow ot filter by AttributeGroup
     *
     * @return ProductSearchFilter
     */
    public function addAttributes(array $attributes)
    {
        return $this->set(self::FILTER_ATTRIBUTES, $attributes);
    }

    /**
     * @param integer $from  must be 1 or greater
     * @param integer $to    must be 1 or greater
     *
     * @return ProductSearchFilter
     */
    public function addPrice($from = 0, $to = 0)
    {
        settype($from, 'int');
        settype($to, 'int');

        $price = [];
        if ($from > 0) {
            $price['from'] = $from;
        }
        if ($to > 0) {
            $price['to'] = $to;
        }

        return $this->set(self::FILTER_PRICE, $price);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->filter;
    }
} 