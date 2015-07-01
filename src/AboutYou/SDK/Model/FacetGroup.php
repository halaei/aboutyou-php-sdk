<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model;


class FacetGroup implements FacetUniqueKeyInterface, FacetGetGroupInterface
{
    /** @var Facet[] */
    protected $facets;

    /** @var integer */
    protected $id;

    /** @var string */
    protected $name;

    /**
     * @param integer $id
     * @param string  $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->facets = array();
    }

    /**
     * @param Facet $facet
     */
    public function addFacet(Facet $facet)
    {
        $this->facets[$facet->getId()] = $facet;
    }

    /**
     * @param Facet[] $facets
     */
    public function addFacets(array $facets)
    {
        foreach ($facets as $facet) {
            $this->addFacet($facet);
        }
    }

    /**
     * @param Facet[] $facets
     */
    public function setFacets(array $facets)
    {
        $this->facets = $facets;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getGroupId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns all facet names separated with the given parameter
     * eg. for size with to size facets "36" and "37" -> "36/37"
     *
     * @param string $separator
     *
     * @return string
     */
    public function getFacetNames($separator = '/')
    {
        $names = array();
        foreach ($this->facets as $facet) {
            $names[] = $facet->getName();
        }

        return join($separator, $names);
    }

    /**
     * @return Facet[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * facet groups are equal, if the ids and all child ids are equal
     *
     * @param FacetGroup $facetGroup
     *
     * @return boolean
     */
    public function isEqual(FacetGroup $facetGroup)
    {
        if ($this->id !== $facetGroup->id) return false;

        return $this->getUniqueKey() === $facetGroup->getUniqueKey();
    }

    /**
     * @see isEqual
     *
     * @return string
     */
    public function getUniqueKey()
    {
        $facetIds = array_keys($this->facets);
        sort($facetIds);

        return $this->id . ':' . join(',', $facetIds);
    }

    public function getIds()
    {
        return array(
            $this->id => array_keys($this->facets)
        );
    }


    public function contains(Facet $facet)
    {
        return isset($this->facets[$facet->getId()]);
    }
}
