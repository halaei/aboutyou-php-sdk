<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi;


use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Exception\InvalidParameterException;

class QueryBuilder
{
    /** @var array */
    protected $query;

    public function __construct()
    {
        $this->query = [];
    }

    /**
     * @param string $searchword The prefix search word to search for.
     * @param int    $limit      Maximum number of results.
     * @param array  $types      Array of types to search for (Constants::TYPE_...).
     *
     * @return $this
     */
    public function fetchAutocomplete(
        $searchword,
        $limit = 50,
        $types = array(
            Constants::TYPE_PRODUCTS,
            Constants::TYPE_CATEGORIES
        )
    ) {
        $this->query[] = [
            'autocompletion' => array(
                'searchword' => $searchword,
                'types' => $types,
                'limit' => $limit
            )
        ];

        return $this;
    }

    /**
     * @param string $sessionId Free to choose ID of the current website visitor.
     *
     * @return $this
     */
    public function fetchBasket($sessionId)
    {
        $this->query[] = [
            'basket_get' => [
                'session_id' => $sessionId
            ]
        ];

        return $this;
    }

    /**
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param int    $amount           Amount of items to add.
     *
     * @return $this
     */
    public function addToBasket($sessionId, $productVariantId, $amount = 1)
    {
        $this->query[] = [
            'basket_add' => array(
                'session_id' => $sessionId,
                'product_variant' => array(
                    array(
                        'id' => (int)$productVariantId,
                        'command' => 'add',
                        'amount' => (int)$amount,
                    ),
                ),
            )
        ];

        return $this;
    }

    /**
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     *
     * @return $this
     */
    public function removeFromBasket($sessionId, $productVariantId)
    {
        $this->query[] = [
            'basket_add' => array(
                'session_id' => $sessionId,
                'product_variant' => array(
                    array(
                        'id' => (int)$productVariantId,
                        'command' => 'set',
                        'amount' => 0,
                    ),
                ),
            )
        ];

        return $this;
    }

    /**
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param int    $amount           Amount to set.
     *
     * @return $this
     */
    public function updateBasketAmount($sessionId, $productVariantId, $amount)
    {
        $this->query[] = [
            'basket_add' => array(
                'session_id' => $sessionId,
                'product_variant' => array(
                    array(
                        'id' => (int)$productVariantId,
                        'command' => 'set',
                        'amount' => (int)$amount,
                    ),
                ),
            )
        ];

        return $this;
    }

    /**
     * @param int[]|string[] $ids either a single category ID as integer or an array of IDs
     *
     * @return $this
     */
    public function fetchCategoriesByIds($ids)
    {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $ids = array_map('intval', $ids);

        $this->query[] = [
            'category' => array(
                'ids' => $ids
            )
        ];

        return $this;
    }

    /**
     * @param int $maxDepth -1 <= $maxDepth <= 10,
     *
     * @return $this
     */
    public function fetchCategoryTree($maxDepth = -1)
    {
        if ($maxDepth >= 0) {
            $params = ['max_depth' => $maxDepth];
        } else {
            $params = new \stdClass();
        }
        $this->query[] = [
            'category_tree' => $params,
        ];

        return $this;
    }

    /**
     * @param string[]|int[] $ids
     * @param array $fields
     *
     * @return $this
     */
    public function fetchProductsByIds(
        array $ids,
        array $fields = []
    ) {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $ids = array_map('intval', $ids);

        $this->query[] = [
            'products' => array(
                'ids' => $ids,
                'fields' => $fields
            )
        ];

        return $this;
    }

   /**
     * @param string|int $id
     *
     * @return $this
     */
    public function fetchOrder($orderId)
    {
        $this->query[] = [
            'get_order' => [
                'order_id' => $orderId
            ]
        ];

        return $this;
    }

    /**
     * @param string $sessionId
     * @param string $successUrl
     * @param string $cancelUrl
     * @param string $errorUrl
     *
     * @return $this
     */
    public function initiateOrder($sessionId, $successUrl, $cancelUrl, $errorUrl)
    {
        $args = [];
        $args['session_id'] = $sessionId;
        $args['success_url'] = $successUrl;
        if ($cancelUrl) $args['cancel_url'] = $cancelUrl;
        if ($errorUrl) $args['error_url'] = $errorUrl;
        $this->query[] = [ 'initiate_order' => $args ];

        return $this;
    }

    /**
     * @param string $userSessionId
     * @param array|CriteriaInterface $filter
     * @param array $result
     *
     * @return $this
     */
    public function fetchProductSearch(
        ProductSearchCriteria $criteria
    ) {
        $this->query[] = [
            'product_search' => $criteria->toArray()
        ];

        return $this;
    }

    /**
     * @param array $groupIds
     *
     * @return $this
     *
     * @throws Exception\InvalidParameterException
     */
    public function fetchFacets(array $groupIds)
    {
        if (empty($groupIds)) {
            throw new InvalidParameterException('no groupId given');
        }

        $groupIds = array_map('intval', $groupIds);

        $this->query[] = [
            'facets' => array(
                'group_ids' => $groupIds
            )
        ];

        return $this;
    }

    /**
     * @param array $params
     *
     * @return $this
     *
     * @throws Exception\InvalidParameterException
     */
    public function fetchFacet(array $params)
    {
        if (empty($params)) {
            throw new InvalidParameterException('no params given');
        }

        $this->query[] = ['facet' => $params];

        return $this;
    }

    /**
     * @param string $searchword The search string to search for.
     *
     * @return $this
     */
    public function fetchSuggest($searchword)
    {
        $this->query[] = [
            'suggest' => array(
                'searchword' => $searchword
            )
        ];

        return $this;
    }

    /**
     * @return $this
     */
    public function fetchChildApps()
    {
        $this->query[] = ['child_apps' => NULL ];

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return json_encode($this->query);
    }

}