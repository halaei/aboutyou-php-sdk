<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Criteria;

use AboutYou\SDK\Model\FacetGetGroupInterface;
use AboutYou\SDK\Model\FacetGroup;
use AboutYou\SDK\Model\FacetGroupSet;
use AboutYou\SDK\Model\Product;

class ProductSearchCriteria extends AbstractCriteria implements CriteriaInterface
{
    const SORT_TYPE_COUNT             = 'count';
    const SORT_TYPE_CREATED           = 'created_date';
    const SORT_TYPE_DEFAULT           = null;
    const SORT_TYPE_MOST_VIEWED       = 'most_viewed';
    const SORT_TYPE_PRICE             = 'price';
    const SORT_TYPE_RELEVANCE         = 'relevance';
    const SORT_TYPE_UPDATED           = 'updated_date';
    const SORT_TYPE_NEW_IN_SINCE_DATE = 'new_in_since_date';

    const NEW_IN_SINCE_DATE_TYPE_DAY = 'day';

    const NEW_IN_SINCE_DATE_TYPE_WEEK = 'week';

    const NEW_IN_SINCE_DATE_TYPE_MONTH = 'month';

    const NEW_IN_SINCE_DATE_SPAN_MIN = 1;

    const NEW_IN_SINCE_DATE_SPAN_MAX = 14;

    const SORT_ASC  = 'asc';
    const SORT_DESC = 'desc';

    const FACETS_ALL = '_all';
    const FACETS_UNLIMITED = -1;

    const FILTER_CATEGORY_IDS       = 'categories';
    const FILTER_PRICE              = 'prices';
    const FILTER_PRODUCT_ATTRIBUTES = 'product_facets';
    const FILTER_SALE               = 'sale';
    const FILTER_SEARCHWORD         = 'searchword';
    const FILTER_VARIANT_ATTRIBUTES = 'facets';
    const FILTER_NEW_IN_SINCE_DATE = 'new_in_since_date';
    /** @deprecated */
    const FILTER_ATTRIBUTES         = 'facets';

    /** @var array */
    protected $filter = [];


    /** @var array */
    protected $result;


    /** @var string */
    protected $sessionId;

    protected $newInSinceDateTypes = [
        self::NEW_IN_SINCE_DATE_TYPE_DAY,
        self::NEW_IN_SINCE_DATE_TYPE_WEEK,
        self::NEW_IN_SINCE_DATE_TYPE_MONTH
    ];

    /**
     * @param string $sessionId
     */
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->result    = [];
    }

    /**
     * Creates a new instance of this class and returns it.
     *
     * @param $sessionId
     *
     * @return ProductSearchCriteria
     */
    public static function create($sessionId)
    {
        return new self($sessionId);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return ProductSearchCriteria
     */
    public function filterBy($key, $value)
    {
        $this->filter[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return null|mixed
     */
    public function getFilter($key)
    {
        return
            isset($this->filter[$key]) ?
                $this->filter[$key] :
                null
            ;
    }

    /**
     * @param boolean|null $sale
     *    true => only sale products
     *    false => no sale products
     *    null => both (default)
     *
     * @return ProductSearchCriteria
     */
    public function filterBySale($sale)
    {
        if (!is_bool($sale)) {
            $sale = null;
        }

        return $this->filterBy(self::FILTER_SALE, $sale);
    }

    /**
     * @return boolean|null
     */
    public function getSaleFilter()
    {
        return $this->getFilter(self::FILTER_SALE);
    }

    /**
     * @param string $searchword
     *
     * @return ProductSearchCriteria
     */
    public function filterBySearchword($searchword)
    {
        return $this->filterBy(self::FILTER_SEARCHWORD, $searchword);
    }

    /**
     * @return string|null
     */
    public function getSearchwordFilter()
    {
        return $this->getFilter(self::FILTER_SEARCHWORD);
    }

    /**
     * @param integer[] $categoryIds  array of integer
     * @param boolean $append         if true the category ids will added to current filter
     *
     * @return ProductSearchCriteria
     */
    public function filterByCategoryIds(array $categoryIds, $append = false)
    {
        if ($append && isset($this->filter[self::FILTER_CATEGORY_IDS])) {
            $categoryIds = array_merge($this->filter[self::FILTER_CATEGORY_IDS], $categoryIds);
        }
        $categoryIds = array_values(array_unique($categoryIds));

        return $this->filterBy(self::FILTER_CATEGORY_IDS, $categoryIds);
    }

    /**
     * @return integer[]|null
     */
    public function getCategoryFilter()
    {
        return $this->getFilter(self::FILTER_CATEGORY_IDS);
    }

    /**
     * @param array $attributes  array of array with group id and attribute ids
     *   for example [0 => [264]]: search for products with the brand "TOM TAILER"
     * @param boolean $append, if true the category ids will added to current filter
     *
     * @return ProductSearchFilter
     */
    public function filterByFacetIds(array $attributes, $append = false)
    {
        if ($append && isset($this->filter[self::FILTER_VARIANT_ATTRIBUTES])) {
            $merged = $this->filter[self::FILTER_VARIANT_ATTRIBUTES];
            foreach ($attributes as $groupId => $facetIds) {
                if (isset($merged[$groupId])) {
                    $merged[$groupId] = array_unique(array_merge($merged[$groupId], $facetIds));
                } else {
                    $merged[$groupId] = $facetIds;
                }
            }
            $attributes = $merged;
        }

        return $this->filterBy(self::FILTER_VARIANT_ATTRIBUTES, $attributes);
    }

    /**
     * @return array|null
     * @see filterByFacetIds()
     */
    public function getFacetFilter()
    {
        return $this->getFilter(self::FILTER_VARIANT_ATTRIBUTES);
    }

    /**
     * @param FacetGroup $facetGroup
     * @param boolean $append, if true the category ids will added to current filter
     *
     * @return ProductSearchCriteria
     */
    public function filterByFacetGroup(FacetGroup $facetGroup, $append = false)
    {
        return $this->filterByFacetIds($facetGroup->getIds(), $append);
    }

    /**
     * @param FacetGroupSet $facetGroupSet
     * @param boolean $append, if true the category ids will added to current filter
     *
     * @return ProductSearchCriteria
     */
    public function filterByFacetGroupSet(FacetGroupSet $facetGroupSet, $append = false)
    {
        return $this->filterByFacetIds($facetGroupSet->getIds(), $append);
    }

    /**
     * @param array $attributes  array of array with group id and attribute ids
     *   for example [0 => [264]]: search for products with the brand "TOM TAILER"
     * @param boolean $append, if true the category ids will added to current filter
     *
     * @return ProductSearchFilter
     */
    public function filterByProductFacetIds(array $attributes, $append = false)
    {
        if ($append && isset($this->filter[self::FILTER_PRODUCT_ATTRIBUTES])) {
            $merged = $this->filter[self::FILTER_PRODUCT_ATTRIBUTES];
            foreach ($attributes as $groupId => $facetIds) {
                if (isset($merged[$groupId])) {
                    $merged[$groupId] = array_unique(array_merge($merged[$groupId], $facetIds));
                } else {
                    $merged[$groupId] = $facetIds;
                }
            }
            $attributes = $merged;
        }

        return $this->filterBy(self::FILTER_PRODUCT_ATTRIBUTES, $attributes);
    }

    /**
     * @return array|null
     * @see filterByFacetIds()
     */
    public function getProductFacetFilter()
    {
        return $this->getFilter(self::FILTER_PRODUCT_ATTRIBUTES);
    }

    /**
     * @param integer $from  must be 1 or greater
     * @param integer $to    must be 1 or greater
     *
     * @return ProductSearchCriteria
     */
    public function filterByPriceRange($from = 0, $to = 0)
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

        return $this->filterBy(self::FILTER_PRICE, $price);
    }

    /**
     * Filter by new_in_since_date.
     *
     * @param int $from
     * @param int $to
     *
     * @return ProductSearchCriteria
     */
    public function filterByNewInSinceDate($from, $to)
    {
        settype($type, 'int');
        settype($span, 'int');

        $params = [
            'from' => $from,
            'to'   => $to,
        ];

        return $this->filterBy(self::FILTER_NEW_IN_SINCE_DATE, $params);
    }

    /**
     * Returns an associative array with could contains "to" and/or "from", eg.
     * ["from" => 100, "to" => 10000] or ["to" => 20000]
     *
     * @return array|null
     */
    public function getPriceRangeFilter()
    {
        return $this->getFilter(self::FILTER_PRICE);
    }

    /**
     * @param string $type
     * @param string $direction
     *
     * @return $this
     */
    public function sortBy($type, $direction = self::SORT_ASC)
    {
        $this->result['sort'] = [
            'by'        => $type,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * @param integer $limit
     * @param integer $offset
     *
     * @return $this
     */
    public function setLimit($limit, $offset = 0)
    {
        max(min($limit, 200), 0);
        $this->result['limit'] = $limit;

        max($offset, 0);
        $this->result['offset'] = $offset;

        return $this;
    }

    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function selectSales($enable = true)
    {
        if ($enable) {
            $this->result['sale'] = true;
        } else {
            unset($this->result['sale']);
        }

        return $this;
    }

    /**
     * @param string $type
     * @param int    $span
     *
     * @return $this
     */
    public function selectNewIn($type = 'week', $span = 4)
    {
        $type = in_array($type, $this->newInSinceDateTypes) ? $type : self::NEW_IN_SINCE_DATE_TYPE_WEEK;
        $span = max(min($span, 14), 1);

        if ($type && $span) {
            $this->result['new_in_since_date'] = ['type' => $type, 'span' => $span];
        } else {
            unset($this->result['new_in_since_date']);
        }

        return $this;
    }

    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function selectPriceRanges($enable = true)
    {
        if ($enable) {
            $this->result['price'] = true;
        } else {
            unset($this->result['price']);
        }

        return $this;
    }

    /**
     * @param integer|string $groupId
     * @param integer $limit
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function selectFacetsByGroupId($groupId, $limit)
    {
        $this->checkFacetLimit($limit);
        if (!is_long($groupId) && !ctype_digit($groupId)) {
            throw new \InvalidArgumentException();
        }

        if (!isset($this->result['facets'])) {
            $this->result['facets'] = new \StdClass;
        }

        if (!isset($this->result['facets']->{$groupId})) {
            $this->result['facets']->{$groupId} = ['limit' => $limit];
        }

        return $this;
    }

    /**
     * @param FacetGetGroupInterface $group
     * @param integer $limit
     *
     * @return $this
     */
    public function selectFacetsByFacetGroup(FacetGetGroupInterface $group, $limit)
    {
        return $this->selectFacetsByGroupId($group->getGroupId(), $limit);
    }

    /**
     * @param integer $limit
     *
     * @return $this
     */
    public function selectAllFacets($limit)
    {
        $this->checkFacetLimit($limit);
        $this->result['facets'] = [self::FACETS_ALL => ['limit' => $limit]];

        return $this;
    }

    /**
     * @param integer|string $groupId
     * @param integer $limit           between 0 and 10000, 0 is to get all
     * @param null $sortBy             only SORT_TYPE_COUNT and SORT_TYPE_DEFAULT is currently supported
     * @param string $sortDirection    SORT_ASC or SORT_DESC, SORT_ASC is default
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function selectProductFacetsByGroupId($groupId, $limit = 0, $sortBy = self::SORT_TYPE_DEFAULT, $sortDirection = self::SORT_ASC)
    {
        $this->checkFacetLimit($limit);
        if (!is_long($groupId) && !ctype_digit($groupId)) {
            throw new \InvalidArgumentException();
        }

        if (!isset($this->result['product_facets'])) {
            $this->result['product_facets'] = new \StdClass;
        }

        if (!isset($this->result['product_facets']->{$groupId})) {
            $attributes = ['size' => (int)$limit, 'sort' => new \stdClass];
            if ($sortBy !== self::SORT_TYPE_DEFAULT) {
                $attributes['sort']->{'by'} = $sortBy;
            }
            if ($sortDirection !== self::SORT_ASC) {
                $attributes['sort']->{'direction'} = $sortDirection;
            }
            $this->result['product_facets']->{$groupId} = $attributes;
        }

        return $this;
    }

    protected function checkFacetLimit($limit)
    {
        if (!is_long($limit)) {
            throw new \InvalidArgumentException('limit must be an integer');
        }
        if ($limit < -1) {
            throw new \InvalidArgumentException('limit must be positive or -1 for unlimited facets');
        }
    }

    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function selectCategories($enable = true)
    {
        if ($enable) {
            $this->result['categories'] = true;
        } else {
            unset($this->result['categories']);
        }

        return $this;
    }

    /**
     * @param integer|Product[] $ids
     *
     * @return $this
     */
    public function boostProducts(array $ids)
    {
        $ids = array_map(function($val) {
            if ($val instanceof Product) {
                return $val->getId();
            }

            return intval($val);
        }, $ids);

        $ids = array_values(array_unique($ids));
        $this->result['boosts'] = $ids;

        return $this;
    }

    /**
     * @param string[] $fields
     *
     * @return $this
     */
    public function selectProductFields(array $fields)
    {
        $this->result['fields'] = ProductFields::filterFields($fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getProductFields()
    {
        return $this->result['fields'];
    }

    /**
     * @param string $sessionId
     *
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function requiresCategories()
    {
        $productCategories =
            isset($this->result['fields']) &&
            ProductFields::requiresCategories($this->result['fields'])
        ;
        $categoryFacets = isset($this->result['categories']) && $this->result['categories'];

        return $productCategories || $categoryFacets;
    }

    public function requiresFacets()
    {
        $productFacets =
            isset($this->result['fields']) &&
            ProductFields::requiresFacets($this->result['fields'])
        ;
        $facetFacets = !empty($this->result['facets']);

        return $productFacets || $facetFacets;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $params = [
            'session_id' => $this->sessionId
        ];

        if (!empty($this->result)) {
            $params['result'] = $this->result;
        }
        if ($this->filter) {
            $filter = $this->filter;
            if (isset($filter[self::FILTER_VARIANT_ATTRIBUTES])) {
                $filter[self::FILTER_VARIANT_ATTRIBUTES] = (object)$filter[self::FILTER_VARIANT_ATTRIBUTES];
            }
            if (isset($filter[self::FILTER_PRODUCT_ATTRIBUTES])) {
                $filter[self::FILTER_PRODUCT_ATTRIBUTES] = (object)$filter[self::FILTER_PRODUCT_ATTRIBUTES];
            }
            $params['filter'] = $filter;
        }

        return $params;
    }
}
