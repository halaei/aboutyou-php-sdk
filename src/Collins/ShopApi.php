<?php
namespace Collins;

use Collins\Cache\NoCache;
use Collins\ShopApi\Constants;
use Collins\ShopApi\Exception\ApiErrorException;
use Collins\ShopApi\Exception\UnexpectedResultException;
use Collins\ShopApi\Exception\InvalidParameterException;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\CategoryTree;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\ProductsResult;
use Collins\ShopApi\Model\Autocomplete;
use Collins\ShopApi\Results as Results;
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
 */
class ShopApi
{
    const API_END_POINT_STAGE = 'http://ant-core-staging-s-api1.wavecloud.de/api';
    const API_END_POINT_LIVE  = 'http://ant-shop-api1.wavecloud.de/api';

    /**
     * Guzzle client that is needed to execute API requests.
     * Will be initialized before the first request is done.
     * @var \Guzzle\Http\Client
     */
    protected $guzzleClient = null;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $logTemplate;

    /** @var CacheInterface */
    protected $cache;

    /** @var string */
    protected $appId = null;
    /** @var string */
    protected $appPassword = null;

    /**
     * @var string
     *
     * current end points are:
     * stage: http://ant-core-staging-s-api1.wavecloud.de/api
     * live:  http://ant-shop-api1.wavecloud.de/api
     */
    protected $apiEndPoint;

    /** @var string */
    protected $imageUrlTemplate;

    /**
     * @param string $appId
     * @param string $appPassword
     * @param string $apiEndPoint
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct($appId, $appPassword, $apiEndPoint = 'stage', CacheInterface $cache = null, LoggerInterface $logger = null)
    {
        $this->setAppCredentials($appId, $appPassword);
        $this->setApiEndpoint($apiEndPoint);
        $this->setCache($cache ?: new NoCache());
        $this->setLogger($logger ?: new NullLogger());
        $this->setImageUrlTemplate();
    }

    /**
     * @param string $appId        the app id for client authentication
     * @param string $appPassword  the app password/token for client authentication.
     */
    public function setAppCredentials($appId, $appPassword)
    {
        $this->appId = $appId;
        $this->appPassword = $appPassword;
    }

    /**
     * @return string
     */
    public function getApiEndPoint()
    {
        return $this->apiEndPoint;
    }

    /**
     * @param string $apiEndPoint the endpoint can be the string 'stage' or 'live',
     *                            then the default endpoints will be used or
     *                            an absolute url
     */
    public function setApiEndpoint($apiEndPoint)
    {
        switch ($apiEndPoint) {
            case 'stage':
                $this->apiEndPoint = self::API_END_POINT_STAGE;
                break;
            case 'live':
                $this->apiEndPoint = self::API_END_POINT_STAGE;
                break;
            default:
                $this->apiEndPoint = $apiEndPoint;
        }
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $logTemplate
     *
     * @see http://api.guzzlephp.org/class-Guzzle.Log.MessageFormatter.html
     */
    public function setLogTemplate($logTemplate)
    {
        $this->logTemplate = $logTemplate;
    }

    /**
     * @return string
     */
    public function getLogTemplate()
    {
        return $this->logTemplate;
    }

    public function setBaseImageUrl($baseImageUrl = null)
    {
        if (!$baseImageUrl) {
            $baseImageUrl = 'http://cdn.mary-paul.de/product_images/';
        } else {
            $baseImageUrl = rtrim($baseImageUrl, '/') . '/';
        }
        $this->setImageUrlTemplate($baseImageUrl. '{{path}}/{{id}}_{{width}}_{{height}}{{extension}}');
    }

    /**
     * @param string $imageUrlTemplate
     */
    public function setImageUrlTemplate($imageUrlTemplate = null)
    {
        $this->imageUrlTemplate = $imageUrlTemplate
            ?: 'http://ant-core-staging-media2.wavecloud.de/mmdb/file/{{hash}}?width={{width}}&height={{height}}';
    }

    /**
     * @return string
     */
    public function getImageUrlTemplate()
    {
        return $this->imageUrlTemplate;
    }

    public function buildImageUrl($id, $extension, $width, $height, $hash)
    {
        $path = substr($id, 0, 3);
        $url = str_replace(
            array(
                '{{path}}',
                '{{id}}',
                '{{extension}}',
                '{{width}}',
                '{{height}}',
                '{{hash}}'
            ),
            array(
                $path,
                $id,
                $extension,
                $width,
                $height,
                $hash
            ),
            $this->imageUrlTemplate
        );

        return $url;
    }

    /**
     * Returns the result of an autocompletion API request.
     * Autocompletion searches for products and categories by
     * a given prefix ($searchword).
     *
     * @param string $searchword The prefix search word to search for.
     * @param int    $limit      Maximum number of results.
     * @param array  $types      Array of types to search for (Constants::TYPE_...).
     *
     * @return \Collins\ShopApi\Model\Autocomplete
     */
    public function fetchAutocomplete(
        $searchword,
        $limit = 50,
        $types = array(
            Constants::TYPE_PRODUCTS,
            Constants::TYPE_CATEGORIES
        )
    ) {
        $data = array(
            'autocompletion' => array(
                'searchword' => $searchword,
                'types' => $types,
                'limit' => $limit
            )
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->autocompletion)) {
            throw new UnexpectedResultException();
        }

        return new Autocomplete($jsonObject[0]->autocompletion);
    }

    /**
     * Fetch the basket of the given sessionId.
     *
     * @param string $sessionId Free to choose ID of the current website visitor.
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function fetchBasket($sessionId)
    {
        $data = array(
            'basket_get' => array(
                'session_id' => $sessionId
            )
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->basket_get)) {
            throw new UnexpectedResultException();
        }

        return new Basket($jsonObject[0]->basket_get);
    }

    /**
     * Add product variant to basket.
     *
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param int    $amount           Amount of items to add.
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function addToBasket($sessionId, $productVariantId, $amount = 1) {
        $data = array(
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
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->basket_add)) {
            throw new UnexpectedResultException();
        }

        return new Basket($jsonObject[0]->basket_add);
    }

    /**
     * Remove product variant from basket.
     *
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function removeFromBasket($sessionId, $productVariantId)
    {
        $data = array(
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
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->basket_add)) {
            throw new UnexpectedResultException();
        }

        return new Basket($jsonObject[0]->basket_add);
    }

    /**
     * Update amount product variant in basket.
     *
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param int    $amount           Amount to set.
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function updateBasketAmount($sessionId, $productVariantId, $amount)
    {
        $data = array(
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
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->basket_add)) {
            throw new UnexpectedResultException();
        }

        return new Basket($jsonObject[0]->basket_add);
    }

    /**
     * Returns the result of a category search API request.
     * By passing one or several category ids it will return
     * a result of the categories data.
     *
     * @param mixed $ids either a single category ID as integer or an array of IDs
     * @return \Collins\ShopApi\Results\CategoryResult
     */
    public function getCategories($ids)
    {
        // we allow to pass a single ID instead of an array
        if (!is_array($ids)) {
            $ids = array($ids);
        }


        $data = array(
            'category' => array(
                'ids' => $ids
            )
        );

        return new Results\CategoryResult($this->request($data, 60 * 60), $this);
    }

    public function fetchCategoryTree($maxDepth = -1)
    {
        $data = array(
            'category_tree' => ['max_depth' => $maxDepth],
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->category_tree)) {
            throw new UnexpectedResultException();
        }

        $categoryTree = new CategoryTree($jsonObject[0]->category_tree);

        return $categoryTree;
    }

    public function fetchProductsByIds(
        array $ids,
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
        settype($ids, 'array');

        $data = array(
            'products' => array(
                'ids' => $ids,
                'fields' => $fields
            )
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->products)) {
            throw new UnexpectedResultException();
        }

        $categoryTree = new ProductsResult($jsonObject[0]->products);

        return $categoryTree;
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
     * Fetch the facets of the given groupIds.
     *
     * @param array $groupIds The group ids.
     *
     * @return \Collins\ShopApi\Model\Facet[]
     */
    public function fetchFacets(array $groupIds)
    {
        if (!$groupIds) {
            throw new InvalidParameterException('no groupId given');
        }

        $data = array(
            'facets' => array(
                'group_ids' => $groupIds
            )
        );

        $response = $this->request($data);
        $jsonObject = json_decode($response->getBody(true));

        if ($jsonObject === false || !isset($jsonObject[0]->facets) || !isset($jsonObject[0]->facets->facet)) {
            throw new UnexpectedResultException();
        }

        $facets = array();
        foreach ($jsonObject[0]->facets->facet as $jsonFacet) {
            $facets[] = new Facet($jsonFacet);
        }
        return $facets;
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

    public function setClient(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function getClient()
    {
        if ($this->guzzleClient) {
            return $this->guzzleClient;
        }
        $this->guzzleClient = new Client($this->getApiEndPoint());

        return $this->guzzleClient;
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
        $apiClient = $this->getClient();

        $body = json_encode(array($data));

        $cacheKey = md5($body);

        $response = $this->cache->get($cacheKey);
        if ($response) {
            return $response;
        }

        /** @var EntityEnclosingRequestInterface $request */
        $request = $apiClient->post();
        $request->setBody($body);
        $request->setAuth($this->appId, $this->appPassword);

        if ($this->logger) {
            $adapter = new \Guzzle\Log\PsrLogAdapter($this->logger);
            $logPlugin = new \Guzzle\Plugin\Log\LogPlugin($adapter, $this->logTemplate);

            $request->addSubscriber($logPlugin);
        }

        $response = $request->send();

        $this->cache->set($cacheKey, $response, $cacheDuration);

        if (!$response->isSuccessful() || !is_array($response->json())) {
            throw new ApiErrorException(
                $response->getReasonPhrase(),
                $response->getStatusCode()
            );
        }

        return $response;
    }

    /**
     * Returns the result of a suggest API request.
     * Suggestions are words that are often searched together
     * with the searchword you pass (e.g. "stretch" for "jeans").
     *
     * @param string $searchword the search string to search for
     * @return \Collins\ShopApi\Results\SuggestResult
     */
    public function getSuggest($searchword)
    {
        $data = array(
            'suggest' => array(
                'searchword' => $searchword
            )
        );

        return new Results\SuggestResult($this->request($data), $this);
    }

    /**
     * Returns the URL to the Collins JavaScript file for helper functions
     * to add product variants into the basket of Mary & Paul or auto-resizing
     * the Iframe. This URL may be changed in future, so please use this method instead
     * of hardcoding the URL into your HTML template.
     *
     * @return string URL to the JavaScript file
     */
    public function getJavaScriptURL()
    {
        $url = '//devcenter.mary-paul.de/apps/js/api.js';

        return $url;
    }


    /**
     * Returns a HTML script tag that loads the Collins JavaScript fie.
     *
     * @return string HTML script tag
     */
    public function getJavaScriptTag()
    {
        $tag = '<script type="text/javascript" src="' . self::getJavaScriptURL() . '"></script>';

        return $tag;
    }
}
