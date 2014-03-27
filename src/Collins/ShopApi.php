<?php
namespace Collins;

use Collins\Cache\CacheInterface;
use Collins\ShopApi\Constants;
use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Criteria\CriteriaInterface;
use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Factory\ResultFactoryInterface;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\CategoryTree;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Model\ProductsResult;
use Collins\ShopApi\Query;
use Collins\ShopApi\ShopApiClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Provides access to the Collins Frontend Platform.
 * This class is abstract because it's not meant to be instanciated.
 * All the public methods cover a single API query.
 *
 * @author Collins GmbH & Co KG
 */
class ShopApi
{
    const IMAGE_URL_STAGE = 'http://ant-core-staging-media2.wavecloud.de/mmdb/file';
    const IMAGE_URL_LIVE = 'http://cdn.mary-paul.de/file';

    // basket and product by ids must not be cached, facets should use a different caching strategy
    const NO_QUERY_CACHE = 0;

    // TODO: replace with cache configuration
    protected $queryCacheDuration = 300;

    /** @var ShopApiClient */
    protected $shopApiClient;

    /** @var string */
    protected $baseImageUrl;

    /** @var ModelFactoryInterface */
    protected $modelFactory;

    /** @var LoggerInterface */
    protected $logger;

    protected $appId;

    /** @var  Symfony\Component\EventDispatcher\EventDispatcher */
    static protected $eventDispatcher;

    /**
     * @param string $appId
     * @param string $appPassword
     * @param string $apiEndPoint Constants::API_ENVIRONMENT_LIVE for live environment, Constants::API_ENVIRONMENT_STAGE for staging
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct($appId, $appPassword, $apiEndPoint = Constants::API_ENVIRONMENT_LIVE, CacheInterface $cache = null, LoggerInterface $logger = null)
    {
        $this->shopApiClient = new ShopApiClient($appId, $appPassword, $apiEndPoint, $cache, $logger);

        $this->modelFactory = new DefaultModelFactory($this);

        if ($apiEndPoint === Constants::API_ENVIRONMENT_STAGE) {
            $this->setBaseImageUrl(self::IMAGE_URL_STAGE);
        } else {
            $this->setBaseImageUrl(self::IMAGE_URL_LIVE);
        }

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
     * @param null|false|string $baseImageUrl null will reset to the default url, false to get relative urls, otherwise the url prefix
     */
    public function setBaseImageUrl($baseImageUrl = null)
    {
        if ($baseImageUrl === null) {
            $this->baseImageUrl = self::IMAGE_URL_LIVE;
        } else if (is_string($baseImageUrl)) {
            $this->baseImageUrl = rtrim($baseImageUrl, '/');
        } else {
            $this->baseImageUrl = '';
        }

        $this->modelFactory->setBaseImageUrl($this->baseImageUrl);
    }

    /**
     * @return string
     */
    public function getBaseImageUrl()
    {
        return $this->baseImageUrl;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        $query = new Query($this->shopApiClient, $this->modelFactory);

        return $query;
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

        return $query->executeSingle($this->queryCacheDuration);
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

        return $query->executeSingle(self::NO_QUERY_CACHE);
    }

    /**
     * Add product variant to basket.
     *
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param int    $productVariantId ID of product variant.
     * @param string $basketItemId  ID of single item or set in the basket
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function addToBasket($sessionId, $productVariantId, $basketItemId)
    {
        $query = $this->getQuery()
            ->addToBasket($sessionId, $productVariantId, $basketItemId)
        ;

        return $query->executeSingle(self::NO_QUERY_CACHE);
    }

    /**
     * Adds a single item into the basket.
     * You can specify an amount. Please mind, that an amount > 1 will result in #amount basket positions.
     * So if you read out the basket again later, it's your job to merge the positions again.
     * 
     * @param string $sessionId
     *
     * @param string $sessionId
     * @param \Collins\ShopApi\Model\BasketItem $item
     * @param integer $amount
     *
     * @return Basket
     * @param int $amount
     *
     * @return $this
     */
    public function addItemToBasket($sessionId, ShopApi\Model\BasketItem $item, $amount = 1)
    {
//        $basket = new Basket(null, null);
        $items = array();
        $idPrefix = $item->getId();
        
        for ($i=0; $i<$amount; $i++) {
            $id = $i== 0 ? $idPrefix : ($idPrefix.'-'.($i+1));

            $clone = clone $item;
            $clone->setId($id);
            $items[] = $clone;
        }
        $query = $this->getQuery()->addItemsToBasket($sessionId, $items);

        return $query->executeSingle(self::NO_QUERY_CACHE);
    }

    /**
     * Adds set of product variants into the basket.
     *
     * @param string $sessionId        Free to choose ID of the current website visitor.
     * @param ShopApi\Model\BasketItemSet[] array of sets of basket items
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function addItemSetToBasket($sessionId, ShopApi\Model\BasketItemSet $itemSet)
    {
        return $this->addItemSetsToBasket($sessionId, array($itemSet));
    }

    /**
     * Remove basket item.
     *
     * @param string $sessionId     Free to choose ID of the current website visitor.
     * @param string[] $itemIds     array of basket item ids to delete, this can be sets or single items
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function removeItemsFromBasket($sessionId, $itemIds)
    {
        $query = $this->getQuery()->removeFromBasket($sessionId, $itemIds);

        return $query->executeSingle(self::NO_QUERY_CACHE);
    }

    /**
     * @param string $sessionId
     * @param Basket $basket
     *
     * @return \Collins\ShopApi\Model\Basket
     */
    public function updateBasket($sessionId, Basket $basket)
    {
        $query = $this->getQuery()
            ->updateBasket($sessionId, $basket)
        ;

        return $query->executeSingle(self::NO_QUERY_CACHE);
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
    public function fetchCategoriesByIds($ids = null)
    {
        // we allow to pass a single ID instead of an array
        if ($ids !== null && !is_array($ids)) {
            $ids = array($ids);
        }

        $query = $this->getQuery()
            ->fetchCategoriesByIds($ids)
        ;

        $result = $query->executeSingle($this->queryCacheDuration);

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

        return $query->executeSingle($this->queryCacheDuration);
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
        array $fields = array()
    ) {
        // we allow to pass a single ID instead of an array
        settype($ids, 'array');

        $query = $this->getQuery()
            ->fetchProductsByIds($ids, $fields)
        ;

        $result = $query->executeSingle(self::NO_QUERY_CACHE);

        $productsNotFound = $result->getProductsNotFound();
        if (!empty($productsNotFound) && $this->logger) {
            $this->logger->warning('products not found: appid=' . $this->appId . ' product ids=[' . join(',', $productsNotFound) . ']');
        }

        return $result;
    }

    /**
     * @param string[] $eans
     * @param string[] $fields
     *
     * @return ProductsEansResult
     *
     * @throws ShopApi\Exception\MalformedJsonException
     * @throws ShopApi\Exception\UnexpectedResultException
     */
    public function fetchProductsByEans(
        array $eans,
        array $fields = array()
    ) {
        // we allow to pass a single ID instead of an array
        settype($eans, 'array');

        $query = $this->getQuery()
            ->fetchProductsByEans($eans, $fields)
        ;

        return $query->executeSingle($this->queryCacheDuration);
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

        return $query->executeSingle($this->queryCacheDuration);
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

        return $query->executeSingle(self::NO_QUERY_CACHE);
    }

    /**
     * @param string|integer $orderId the order id
     *
     * @return \Collins\ShopApi\Model\Order the order
     */
    public function fetchOrder($orderId)
    {
        $query = $this->getQuery()
            ->fetchOrder($orderId)
        ;

        return $query->executeSingle(self::NO_QUERY_CACHE);
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
            ->initiateOrder($sessionId, $successUrl, $cancelUrl, $errorUrl)
        ;

        return $query->executeSingle(self::NO_QUERY_CACHE);
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

        return $query->executeSingle(self::NO_QUERY_CACHE);
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

        return $query->executeSingle($this->queryCacheDuration);
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

        return $query->executeSingle($this->queryCacheDuration);
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

    public static function getEventDispatcher()
    {
        if(is_null(self::$eventDispatcher))
        {
            self::$eventDispatcher = new EventDispatcher();
        }

        return(self::$eventDispatcher);
    }
}
