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

class Query extends QueryBuilder
{
    const QUERY_TREE   = 'category tree';
    const QUERY_FACETS = 'all facets';

    /** @var ShopApiClient */
    protected $client;

    /** @var ModelFactoryInterface */
    protected $factory;

    protected $additionalQuery = array();

    /**
     * @param ShopApiClient       $client
     * @param ModelFactoryInterface $factory
     */
    public function __construct(ShopApiClient $client, ModelFactoryInterface $factory)
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
        array $fields = array()
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

        $this->query[self::QUERY_TREE] = array(
            'category_tree' => array('version' => '2')
        );

        return $this;
    }

    public function requireFacets($fetchForced = false)
    {
        if (!($fetchForced || $this->factory->getFacetManager()->isEmpty())) {
            return $this;
        }

        $this->query[self::QUERY_FACETS] = array(
            'facets' => new \stdClass()
        );

        return $this;
    }

    /**
     * request the queries and returns an array of the results
     *
     * @return array
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
        if ($jsonResponse === false ||
            !is_array($jsonResponse) ||
            count($jsonResponse) !== count($this->query)
        ) {
            throw new UnexpectedResultException();
        }

        $results = array();
        $currentQueries = array_values($this->query);
        $queryIds = array_keys($this->query);

        foreach ($jsonResponse as $index => $responseObject) {
            $jsonObject     = current($responseObject);
            $currentQuery   = $currentQueries[$index];
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

            $query   = $currentQuery[$queryKey];
            $queryId = $queryIds[$index];

            if ($queryId === self::QUERY_FACETS) {
                $factory->updateFacetManager($jsonObject);
            } else {
                $method  = $this->mapping[$resultKey];
                $results[$resultKey] = $factory->$method($jsonObject, $query);
            }
        }

        return $results;
    }
}