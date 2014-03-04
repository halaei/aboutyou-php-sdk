<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Model\ProductSearchResult\FacetCounts;
use Collins\ShopApi\Model\ProductSearchResult\PriceRange;
use Collins\ShopApi\Model\ProductSearchResult\SaleCounts;

class ProductSearchResult extends AbstractModel
{
    /** @var Product[] */
    protected $products;

    /** @var string */
    protected $pageHash;

    /** @var integer */
    protected $productCount;

    /** @var SaleCounts */
    protected $saleCounts;

    /** @var PriceRange */
    protected $priceRanges;

    /** @var FacetCounts[] */
    protected $facets;

    /** @var Category[] */
    protected $categories;

    /**
     * @var array
     * @deprcated
     */
    protected $rawFacets;

    public function __construct($jsonObject)
    {
        $this->products = [];
        $this->fromJson($jsonObject);
    }

    public function fromJson(\stdClass $jsonObject)
    {
        // workaround for SHOPAPI-278
        $this->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;
        $this->productCount = $jsonObject->product_count;
        $this->rawFacets = $jsonObject->facets;

        $factory = $this->getModelFactory();

        foreach ($jsonObject->products as $key => $jsonProduct) {
            $this->products[$key] = $factory->createProduct($jsonProduct);
        }

        $this->parseFacets($jsonObject->facets);
    }

    /**
     * @return string
     */
    public function getPageHash()
    {
        return $this->pageHash;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    protected function parseFacets($jsonObject)
    {
        $factory = $this->getModelFactory();

        if (isset($jsonObject->categories)) {
            $this->categories = $factory->createCategoriesFacets($jsonObject->categories);
            unset($jsonObject->categories);
        }
        if (isset($jsonObject->prices)) {
            $this->priceRanges = $factory->createPriceRanges($jsonObject->prices);
            unset($jsonObject->prices);
        }
        if (isset($jsonObject->sale)) {
            $this->saleCounts = $factory->createSaleFacet($jsonObject->sale);
            unset($jsonObject->sale);
        }

        $this->facets = $factory->createFacetsCounts($jsonObject);
    }


    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @return array
     */
    public function getRawFacets()
    {
        return $this->rawFacets;
    }

    /**
     * @return PriceRange[]
     */
    public function getPriceRanges()
    {
        return $this->priceRanges;
    }

    /**
     * @return SaleCounts
     */
    public function getSaleCounts()
    {
        return $this->saleCounts;
    }
}