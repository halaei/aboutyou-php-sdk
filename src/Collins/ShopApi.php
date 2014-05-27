<?php
namespace Collins;

use Collins\ShopApi\Constants;
use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Factory\ResultFactoryInterface;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\CategoryTree;
use Collins\ShopApi\Model\FacetManager\DefaultFacetManager;
use Collins\ShopApi\Model\FacetManager\DoctrineMultiGetCacheStrategy;
use Collins\ShopApi\Model\FacetManager\FetchFacetGroupStrategy;
use Collins\ShopApi\Model\FacetManager\FetchSingleFacetStrategy;
use Collins\ShopApi\Model\ProductsEansResult;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Model\ProductsResult;
use Collins\ShopApi\Query;
use Collins\ShopApi\ShopApiClient;
use Psr\Log\LoggerInterface;
use Rhumsaa\Uuid\Uuid;
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

    /** @var ShopApiClient */
    protected $shopApiClient;

    /** @var string */
    protected $baseImageUrl;

    /** @var ModelFactoryInterface */
    protected $modelFactory;

    /** @var LoggerInterface */
    protected $logger;

    protected $appId;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /**
     * @param string $appId
     * @param string $appPassword
     * @param string $apiEndPoint Constants::API_ENVIRONMENT_LIVE for live environment, Constants::API_ENVIRONMENT_STAGE for staging
     * @param ResultFactoryInterface $resultFactory if null it will use the DefaultModelFactory with the DefaultFacetManager
     * @param LoggerInterface $logger
     * @param \Doctrine\Common\Cache\CacheMultiGet $facetManagerCache
     */
    public function __construct(
        $appId,
        $appPassword,
        $apiEndPoint = Constants::API_ENVIRONMENT_LIVE,
        ResultFactoryInterface $resultFactory = null,
        LoggerInterface $logger = null,
        $facetManagerCache = null
    ) {
        $this->shopApiClient = new ShopApiClient($appId, $appPassword, $apiEndPoint, $logger);

        if ($resultFactory === null) {
            $strategy = new FetchFacetGroupStrategy($this);
            if ($facetManagerCache) {
                $strategy = new DoctrineMultiGetCacheStrategy($facetManagerCache, $strategy);
            }
            $this->setResultFactory(
                new DefaultModelFactory(
                    $this,
                    new DefaultFacetManager($strategy),
                    new EventDispatcher()
                )
            );
        }

        if ($apiEndPoint === Constants::API_ENVIRONMENT_STAGE) {
            $this->setBaseImageUrl(self::IMAGE_URL_STAGE);
        } else {
            $this->setBaseImageUrl(self::IMAGE_URL_LIVE);
        }

        $this->logger = $logger;
        $this->appId  = $appId;
    }



    /**
     * @return ShopApiClient
     */
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
    public function getAppId()
    {
        return $this->appId;
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
        $query = new Query($this->shopApiClient, $this->modelFactory, $this->eventDispatcher);

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

        return $query->executeSingle();
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
     * Adds a single item into the basket.
     * You can specify an amount. Please mind, that an amount > 1 will result in #amount basket positions.
     * So if you read out the basket again later, it's your job to merge the positions again.
     *
     * It is highly recommend to use the basket update method, to manage your items.
     *
     * @param string $sessionId
     * @param integer $variantId
     * @param integer $amount
     *
     * @return Basket
     *
     * @throws \InvalidArgumentException
     */
    public function addItemToBasket($sessionId, $variantId, $amount = 1)
    {
        if (!is_long($variantId)) {
            if (is_string($variantId) && ctype_digit($variantId)) {
                $variantId = intval($variantId);
            } else {
                throw new \InvalidArgumentException('the variant id must be an integer or sting with digits');
            }
        }

        $basket = new Basket();

        for ($i=0; $i < $amount; $i++) {
            $item = new Basket\BasketItem($this->generateBasketItemId(), $variantId);
            $basket->updateItem($item);
        }

        return $this->updateBasket($sessionId, $basket);
    }

    public function generateBasketItemId()
    {
        $id = 'i_' . Uuid::uuid4();

        return $id;
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
        $basket = new Basket();
        $basket->deleteItems($itemIds);

        return $this->updateBasket($sessionId, $basket);
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
    public function fetchCategoriesByIds($ids = null)
    {
        // we allow to pass a single ID instead of an array
        if ($ids !== null && !is_array($ids)) {
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
        array $fields = array()
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

        return $query->executeSingle();
    }

    /**
     * @param ResultFactoryInterface $modelFactory
     */
    public function setResultFactory(ResultFactoryInterface $modelFactory)
    {
        if ($modelFactory instanceof DefaultModelFactory) {
            $this->eventDispatcher = $modelFactory->getEventDispatcher();
        }

        $this->modelFactory = $modelFactory;
    }

    /**
     * @return ResultFactoryInterface|DefaultModelFactory
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

    public function fetchFacetTypes()
    {
        $query = $this->getQuery()
            ->fetchFacetTypes()
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
        $query = $this->getQuery()
            ->fetchOrder($orderId)
        ;

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
            ->initiateOrder($sessionId, $successUrl, $cancelUrl, $errorUrl)
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
        $url = '//developer.aboutyou.de/apps/js/api.js';

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

    /**
     * @private
     * @return array
     */
    public function getFacetGroups()
    {
        return array(
            0   => 'brand',
            1   => 'color',
            5   => 'length',
            206 => 'size_code',
            172 => 'size_run',
            211 => 'channel',
            247 => 'care_symbol',
            173 => 'clothing_unisex_int',
            204 => 'clothing_unisex_onesize',
            175 => 'clothing_womens_de',
            180 => 'clothing_womens_inch',
            194 => 'shoes_unisex_eur',
            189 => 'clothing_mens_inch',
            185 => 'clothing_womens_scotchsoda_81hours',
            187 => 'clothing_mens_de',
            178 => 'clothing_womens_uk',
            183 => 'clothing_womens_us',
            181 => 'clothing_womens_belts_cm',
            190 => 'clothing_mens_belts_cm',
            176 => 'clothing_womens_it',
            192 => 'clothing_mens_acc'
        );
    }
}
