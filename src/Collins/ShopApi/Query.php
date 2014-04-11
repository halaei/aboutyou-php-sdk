<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi;

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
     * request the queries and returns an array of the results
     *
     * @param int $cacheDuration
     *
     * @return array
     */
    public function execute($cacheDuration = 0)
    {
        if (empty($this->query)) {
            return array();
        }

        $queryString = $this->getQueryString();

        $response = $this->client->request($queryString, $cacheDuration);
        
        $jsonResponse = json_decode($response->getBody(true));

        return $this->parseResult($jsonResponse, count($this->query) > 1);
    }

    /**
     * request the current query and returns the first result
     *
     * @param int $cacheDuration
     *
     * @return mixed
     */
    public function executeSingle($cacheDuration = 0)
    {
        $result = $this->execute($cacheDuration);

        return reset($result);
    }

    protected $mapping = array(
        'autocompletion' => 'createAutocomplete',
        'basket'         => 'createBasket',
        'category'       => 'createCategoriesResult',
        'category_tree'  => 'createCategoryTree',
        'facets'         => 'createFacetsList',
        'facet'          => 'createFacetList',
        'products'       => 'createProductsResult',
        'products_eans'  => 'createProductsEansResult',
        'product_search' => 'createProductSearchResult',
        'suggest'        => 'createSuggest',
        'get_order'      => 'createOrder',
        'initiate_order' => 'initiateOrder',
        'child_apps'     => 'createChildApps'
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
                throw new UnexpectedResultException('result ' . $queryKey . ' expected, but '. $resultKey . ' given on position ' . $index);
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
            $this->eventDispatcher->dispatch('collins.shop_api.' . $resultKey . '.create_model.before', $event);

            $method  = $this->mapping[$resultKey];
            $results[$resultKey] = $factory->$method($jsonObject, $query);
        }

        return $results;
    }
}