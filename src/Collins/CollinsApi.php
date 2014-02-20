<?php
namespace Collins;

use Collins\Cache\NoCache;
use Collins\ShopApi\Constants;
use Collins\ShopApi\CriteriaInterface;
use Collins\ShopApi\Exception\ApiErrorException;
use Collins\ShopApi\Exception\MalformedJsonException;
use Collins\ShopApi\Exception\UnexpectedResultException;
use Collins\ShopApi\Exception\InvalidParameterException;
use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\CategoryTree;
use Collins\ShopApi\Model\CategoriesResult;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Model\ProductsResult;
use Collins\ShopApi\Model\Autocomplete;
use Collins\ShopApi\Results as Results;
use Collins\ShopApi\ShopApiClient;
use Guzzle\Http\Client;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Psr\Log\LoggerInterface;
use Collins\Cache\CacheInterface;
use Psr\Log\NullLogger;

/**
 * Provides access to the Collins Frontend Platform.
 * This class is abstract because it's not meant to be instanciated.
 * All the public methods cover a single API query.
 *
 * @author Antevorte GmbH & Co KG
 *
 * @deprecated
 */
class CollinsApi extends ShopApi
{

    public function buildImageUrl($id, $extension, $width, $height, $hash)
    {
        $width  = max(min($width, self::MAX_WIDTH), self::MIN_WIDTH);
        $height = max(min($height, self::MAX_WIDTH), self::MIN_WIDTH);

        $this->getImageUrlTemplate();
        $url = '/' . $hash . '?width=' . $width . '&height=' . $height;

        return $url;
    }

    /**
     * Returns the result of a category tree API request.
     * It simply returns the whole category tree of your app.
     *
     * @return \Collins\ShopApi\Results\CategoryTreeResult
     */
    public function getCategoryTree()
    {
        $data = array(
            'category_tree' => (object)null
        );

        return new Results\CategoryTreeResult($this->request($data, 60 * 60), $this);
    }

    /**
     * Returns the result of a facet type API request.
     * It simply returns all the ids of facet groups tat are relevant for your app.
     *
     * @return \Collins\ShopApi\Results\FacetTypeResult
     */
    public function getFacetTypes()
    {
        $data = array(
            'facet_types' => (object)null
        );

        return new Results\FacetTypeResult($this->request($data, 60 * 60), $this);
    }

    /**
     * Initiates an order.
     *
     * @param int $user_session_id free to choose ID of the current website visitor.
     * This is needed here to get the basket of the user.
     * @param string $success_url URL Collins will redirect to after the order
     * is finished.
     * @param string $cancel_url URL Collins will redirect to if the user cancels the order
     * on purpose.
     * @param string $error_url URL Collins will redirect to if the order couldn't be finished.
     * * @return \Collins\ShopApi\Results\InitiateOrderResult
     */
    public function initiateOrder($user_session_id, $success_url, $cancel_url, $error_url)
    {
        $data = array(
            'initiate_order' => array(
                'session_id' => (string)$user_session_id,
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'error_url' => $error_url
            )
        );

        return new Results\InitiateOrderResult($this->request($data), $this);
    }

    /**
     * Returns the result of a live query API request.
     * Use this to check if a product variant is really in stock.
     * This call skips the internal cache and could return a different
     * result than the product request because of this. Don't use
     * this for a lot of products, e.g. on category pages but for
     * single products e.g. before a product is added to the basket.
     *
     * @param mixed $ids either a single product ID as integer or an array of IDs
     * @return \Collins\ShopApi\Results\LiveVariantResult
     */
    public function getLiveVariant($ids)
    {
        // we allow to pass a single ID instead of an array
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $data = array(
            'live_variant' => array(
                'ids' => $ids
            )
        );
        return new Results\LiveVariantResult($this->request($data), $this);
    }

    /**
     * Returns the result of a product search API request.
     * Use this method to search for products you don't know the ID of.
     * If you already know the ID, e.g. on a product detail page, use
     * Collins::getProducts() instead.
     *
     * @param int $user_session_id free to choose ID of the current website visitor.
     * This field is required for tracking reasons.
     * @param array $filter contains data to filter products for
     * @param array $result contains data for reducing the result
     * @return \Collins\ShopApi\Results\ProductSearchResult
     */
    public function getProductSearch(
        $user_session_id,
        array $filter = array(),
        array $result = array(
            'fields' => array(
                'id',
                'name',
                'active',
                'brand_id',
                'description_long',
                'description_short',
                'default_variant',
                'variants',
                'min_price',
                'max_price',
                'sale',
                'default_image',
                'attributes_merged',
                'categories'
            )
        )
    ) {
        $data = array(
            'product_search' => array(
                'session_id' => (string)$user_session_id
            )
        );

        if (count($filter) > 0) {
            $data['product_search']['filter'] = $filter;
        }

        if (count($result) > 0) {
            $data['product_search']['result'] = $result;
        }

        return new Results\ProductSearchResult($this->request($data), $this);
    }

    /**
     * Returns the result of a product get API request.
     * Use this method to get product data of products you already know
     * the ID of. E.g. on a product detail page.
     *
     * @param mixed $ids either a single category ID as integer or an array of IDs
     * @param array $fields fields of product data to be returned
     * @return \Collins\ShopApi\Results\ProductResult
     */
    public function getProducts(
        $ids,
        array $fields = array(
            'id',
            'name',
            'active',
            'brand_id',
            'description_long',
            'description_short',
            'default_variant',
            'variants',
            'min_price',
            'max_price',
            'sale',
            'default_image',
            'attributes_merged',
            'categories'
        )
    ) {
        // we allow to pass a single ID instead of an array
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $data = array(
            'products' => array(
                'ids' => $ids,
                'fields' => $fields
            )
        );

        return new Results\ProductResult($this->request($data), $this);
    }

    /**
     * Returns the result of a product get API request.
     * Use this method to search for products with a given facet
     *
     * @param int $user_session_id free to choose ID of the current website visitor.
     * This field is required for tracking reasons.
     * @param int $facet_group_id ID of the facet group. You can use the Constants::FACET_* constants for this.
     * @param mixed $facets facet ID or array of facet IDs you want to filter for
     * @param array $filter
     * @param array $result contains data for reducing the result
     *
     * @return Results\ProductSearchResult
     */
    public function getProductSearchByFacet(
        $user_session_id,
        $facet_group_id,
        $facets,
        array $filter = array(),
        array $result = array(
            'fields' => array(
                'id',
                'name',
                'active',
                'brand_id',
                'description_long',
                'description_short',
                'default_variant',
                'variants',
                'min_price',
                'max_price',
                'sale',
                'default_image',
                'attributes_merged',
                'categories'
            )
        )
    ) {
        if (!is_array($facets)) {
            $facets = array($facets);
        }

        $filter = array(
            'facets' => array(
                $facet_group_id => $facets
            )
        );

        return self::getProductSearch(
            $user_session_id,
            $filter,
            $result
        );
    }

    /**
     * Builds a JSON string representing the request data via Guzzle.
     * Executes the API request.
     *
     * @param array $data array representing the API request data
     * @param integer $cacheDuration how long to save the response in the cache (if enabled) - 0 = no caching
     *
     * @return \Guzzle\Http\Message\Response response object
     *
     * @throws ApiErrorException will be thrown if response was invalid
     */
    protected function request($data, $cacheDuration = 0)
    {
        $queryString = json_encode([$data]);

        return $this->shopApiClient->request($queryString, $cacheDuration);
    }
}
