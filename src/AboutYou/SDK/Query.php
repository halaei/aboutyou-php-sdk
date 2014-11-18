<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK;

use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Criteria\ProductSearchCriteria;
use AboutYou\SDK\Exception\UnexpectedResultException;
use AboutYou\SDK\Factory\ModelFactoryInterface;

class Query extends QueryBuilder
{
    const QUERY_TREE   = 'category tree';
    const QUERY_FACETS = 'all facets';

    /** @var Client */
    protected $client;

    /** @var ModelFactoryInterface */
    protected $factory;

    protected $ghostQuery = array();

    private $allQuery = array();

    /**
     * @param Client       $client
     * @param ModelFactoryInterface $factory
     */
    public function __construct(Client $client, ModelFactoryInterface $factory)
    {
        $this->client  = $client;
        $this->factory = $factory;
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

        $this->requireCategoryTree();
        $this->requireFacets();

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

        $this->requireCategoryTree();
        $this->requireFacets();

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
        array $fields = array(),
        $loadStyles = true
    ) {
        parent::fetchProductsByIds($ids, $fields);

        if (ProductFields::requiresCategories($fields)) {
            $this->requireCategoryTree();
        }
        if (ProductFields::requiresFacets($fields)) {
            $this->requireFacets();
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

        if (ProductFields::requiresCategories($fields)) {
            $this->requireCategoryTree();
        }
        if (ProductFields::requiresFacets($fields)) {
            $this->requireFacets();
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

        if ($criteria->requiresCategories()) {
            $this->requireCategoryTree();
        }
        if ($criteria->requiresFacets()) {
            $this->requireFacets();
        }

        return $this;
    }

    public function requireCategoryTree($fetchForced = false)
    {
        if (!($fetchForced || $this->factory->getCategoryManager()->isEmpty())) {
            return $this;
        }

        $this->ghostQuery[self::QUERY_TREE] = array(
            'category_tree' => array('version' => '2')
        );

        return $this;
    }

    public function requireFacets($fetchForced = false)
    {
        if (!($fetchForced || $this->factory->getFacetManager()->isEmpty())) {
            return $this;
        }

        $this->ghostQuery[self::QUERY_FACETS] = array(
            'facets' => new \stdClass()
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return json_encode(array_values($this->ghostQuery + $this->query));
    }

    /**
     * request the queries and returns an array of the results
     *
     * @return array
     */
    public function execute()
    {
        if (empty($this->query) && empty($this->ghostQuery)) {
            return array();
        }

        $this->allQuery = $this->ghostQuery + $this->query;

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
        'category_tree'  => 'createCategoryTree',
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

    protected function checkResponse($jsonResponse)
    {
        if ($jsonResponse === false ||
            !is_array($jsonResponse) ||
            count($jsonResponse) !== count($this->allQuery)
        ) {
            throw new UnexpectedResultException();
        }

        $currentQueries = array_values($this->allQuery);

        foreach ($jsonResponse as $index => $responseObject) {
            $currentQuery = $currentQueries[$index];
            $responseKey  = key($responseObject);
            $queryKey     = key($currentQuery);

            if ($responseKey !== $queryKey) {
                throw new UnexpectedResultException(
                    'result ' . $queryKey . ' expected, but ' . $responseKey . ' given on position ' . $index .
                    ' - query: ' . json_encode(array($currentQuery))
                );
            }
            if (!isset($this->mapping[$responseKey])) {
                throw new UnexpectedResultException('internal error, ' . $responseKey . ' is unknown result');
            }
        }
    }

    /**
     * returns an array of parsed results
     *
     * @param array $jsonResponse the response body as json array
     * @param bool $isMultiRequest
     *
     * @return array
     *
     * @throws UnexpectedResultException
     */
    protected function parseResult($jsonResponse, $isMultiRequest = true)
    {
        $this->checkResponse($jsonResponse);

        $results = array();
        $currentQueries = array_values($this->allQuery);
        $queryIds = array_keys($this->allQuery);

        foreach ($jsonResponse as $index => $responseObject) {
            $jsonObject     = current($responseObject);
            $currentQuery   = $currentQueries[$index];
            $responseKey    = key($responseObject);
            $queryKey       = key($currentQuery);

            $factory = $this->factory;

            if (isset($jsonObject->error_code)) {
                $result = $factory->preHandleError($jsonObject, $responseKey, $isMultiRequest);
                if ($result !== false) {
                    $results[$responseKey] = $result;
                    continue;
                }
            }

            $query   = $currentQuery[$queryKey];
            $queryId = $queryIds[$index];

            if ($queryId === self::QUERY_FACETS) {
                $factory->updateFacetManager($jsonObject);
            } else if ($queryId === self::QUERY_TREE) {
                $factory->initializeCategoryManager($jsonObject);
            } else {
                $method  = $this->mapping[$responseKey];
                $results[$responseKey] = $factory->$method($jsonObject, $query);
            }
        }

        return $results;
    }
}