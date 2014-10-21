<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\ProductSearchResult;


class SaleCounts extends TermsCounts
{
    /** @var integer */
    protected $productCountInSale;

    /** @var integer */
    protected $productCountNotInSale;

    /**
     * @return integer
     */
    public function getProductCountInSale()
    {
        return $this->productCountInSale;
    }

    /**
     * @return integer
     */
    public function getProductCountNotInSale()
    {
        return $this->productCountNotInSale;
    }

    /**
     * @param \stdClass $jsonObject
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject)
    {
        $self = new static($jsonObject->total, $jsonObject->other, $jsonObject->missing);
        $self->parseTerms($jsonObject->terms);

        return $self;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseTerms($jsonTerms)
    {
        foreach ($jsonTerms as $term) {
            if ($term->term === "0") {
                $this->productCountNotInSale = $term->count;
            } else {
                $this->productCountInSale = $term->count;
            }
        }
    }
} 