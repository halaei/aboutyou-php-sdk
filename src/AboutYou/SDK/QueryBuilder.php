<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK;

use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Criteria\ProductSearchCriteria;
use AboutYou\SDK\Model\Basket;
use AboutYou\SDK\Model\WishList;

class QueryBuilder
{
    /** @var array */
    protected $query = array();

    /**
     * @param string $searchWord The prefix search word to search for.
     * @param int    $limit      Maximum number of results.
     * @param array  $types      Array of types to search for (Constants::TYPE_...).
     * @param array  $categories List of category ids to be included
     *
     * @return $this
     */
    public function fetchAutocomplete(
        $searchWord,
        $limit = null,
        array $types = null,
        array $categories = []
    ) {
        if (!is_string($searchWord)) {
            throw new \InvalidArgumentException('searchword must be a string');
        }

        // strtolower is a workaround of ticket SAPI-532
        $options = [
            'searchword' => mb_strtolower($searchWord, 'UTF-8'),
        ];

        if ($limit !== null) {
            if (!is_int($limit) && !ctype_digit($limit)) {
                throw new \InvalidArgumentException('limit must be an integer');
            }
            $options['limit'] = intval($limit);
        }

        if (!empty($types)) {
            $options['types'] = $types;
        }

        if ($categories) {
            $options['filters']['categories'] = $categories;
        }

        $this->query[] = ['autocompletion' => $options];

        return $this;
    }

    /**
     * @param string $searchWord The prefix search word to search for
     * @param integer[] $categoryIds Array of category Ids for filtering
     * @return $this
     *
     * @throws \InvalidArgumentException
     */

    public function fetchSpellCorrection($searchword, $categoryIds = null)
    {
        if (!is_string($searchword)) {
            throw new \InvalidArgumentException('searchword must be a string');
        }

        $options = array(
            'searchword' => $searchword
        );
        if (!empty($categoryIds)) {
            $options['filter'] = array(
                'categories' => $categoryIds
            );
        }

        $this->query[] = array(
            'did_you_mean' => $options
        );

        return $this;
    }

    /**
     * @param string     $basketId      Free to choose ID of the current website visitor.
     * @param array|null $productFields Product fields to fetch or null for default fields.
     * @param bool       $cleanErrors   Return all errors and then removes them from any further responses
     * @param bool       $refresh       Updates all products and variants
     *
     * @return $this
     */
    public function fetchBasket($basketId, array $productFields = null, $cleanErrors = true, $refresh = true)
    {
        $this->checkBasketId($basketId);

        $basketQuery = array(
            'session_id'   => $basketId,
            'clean_errors' => $cleanErrors,
            'refresh'      => $refresh,
        );

        if ($productFields !== null) {
            $basketQuery['fields'] = $productFields;
        }

        $this->query[] = array(
            'basket' => $basketQuery
        );

        return $this;
    }

    /**
     * @param string     $wishListId    Free to choose ID of the current website visitor.
     * @param array|null $productFields Product fields to fetch or null for default fields.
     *
     * @return $this
     */
    public function fetchWishList($wishListId, array $productFields = null)
    {
        $this->checkWishListId($wishListId);

        $wishlistQuery = array(
            'session_id' => $wishListId
        );

        if ($productFields !== null) {
            $wishlistQuery['fields'] = $productFields;
        }

        $this->query[] = array(
            'wishlist' => $wishlistQuery
        );

        return $this;
    }

    /**
     * @param string $basketId        Free to choose ID of the current website visitor.
     * @param BasketItem[] $items     Array of basket items
     *
     * @return $this
     */
    public function addItemsToBasket($basketId, array $items)
    {
        $this->checkBasketId($basketId);

        $orderLines = array();

        foreach ($items as $item) {
            $orderLine = array(
                'id' => $item->getId(),
                'variant_id' => $item->getVariantId(),
            );

            if ($item->getAdditionalData()) {
                $orderLine['additional_data'] = $item->getAdditionalData();
            }

            $orderLines[] = $orderLine;
        }

        $this->query[] = array(
            'basket' => array(
                'session_id' => $basketId,
                'order_lines' => $orderLines
            )
        );

        return $this;
    }

    /**
     * @param string $basketId        Free to choose ID of the current website visitor.
     * @param BasketItemSet[]         $itemSets
     *
     * @return $this
     */
    public function addItemSetsToBasket($basketId, array $itemSets)
    {
        $this->checkBasketId($basketId);

        $orderLines = array();

        foreach ($itemSets as $itemSet) {
            $orderLine = array(
                'id' => $itemSet->getId(),
                'set_items' => array()
            );

            if ($itemSet->getAdditionalData()) {
                $orderLine['additional_data'] = $itemSet->getAdditionalData();
            }


            foreach ($itemSet->getItems() as $item) {
                $entry = array(
                    'variant_id' => $item->getVariantId(),
                );

                if ($item->getAdditionalData()) {
                    $entry['additional_data'] = $item->getAdditionalData();
                }

                $orderLine['set_items'][] = $entry;
            }

            $orderLines[] = $orderLine;
        }

        $this->query[] = array(
            'basket' => array(
                'session_id' => $basketId,
                'order_lines' => $orderLines
            )
        );

        return $this;
    }

    /**
     * @param string $basketId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param string $basketItemId  ID of single item or set in the basket
     *
     * @return $this
     */
    public function addToBasket($basketId, $productVariantId, $basketItemId)
    {
        $this->checkBasketId($basketId);

        $this->query[] = array(
            'basket' => array(
                'session_id' => $basketId,
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
     * @param string   $basketId   Free to choose ID of the current website visitor.
     * @param string[] $itemIds     array of basket item ids to delete, this can be sets or single items
     *
     * @return $this
     */
    public function removeFromBasket($basketId, $itemIds)
    {
        $this->checkBasketId($basketId);

        $orderLines = array();

        foreach ($itemIds as $id) {
            $orderLines[] = array('delete' => $id);
        }

        $this->query[] = array(
            'basket' => array(
                'session_id' => $basketId,
                'order_lines' => $orderLines
            )
        );

        return $this;
    }

    /**
     * @param string $basketId
     * @param Basket $basket
     * @param bool   $cleanErrors
     * @param bool   $refresh
     *
     * @return $this
     */
    public function updateBasket($basketId, Basket $basket, $cleanErrors = true, $refresh = true)
    {
        $this->checkBasketId($basketId);

        $basketQuery = array(
            'session_id'   => $basketId,
            'clean_errors' => $cleanErrors,
            'refresh'      => $refresh,
        );


        if ($basket->isClearedOnUpdate()) {
            $basketQuery['clear'] = true;
        }

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
     * @param string $wishListId
     * @param WishList $wishList
     *
     * @return $this
     */
    public function updateWishList($wishListId, WishList $wishList)
    {
        $this->checkWishListId($wishListId);

        $wishListQuery = array('session_id'  => $wishListId);

        if ($wishList->isClearedOnUpdate()) {
            $wishListQuery['clear'] = true;
        }

        $orderLines = $wishList->getOrderLinesArray();
        if (!empty($orderLines)) {
            $wishListQuery['order_lines'] = $orderLines;
        }

        $this->query[] = array(
            'wishlist' => $wishListQuery
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
            $ids = array_values($ids);

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
     * @throws \InvalidArgumentException
     *
     * @deprecated use requireCategoryTree and the CategoryManager instead of
     */
    public function fetchCategoryTree($maxDepth = -1)
    {
        if ($maxDepth >= 0 && $maxDepth <= 10) {
            $params = array('max_depth' => $maxDepth);
        } else if ($maxDepth > 10 || $maxDepth < -1) {
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
     * @param boolean $loadStyles styles loaded by default, but it could be
     *
     * @return $this
     */
    public function fetchProductsByIds(
        array $ids,
        array $fields = array(),
        $loadStyles = true
    ) {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $ids = array_map('intval', $ids);

        // make sure the keys are correct to avoid creating an json object instead of array
        $ids = array_values($ids);
        $args = array(
            'ids'    => $ids,
            'fields' => ProductFields::filterFields($fields)
        );
        if ($loadStyles === false) {
            $args['get_styles'] = false;
        }

        $this->query[] = array(
            'products' => $args
        );

        return $this;
    }

    /**
     * @param string[] $keys
     * @param array    $fields
     *
     * @return $this
     */
    public function fetchProductsByStyleKeys(array $keys, array $fields = array()) {
        // we allow to pass a single ID instead of an array
        settype($keys, 'array');

        $keys = array_map('strval', $keys);

        // make sure the keys are correct to avoid creating an json object instead of array
        $keys = array_values($keys);
        $args = array(
            'styles'    => $keys,
            'fields' => ProductFields::filterFields($fields)
        );

        $this->query[] = array(
            'styles' => $args
        );

        return $this;
    }

    /**
     * @param string[]|int[] $ids
     * @param bool           $includeInactive
     * @param bool           $searchInactive
     *
     * @return $this
     */
    public function fetchLiveVariantByIds(array $ids, $includeInactive = true, $searchInactive = false) {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $ids = array_map('intval', $ids);
        $ids = array_values($ids);

        $this->query[] = array(
            'products_variant_ids' => array(
                'version' => '2',
                'variant_ids' => $ids,
                'include_inactive' => (bool)$includeInactive,
                'search_inactive_variants' => (bool)$searchInactive,
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
                'eans'   => $eans,
                'fields' => ProductFields::filterFields($fields),
                'version' => '2'
            )
        );

        return $this;
    }

   /**
     * @param string|int $orderId
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
     * @param string $basketId
     * @param string $successUrl
     * @param string $cancelUrl
     * @param string $errorUrl
     *
     * @return $this
     */
    public function initiateOrder($basketId, $successUrl, $cancelUrl, $errorUrl)
    {
        $this->checkBasketId($basketId);

        $args = array();
        $args['session_id'] = $basketId;
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
        $this->checkBasketId($criteria->getSessionId());

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
    public function fetchFacets(array $groupIds = array())
    {
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
     * @return $this
     */
    public function fetchFacetTypes()
    {
        $this->query[] = array('facet_types' => null);

        return $this;
    }

    /**
     * @param string $searchWord The search string to search for.
     *
     * @return $this
     */
    public function fetchSuggest($searchWord)
    {
        $this->query[] = array(
            'suggest' => array(
                'searchword' => $searchWord
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
        return json_encode(array_values($this->query));
    }

    /**
     * @param $basketId
     *
     * @throws \InvalidArgumentException
     */
    protected function checkBasketId($basketId)
    {
        if (!is_string($basketId)) {
            throw new \InvalidArgumentException('The basket id must be a string');
        }
        if (!isset($basketId{4})) {
            throw new \InvalidArgumentException('The basket id must have at least 5 characters');
        }
    }

    /**
     * @param $wishListId
     *
     * @throws \InvalidArgumentException
     */
    protected function checkWishListId($wishListId)
    {
        if (!is_string($wishListId)) {
            throw new \InvalidArgumentException('The wishList id must be a string');
        }
        if (!isset($wishListId{4})) {
            throw new \InvalidArgumentException('The wishList id must have at least 5 characters');
        }
    }
}
