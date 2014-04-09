<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi;

use Collins\ShopApi\Exception\UnexpectedResultException;
use Collins\ShopApi\Factory\ModelFactoryInterface;

class Query extends QueryBuilder
{
    /** @var ShopApiClient */
    protected $client;

    /** @var ModelFactoryInterface */
    protected $factory;

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
     * request the queries and returns an array of the results
     *
     * @return array
     */
    public function execute($cacheDuration = 0)
    {
        if (empty($this->query)) {
            return array();
        }

        $queryString = $this->getQueryString();

        $response   = $this->client->request($queryString, $cacheDuration);
        
        $jsonResponse = json_decode($response->getBody(true));

        return $this->parseResult($jsonResponse, count($this->query) > 1);
    }

    /**
     * request the current query and returns the first result
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

//            if (isset($jsonObject->error_code)) {
//                $resultKeyClass = preg_replace('/[^a-z]+/i', '', $resultKey);
//                $resultKeyClass = ucfirst(strtolower($resultKeyClass));
//                $resultKeyClass .= 'ResultException';
//
//                $namespace = 'Collins\\ShopApi\\Exception\\';
//                $class = $namespace.'ResultException';
//                if(class_exists($namespace.$resultKeyClass)) {
//                    $class = $namespace.$resultKeyClass;
//                }
//                $message = isset($jsonObject->error_message) ? implode(', ',$jsonObject->error_message) : '';
//                $message .= PHP_EOL.PHP_EOL;
//                $message .= 'Query was: '.json_encode($this->query);
//                $message = trim($message);
//
//                throw new $class($message, $jsonObject->error_code);
//            }

            $factory = $this->factory;
            
            if (isset($jsonObject->error_code)) {
                $result = $factory->preHandleError($jsonObject, $resultKey, $isMultiRequest);
                if ($result !== false) {
                    $results[$resultKey] = $result;
                    continue;
                }
            }

            $method  = $this->mapping[$resultKey];
            $results[$resultKey] = $factory->$method($jsonObject, $currentQuery[$queryKey]);
        }

        return $results;
    }
}