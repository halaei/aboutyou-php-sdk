<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi;


use Collins\ShopApi\Factory\DefaultModelFactory;

class Query extends QueryBuilder
{
    /** @var ShopApiClient */
    protected $client;

    /** @var ModelFactoryInterface */
    protected $factory;

    public function __construct(ShopApiClient $client, DefaultModelFactory $factory)
    {
        $this->client  = $client;
        $this->factory = $factory;
    }

    public function execute()
    {
        if (empty($this->query)) {
            return [];
        }

        $queryString = $this->getQueryString();

        $response   = $this->client->request($queryString);
        $jsonResponse = json_decode($response->getBody(true));

        return $this->parseResult($jsonResponse);
    }

    public function executeSingle()
    {
        $result = $this->execute();

        return reset($result);
    }

    protected $mapping = [
        'autocompletion' => 'createAutocomplete',
        'basket_get'     => 'createBasket',
        'basket_add'     => 'createBasket',
        'category'       => 'createCategoriesResult',
        'category_tree'  => 'createCategoryTree',
        'facets'         => 'createFacetList',
        'products'       => 'createProductsResult',
        'product_search' => 'createProductSearchResult',
        'suggest'        => 'createSuggest'
    ];

    protected function parseResult($jsonResponse)
    {
        if ($jsonResponse === false ||
            !is_array($jsonResponse) ||
            count($jsonResponse) !== count($this->query)
        ) {
            throw new UnexpectedResultException();
        }

        $results = [];

        foreach ($jsonResponse as $index => $responseObject) {
            $currentQuery   = $this->query[$index];
            $jsonObject     = current($responseObject);
            $resultKey      = key($responseObject);
            $queryKey       = key($currentQuery);

            if ($resultKey !== $queryKey) {
                throw new UnexpectedResultException('result ' . $queryKey . ' expected, but '. $resultKey . ' given on position ' . $index);
            }

            if (isset($jsonObject->error_code)) {
                // TODO: Log error
                $results[$resultKey] = null;
                continue;
            }

            if (!isset($this->mapping[$resultKey])) {
                echo '<pre>', __LINE__, ') ', __METHOD__, ': <b>$resultKey</b>=', var_export($resultKey), '</pre>', PHP_EOL;
                throw new UnexpectedResultException('internal error, '. $resultKey . ' is unknown result');
            }

            $factory = $this->factory;
            $method  = $this->mapping[$resultKey];
            $results[$resultKey] = $factory->$method($jsonObject, $currentQuery[$queryKey]);
        }

        return $results;
    }
}