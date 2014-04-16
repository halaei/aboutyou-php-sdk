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

    public function __construct(FetchStrategyInterface $fetchStrategy)
    {
        $this->fetchStrategy = $fetchStrategy;
    }

    protected function preFetch()
    {
        $this->facets += $this->fetchStrategy->fetch($this->missingFacetGroupIdsAndFacetIds);
        $this->missingFacetGroupIdsAndFacetIds = array();
    }
} 