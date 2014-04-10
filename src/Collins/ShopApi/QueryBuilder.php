<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi;


use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\Basket;

class QueryBuilder
{
    /** @var array */
    protected $query;

    public function __construct()
    {
        $this->query = array();
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
        if (!is_string($searchword)) {
            throw new \InvalidArgumentException('$searchword must be a string');
        }
        
        $this->query[] = array(
            'autocompletion' => array(
                'searchword' => $searchword,
                'types' => $types,
                'limit' => $limit
            )
        );

        return $this;
    }

    /**
     * @param string $sessionId Free to choose ID of the current website visitor.
     *
     * @return $this
     */
    public function fetchBasket($sessionId)
    {
        $this->checkSessionId($sessionId);

        $this->query[] = array(
            'basket' => array(
                'session_id' => $sessionId
            )
        );

        return $this;
    }

    /**
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param int    $amount           Amount of items to add.
     *
     * @return $this
     */
    public function addItemsToBasket($sessionId, array $items)
    {
        $this->checkSessionId($sessionId);

        $orderLines = array();

        foreach($items as $item) {
            $orderLine = array(
                'id' => $item->getId(),
                'variant_id' => $item->getVariantId(),
            );

            if($item->getAdditionalData()) {
                $orderLine['additional_data'] = $item->getAdditionalData();
            }

            $orderLines[] = $orderLine;
        }

        $this->query[] = array(
            'basket' => array(
                'session_id' => $sessionId,
                'order_lines' => $orderLines
            )
        );

        return $this;
    }

    /**
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param Model\BasketItemSet[]    $itemSets
     * @param int    $amount           Amount of items to add.
     *
     * @return $this
     */
    public function addItemSetsToBasket($sessionId, array $itemSets)
    {
        $this->checkSessionId($sessionId);

        $orderLines = array();

        foreach ($itemSets as $itemSet) {
            $orderLine = array(
                'id' => $itemSet->getId(),
                'set_items' => array()
            );

            if($itemSet->getAdditionalData()) {
                $orderLine['additional_data'] = $itemSet->getAdditionalData();
            }


            foreach($itemSet->getItems() as $item) {
                $entry = array(
                    'variant_id' => $item->getVariantId(),
                );

                if($item->getAdditionalData()) {
                    $entry['additional_data'] = $item->getAdditionalData();
                }

                $orderLine['set_items'][] = $entry;
            }

            $orderLines[] = $orderLine;
        }

        $this->query[] = array(
            'basket' => array(
                'session_id' => $sessionId,
                'order_lines' => $orderLines
            )
        );

        return $this;
    }

    /**
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param string $basketItemId  ID of single item or set in the basket
     *
     * @return $this
     */
    public function addToBasket($sessionId, $productVariantId, $basketItemId)
    {
        $this->checkSessionId($sessionId);

        $this->query[] = array(
            'basket' => array(
                'session_id' => $sessionId,
                'order_lines' => array(
                    array(
                        'id' => $basketItemId,
                        'variant_id' => (int)$productVariantId
                    )
                )
            )
        );

        return $this;
    }

    /**
     * @param string   $sessionId   Free to choose ID of the current website visitor.
     * @param string[] $itemIds     array of basket item ids to delete, this can be sets or single items
     *
     * @return $this
     */
    public function removeFromBasket($sessionId, $itemIds)
    {
        $this->checkSessionId($sessionId);

        $orderLines = array();
        
        foreach ($itemIds as $id) {
            $orderLines[] = array('delete' => $id);
        }
        
        $this->query[] = array(
            'basket' => array(
                'session_id' => $sessionId,
                'order_lines' => $orderLines
            )
        );

        return $this;
    }

    /**
     * @param string $sessionId
     * @param Basket $basket
     *
     * @return $this
     */
    public function updateBasket($sessionId, Basket $basket)
    {
        $this->checkSessionId($sessionId);

        $basketQuery = array('session_id'  => $sessionId);

        $orderLines = $basket->getOrderLinesArray();
        if (!empty($orderLines)) {
            $basketQuery['order_lines'] = $orderLines;
        }

        $this->query[] = array(
            'basket' => $basketQuery
        );

        return $this;
    }

    /**
     * @param int[]|string[] $ids either a single category ID as integer or an array of IDs
     *
     * @return $this
     */
    public function fetchCategoriesByIds($ids = null)
    {
        if ($ids === null) {
            $this->query[] = array(
                'category' => null
            );
        } else {
            // we allow to pass a single ID instead of an array
            settype($ids, 'array');
            
            foreach ($ids as $id) {
                if (!is_long($id) && !ctype_digit($id)) {
                    throw new \InvalidArgumentException('A single category ID must be an integer or a numeric string');
                } else if ($id < 1) {
                    throw new \InvalidArgumentException('A single category ID must be greater than 0');
                }
            }

            $ids = array_map('intval', $ids);

            $this->query[] = array(
                'category' => array(
                    'ids' => $ids
                )
            );
        }

        return $this;
    }

    /**
     * @param int $maxDepth -1 <= $maxDepth <= 10,
     *
     * @return $this
     */
    public function fetchCategoryTree($maxDepth = -1)
    {
        if ($maxDepth >= 0 && $maxDepth <= 10) {
            $params = array('max_depth' => $maxDepth);
        } else if($maxDepth > 10 || $maxDepth < -1) {
            throw new \InvalidArgumentException('$maxDepth must be greater than or equal to -1 and less than or equal to 10');
        } else {
            $params = new \stdClass();
        }
        $this->query[] = array(
            'category_tree' => $params,
        );

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
        array $fields = array()
    ) {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $ids = array_map('intval', $ids);

        $this->query[] = array(
            'products' => array(
                'ids' => $ids,
                'fields' => $fields
            )
        );

        return $this;
    }

    /**
     * @param string[] $eans
     * @param array $fields
     *
     * @return $this
     */
    public function fetchProductsByEans(
        array $eans,
        array $fields = array()
    ) {
        $this->query[] = array(
            'products_eans' => array(
                'eans' => $eans,
                'fields' => $fields
            )
        );

        return $this;
    }

   /**
     * @param string|int $id
     *
     * @return $this
     */
    public function fetchOrder($orderId)
    {
        $this->query[] = array(
            'get_order' => array(
                'order_id' => $orderId
            )
        );

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
        $this->checkSessionId($sessionId);

        $args = array();
        $args['session_id'] = $sessionId;
        $args['success_url'] = $successUrl;
        if ($cancelUrl) $args['cancel_url'] = $cancelUrl;
        if ($errorUrl) $args['error_url'] = $errorUrl;
        $this->query[] = array( 'initiate_order' => $args );

        return $this;
    }

    /**
     * @param ProductSearchCriteria $criteria
     *
     * @return $this
     */
    public function fetchProductSearch(ProductSearchCriteria $criteria)
    {
        $this->checkSessionId($criteria->getSessionId());

        $this->query[] = array(
            'product_search' => $criteria->toArray()
        );

        return $this;
    }

    /**
     * @param array $groupIds
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function fetchFacets(array $groupIds)
    {
        if (empty($groupIds)) {
            throw new \InvalidArgumentException('no groupId given');
        }

        $groupIds = array_map('intval', $groupIds);

        $this->query[] = array(
            'facets' => array(
                'group_ids' => $groupIds
            )
        );

        return $this;
    }

    /**
     * @param array $params
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function fetchFacet(array $params)
    {
        if (empty($params)) {
            throw new \InvalidArgumentException('no params given');
        }

        $this->query[] = array('facet' => $params);

        return $this;
    }

    /**
     * @param string $searchword The search string to search for.
     *
     * @return $this
     */
    public function fetchSuggest($searchword)
    {
        $this->query[] = array(
            'suggest' => array(
                'searchword' => $searchword
            )
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function fetchChildApps()
    {
        $this->query[] = array('child_apps' => NULL );

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return json_encode($this->query);
    }

    /**
     * @param $sessionId
     *
     * @throws \InvalidArgumentException
     */
    protected function checkSessionId($sessionId)
    {
        if (!is_string($sessionId)) {
            throw new \InvalidArgumentException('The session id must be a string');
        }
        if (!isset($sessionId[4])) {
            throw new \InvalidArgumentException('The session id must have at least 5 characters');
        }
    }
}