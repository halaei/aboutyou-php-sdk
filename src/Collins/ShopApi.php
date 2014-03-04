<?php
namespace Collins;

use Collins\Cache\CacheInterface;
use Collins\ShopApi\Constants;
use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Criteria\CriteriaInterface;
use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Factory\ResultFactoryInterface;
use Collins\ShopApi\Model\CategoryTree;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Model\ProductsResult;
use Collins\ShopApi\Query;
use Collins\ShopApi\ShopApiClient;
use Psr\Log\LoggerInterface;

/**
 * Provides access to the Collins Frontend Platform.
 * This class is abstract because it's not meant to be instanciated.
 * All the public methods cover a single API query.
 *
 * @author Antevorte GmbH & Co KG
 */
class ShopApi
{
    const DEFAULT_BASE_IMAGE_URL = 'http://ant-core-staging-media2.wavecloud.de/mmdb/file/';

    /** @var ShopApiClient */
    protected $shopApiClient;

    /** @var string */
    protected $baseImageUrl;

    /** @var ModelFactoryInterface */
    protected $modelFactory;

    /** @var LoggerInterface */
    protected $logger;

    protected $appId;

    /**
     * @param string $appId
     * @param string $appPassword
     * @param string $apiEndPoint
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct($appId, $appPassword, $apiEndPoint = 'stage', CacheInterface $cache = null, LoggerInterface $logger = null)
    {
        $this->shopApiClient = new ShopApiClient($appId, $appPassword, $apiEndPoint, $cache, $logger);

        $this->modelFactory = new DefaultModelFactory($this);

        $this->baseImageUrl = self::DEFAULT_BASE_IMAGE_URL;

        $this->logger = $logger;
        $this->appId  = $appId;
    }

    public function getApiClient()
    {
        return $this->shopApiClient;
    }

    /**
     * @param string $appId        the app id for client authentication
     * @param string $appPassword  the app password/token for client authentication.
     */
    public function setAppCredentials($appId, $appPassword)
    {
        $this->appId = $appId;
        $this->shopApiClient->setAppCredentials($appId, $appPassword);
    }

    /**
     * @return string
     */
    public function getApiEndPoint()
    {
        return $this->shopApiClient->getApiEndPoint();
    }

    /**
     * @param string $apiEndPoint the endpoint can be the string 'stage' or 'live',
     *                            then the default endpoints will be used or
     *                            an absolute url
     */
    public function setApiEndpoint($apiEndPoint)
    {
        $this->shopApiClient->setApiEndpoint($apiEndPoint);
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->shopApiClient->setCache($cache);
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->shopApiClient->getCache();
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->shopApiClient->setLogger($logger);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->shopApiClient->getLogger();
    }

    /**
     * @param string $logTemplate
     *
     * @see http://api.guzzlephp.org/class-Guzzle.Log.MessageFormatter.html
     */
    public function setLogTemplate($logTemplate)
    {
        $this->shopApiClient->setLogTemplate($logTemplate);
    }

    /**
     * @return string
     */
    public function getLogTemplate()
    {
        return $this->shopApiClient->getLogTemplate();
    }

    /**
     * @param null|false|string $baseImageUrl
     */
    public function setBaseImageUrl($baseImageUrl = null)
    {
<<<<<<< HEAD
        if (!$baseImageUrl) {
            $baseImageUrl = 'http://cdn.mary-paul.de/file/';
=======
        if ($baseImageUrl === null) {
            $this->baseImageUrl = self::DEFAULT_BASE_IMAGE_URL;
        } else if (is_string($baseImageUrl)) {
            $this->baseImageUrl = rtrim($baseImageUrl, '/') . '/';
>>>>>>> 6370ae08dcd3da1a29279b901cf8b7d2fed874be
        } else {
            $this->baseImageUrl = '';
        }
    }

    /**
     * @return string
     */
    public function getBaseImageUrl()
    {
<<<<<<< HEAD
        $this->imageUrlTemplate = $imageUrlTemplate
            ?: 'http://cdn.mary-paul.de/file/{{hash}}?width={{width}}&height={{height}}';
=======
        return $this->baseImageUrl;
>>>>>>> 6370ae08dcd3da1a29279b901cf8b7d2fed874be
    }

    /**
     * Returns the result of an auto completion API request.
     * Auto completion searches for products and categories by
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
        $query = $this->getQuery()
            ->fetchAutocomplete($searchword, $limit, $types)
        ;

        return $query->executeSingle();
    }

    public function getQuery()
    {
        $query = new Query($this->shopApiClient, $this->modelFactory);

        return $query;
    }

    /**
     * Fetch the basket of the given sessionId.
     *
     * @param string $sessionId Free to choose ID of the current website visitor.
     *
     * @return \Collins\ShopApi\Model\Basket
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchBasket($sessionId)
    {
        $query = $this->getQuery()->fetchBasket($sessionId);

        return $query->executeSingle();
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
    public function addToBasket($sessionId, $productVariantId, $amount = 1)
    {
        $query = $this->getQuery()
            ->addToBasket($sessionId, $productVariantId, $amount)
        ;

        return $query->executeSingle();
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
        $query = $this->getQuery()
            ->removeFromBasket($sessionId, $productVariantId)
        ;

        return $query->executeSingle();
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
        $query = $this->getQuery()
            ->updateBasketAmount($sessionId, $productVariantId, $amount)
        ;

        return $query->executeSingle();
    }

    /**
     * Returns the result of a category search API request.
     * By passing one or several category ids it will return
     * a result of the categories data.
     *
     * @param mixed $ids either a single category ID as integer or an array of IDs
     *
     * @return \Collins\ShopApi\Model\CategoriesResult
     */
    public function fetchCategoriesByIds($ids)
    {
        // we allow to pass a single ID instead of an array
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $query = $this->getQuery()
            ->fetchCategoriesByIds($ids)
        ;

        $result = $query->executeSingle();

        $notFound = $result->getCategoriesNotFound();
        if (!empty($notFound) && $this->logger) {
            $this->logger->warning('categories not found: appid=' . $this->appId . ' product ids=[' . join(',', $notFound) . ']');
        }

        return $result;
    }

    /**
     * @param int $maxDepth  -1 <= $maxDepth <= 10
     *
     * @return CategoryTree
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchCategoryTree($maxDepth = -1)
    {
        $query = $this->getQuery()
            ->fetchCategoryTree($maxDepth)
        ;

        return $query->executeSingle();
    }

    /**
     * @param integer[] $ids
     * @param string[] $fields
     *
     * @return ProductsResult
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchProductsByIds(
        array $ids,
        array $fields = []
    ) {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $query = $this->getQuery()
            ->fetchProductsByIds($ids, $fields)
        ;

        $result = $query->executeSingle();

        $productsNotFound = $result->getProductsNotFound();
        if (!empty($productsNotFound) && $this->logger) {
            $this->logger->warning('products not found: appid=' . $this->appId . ' product ids=[' . join(',', $productsNotFound) . ']');
        }

        return $result;
    }

    /**
     * @param integer[] $eans
     * @param string[] $fields
     *
     * @return ProductsResult
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchProductsByEans(
        array $eans,
        array $fields = []
    ) {
        // we allow to pass a single ID instead of an array
        settype($eans, 'array');

        $query = $this->getQuery()
            ->fetchProductsByEans($eans, $fields)
        ;

        return $query->executeSingle();
    }

    /**
     * @param ResultFactoryInterface $modelFactory
     */
    public function setResultFactory(ResultFactoryInterface $modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    /**
     * @return ResultFactoryInterface
     */
    public function getResultFactory()
    {
        return $this->modelFactory;
    }

    /**
     * @param ProductSearchCriteria $criteria
     *
     * @return ProductSearchResult
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchProductSearch(
        ProductSearchCriteria $criteria
    ) {
        $query = $this->getQuery()
            ->fetchProductSearch($criteria)
        ;

        return $query->executeSingle();
    }

    /**
     * Fetch the facets of the given groupIds.
     *
     * @param array $groupIds The group ids.
     *
     * @return \Collins\ShopApi\Model\Facet[] With facet id as key.
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchFacets(array $groupIds)
    {
        $query = $this->getQuery()
            ->fetchFacets($groupIds)
        ;

        return $query->executeSingle();
    }

    /**
     * @param string|integer $orderId the order id
     *
     * @return \Collins\ShopApi\Model\Order the order
     */
    public function fetchOrder($orderId)
    {
<<<<<<< HEAD
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
     *
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
=======
        $query = $this->getQuery()
            ->fetchOrder($orderId)
        ;
>>>>>>> 6370ae08dcd3da1a29279b901cf8b7d2fed874be

        return $query->executeSingle();
    }

    /**
     * @param string $sessionId the session id
     * @param string $successUrl callback URL if the order was OK
     * @param string $cancelUrl callback URL if the order was canceled [optional]
     * @param string $errorUrl callback URL if the order had any exceptions [optional]
     *
     * @return \Collins\ShopApi\Model\InitiateOrder
     */
    public function initiateOrder(
        $sessionId,
        $successUrl,
        $cancelUrl = NULL,
        $errorUrl = NULL
    ) {
        $query = $this->getQuery()
            ->InitiateOrder($sessionId, $successUrl, $cancelUrl, $errorUrl)
        ;

        return $query->executeSingle();
    }

    /**
     * Fetch single facets by id and group id
     * For example:
     * $shopApi->fetchFacet([
     *   ["id" => 123, "group_id" => 0 ],
     *   ["id" => 456, "group_id" => 0 ]
     * ]);
     *
     * @param array $params Array of (id, group_id) pairs
     *
     * @return \Collins\ShopApi\Model\Facet[] With facet id as key.
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchFacet(array $params)
    {
        $query = $this->getQuery()
            ->fetchFacet($params)
        ;

        return $query->executeSingle();
    }

    /**
     * Returns the result of a suggest API request.
     * Suggestions are words that are often searched together
     * with the searchword you pass (e.g. "stretch" for "jeans").
     *
     * @param string $searchword The search string to search for.
     *
     * @return array
     */
    public function fetchSuggest($searchword)
    {
        $query = $this->getQuery()
            ->fetchSuggest($searchword)
        ;

        return $query->executeSingle();
    }

    /**
     * Returns the list of child apps
     *
     * @return array
     */
    public function fetchChildApps()
    {
        $query = $this->getQuery()
            ->fetchChildApps()
        ;

        return $query->executeSingle();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * @param string|null $sessionId
     *
     * @return ProductSearchCriteria
     */
    public function getProductSearchCriteria($sessionId = null)
    {
        if (!$sessionId) {
            $sessionId = $this->getSessionId();
        }

        return new ProductSearchCriteria($sessionId);
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
