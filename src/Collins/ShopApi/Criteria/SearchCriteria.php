<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Criteria;

use Collins\ShopApi\Exception\InvalidParameterException;
use Collins\ShopApi\Model\FacetGroup;

class SearchCriteria implements SearchCriteriaInterface
{
    const SORT_TYPE_RELEVANCE   = 'relevance';
    const SORT_TYPE_UPDATED     = 'updated_date';
    const SORT_TYPE_CREATED     = 'created_date';
    const SORT_TYPE_MOST_VIEWED = 'most_viewed';
    const SORT_TYPE_PRICE       = 'price';

    const SORT_ASC  = 'asc';
    const SORT_DESC = 'desc';

    const FACETS_ALL = '_all';

    /** @var array */
    protected $result;

    /** @var ProductSearchFilter */
    protected $filter;

    /** @var string */
    protected $sessionId;

    /**
     * @param string $sessionId
     */
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->result    = [];
    }

    /**
     * @param string $type
     * @param string $direction
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
    public function saleFacets($enable = false)
    {
        if ($enable) {
            $this->result['sale'] = true;
        } else {
            unset($this->result['sale']);
        }

        return $this;
    }

    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function priceFactes($enable = false)
    {
        if ($enable) {
            $this->result['price'] = true;
        } else {
            unset($this->result['price']);
        }

        return $this;
    }

    /**
     * @param integer|string|FacetGroup $groupId
     * @param integer $limit
     *
     * @return $this
     */
    public function otherFacets($groupId, $limit)
    {
        if ($groupId instanceof FacetGroup) {
            $groupId = $groupId->getId();
        } else if ($groupId !== self::FACETS_ALL && !is_long($groupId) && !ctype_digit($groupId)) {
            throw new InvalidParameterException();
        }

        if (!isset($this->result['facets'])) {
            $this->result['facets'] = new \StdClass;
        }

        if (!isset($this->result['facets']->{$groupId})) {
            $this->result['facets']->{$groupId} = new \StdClass;
        }

        $this->result['facets']->{$groupId}->limit = $limit;

        return $this;
    }


    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function categoryFacets($enable = false)
    {
        if ($enable) {
            $this->result['categories'] = true;
        } else {
            unset($this->result['categories']);
        }

        return $this;
    }

    /**
     * @param integer[] $ids
     *
     * @return $this
     */
    public function boostProducts(array $ids)
    {
        if (empty($this->result['boost'])) {
            unset($this->result['boost']);
        }

        $ids = array_unique(array_map('intval', $ids));
        $this->result['boost'] = $ids;

        return $this;
    }

    /**
     * @param string[] $fields
     *
     * @return $this
     */
    public function selectFields(array $fields)
    {
        $this->result['fields'] = array_unique($fields);

        return $this;
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
     * @param ProductSearchFilter $filter
     *
     * @return ProductSearchFilter
     */
    public function filter(ProductSearchFilter $filter = null)
    {
        if ($filter !== null) {
            $this->filter = $filter;
        } else if ($this->filter === null) {
            $this->filter = new ProductSearchFilter();
        }

        return $this->filter;
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
            $params['filter'] = $this->filter->toArray();
        }

        return $params;
    }
}
