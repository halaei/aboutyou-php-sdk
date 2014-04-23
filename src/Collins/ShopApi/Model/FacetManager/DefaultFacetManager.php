<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

class DefaultFacetManager extends AbstractFacetManager
{
    /** @var FetchStrategyInterface */
    protected $fetchStrategy;

    /**
     * @param FetchStrategyInterface $fetchStrategy
     */
    public function __construct(FetchStrategyInterface $fetchStrategy)
    {
        $this->fetchStrategy = $fetchStrategy;
    }

    /**
     * @return FetchStrategyInterface
     */
    public function getFetchStrategy()
    {
        return $this->fetchStrategy;
    }

    /**
     * @param FetchStrategyInterface
     */
    public function setFetchStrategy(FetchStrategyInterface $fetchStrategy)
    {
        $this->fetchStrategy = $fetchStrategy;
    }

    protected function preFetch()
    {
        $this->facets += $this->fetchStrategy->fetch($this->missingFacetGroupIdsAndFacetIds);
        $this->missingFacetGroupIdsAndFacetIds = array();
    }
} 