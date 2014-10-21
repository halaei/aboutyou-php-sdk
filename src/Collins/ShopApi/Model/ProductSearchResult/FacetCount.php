<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Model\ProductSearchResult;

use Collins\ShopApi\Model\Facet;

class FacetCount
{
    /** @var Facet */
    protected $facet;

    /** @var integer */
    protected $count;

    /**
     * @param Facet   $facet
     * @param integer $count
     */
    public function __construct(Facet $facet, $count)
    {
        $this->facet = $facet;
        $this->count = $count;
    }

    /**
     * @return Facet
     */
    public function getFacet()
    {
        return $this->facet;
    }

    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->count;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->facet->getId();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->facet->getName();
    }

    /**
     * @return integer
     */
    public function getGroupId()
    {
        return $this->facet->getGroupId();
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->facet->getGroupName();
    }
} 