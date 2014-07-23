<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi;

use Collins\ShopApi\Criteria\ProductFields;
use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Exception\UnexpectedResultException;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class Query extends QueryBuilder
{
    /** @var ShopApiClient */
    protected $client;

    /** @var ModelFactoryInterface */
    protected $factory;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /**
     * @param ShopApiClient       $client
     * @param ModelFactoryInterface $factory
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(ShopApiClient $client, ModelFactoryInterface $factory, EventDispatcher $eventDispatcher)
    {
        $this->client          = $client;
        $this->factory         = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param string $searchword The prefix search word to search for.
     * @param int    $limit      Maximum number of results.
     * @param array  $types      Array of types to search for (Constants::TYPE_...).
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function fetchAutocomplete(
        $searchword,
        $limit = null,
        array $types = null
    ) {
        parent::fetchAutocomplete($searchword, $limit, $types);

        if ($this->factory->getCategoryManager()->isEmpty()) {
            $this->fetchCategoryTree();
        }

        return $this;
    }

        /**
     * @param string $sessionId Free to choose ID of the current website visitor.
     *
     * @return $this
     */
    public function fetchBasket($sessionId)
    {
        parent::fetchBasket($sessionId);

        if ($this->factory->getCategoryManager()->isEmpty()) {
            $this->fetchCategoryTree();
        }

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
        parent::fetchProductsByIds($ids, $fields);

        if ($this->factory->getCategoryManager()->isEmpty() && in_array(ProductFields::CATEGORIES, $fields)) {
            $this->fetchCategoryTree();
        }

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
        parent::fetchProductsByEans($eans, $fields);

        if ($this->factory->getCategoryManager()->isEmpty() && in_array(ProductFields::CATEGORIES, $fields)) {
            $this->fetchCategoryTree();
        }

        return $this;
    }

    /**
     * @param ProductSearchCriteria $criteria
     *
     * @return $this
     */
    public function fetchProductSearch(ProductSearchCriteria $criteria)
    {
        parent::fetchProductSearch($criteria);

        if ($this->factory->getCategoryManager()->isEmpty() && in_array(ProductFields::CATEGORIES, $criteria->getProductFields())) {
            $this->fetchCategoryTree();
        }

        return $this;
    }


    /**
     * request the queries and returns an array of the results
     *
     * @return \Collins\ShopApi\Model\AbstractModel[]
     */
    public function execute()
    {
        if (empty($this->query)) {
            return array();
        }

        $queryString = $this->getQueryString();

        $response = $this->client->request($queryString);

        $jsonResponse = json_decode($response->getBody(true));

        return $this->parseResult($jsonResponse, count($this->query) > 1);
    }

    /**
     * request the current query and returns the first result
     *
     * @return mixed
     */
    public function executeSingle()
    {
        $result = $this->execute();

        return reset($result);
    }

    protected $mapping = array(
        'autocompletion' => 'createAutocomplete',
        'basket'         => 'createBasket',
        'category'       => 'createCategoriesResult',
        'category_tree'  => 'initializeCategoryManager',
        'facets'         => 'createFacetsList',
        'facet'          => 'createFacetList',
        'facet_types'    => 'createFacetTypes',
        'products'       => 'createProductsResult',
        'products_eans'  => 'createProductsEansResult',
        'product_search' => 'createProductSearchResult',
        'suggest'        => 'createSuggest',
        'get_order'      => 'createOrder',
        'initiate_order' => 'initiateOrder',
        'child_apps'     => 'createChildApps',
        'live_variant'   => 'createVariantsResult'
    );

    /**
     * returns an array of parsed results
     *
     * @param array $jsonResponse the response body as json array
     * @param bool $isMultiRequest
     *
     * @return \Collins\ShopApi\Model\AbstractModel[]
     *
     * @throws UnexpectedResultException
     */
    protected function parseResult($jsonResponse, $isMultiRequest=true)
    {
        if ($jsonResponse === false ||
            !is_array($jsonResponse) ||
            count($jsonResponse) !== count($this->query)
        ) {
            throw new UnexpectedResultException();
        }

        $results = array();

        foreach ($jsonResponse as $index => $responseObject) {
            $currentQuery   = $this->query[$index];
            $jsonObject     = current($responseObject);
            $resultKey      = key($responseObject);
            $queryKey       = key($currentQuery);

            if ($resultKey !== $queryKey) {
                throw new UnexpectedResultException(
                    'result ' . $queryKey . ' expected, but '. $resultKey . ' given on position ' . $index .
                    ' - query: ' . json_encode(array($currentQuery))
                );
            }
            if (!isset($this->mapping[$resultKey])) {
                throw new UnexpectedResultException('internal error, '. $resultKey . ' is unknown result');
            }

            $factory = $this->factory;
            
            if (isset($jsonObject->error_code)) {
                $result = $factory->preHandleError($jsonObject, $resultKey, $isMultiRequest);
                if ($result !== false) {
                    $results[$resultKey] = $result;
                    continue;
                }
            }

            $query = $currentQuery[$queryKey];

            $event = new GenericEvent($jsonObject, array('result' => $resultKey, 'query' => $query));
            $this->eventDispatcher->dispatch('collins.shop_api.' . $resultKey . '_result.create_model.before', $event);

            $method  = $this->mapping[$resultKey];
            $results[$resultKey] = $factory->$method($jsonObject, $query);
        }

        return $results;
    }
}