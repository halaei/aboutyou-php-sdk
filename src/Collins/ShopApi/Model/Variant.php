<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Constants;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Model\Product;

class Variant
{
    protected $jsonObject;

    /** @var Image[]|null */
    protected $images = null;

    /** @var FacetGroupSet */
    protected $facetGroups;

    /** @var ModelFactoryInterface */
    private $factory;
    
    /** @var Product */
    protected $product;
    
    /**
     * @var Image
     */
    protected $selectedImage = null;

    protected function __construct()
    {
    }

    /**
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     * @param Product $product
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory, Product $product)
    {
        $variant = new static();

        $variant->factory    = $factory;
        $variant->jsonObject = $jsonObject;
        $variant->product    = $product;

        return $variant;
    }        

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->jsonObject->id;
    }

    /**
     * @return Image[]
     */
    public function getImages()
    {
        // parse lazy
        if ($this->images === null) {
            $this->images = array();
            if (!empty($this->jsonObject->images)) {
                $factory = $this->factory;

                foreach ($this->jsonObject->images as $image) {
                    $this->images[] = $factory->createImage($image);
                }
            }
            unset($this->jsonObject->images); // free memory
        }

        return $this->images;
    }
    
    /**    
     * @return string
     */
    public function getAboutNumber()
    {
        return isset($this->jsonObject->about_number) ?
            $this->jsonObject->about_number :
            null
        ;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get image by given hash.
     *
     * @param string $hash The image hash.
     *
     * @return Image
     */
    public function getImageByHash($hash)
    {
        foreach ($this->getImages() as $image) {
            if ($image->getHash() === $hash) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Select a specific image.
     *
     * @param string $hash The image hash or null for default image.
     *
     * @return void
     *
     * @depracted
     */
    public function selectImage($hash)
    {
        if ($hash) {
            $this->selectedImage = $this->getImageByHash($hash);
        } else {
            $this->selectedImage = null;
        }
    }

    /**
     * Get selected or default image.
     *
     * @return Image
     */
    public function getImage()
    {
        if ($this->selectedImage) {
            return $this->selectedImage;
        } else {
            $images = $this->getImages();
            if (isset($images[0])) {
                return $images[0];
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->jsonObject->ean;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->jsonObject->default;
    }

    /**
     * Returns the price in euro cent
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->jsonObject->price;
    }

    /**
     * return integer in euro cent
     */
    public function getOldPrice()
    {
        return $this->jsonObject->old_price;
    }

    /**
     * return integer in euro cent
     */
    public function getRetailPrice()
    {
        return $this->jsonObject->retail_price;
    }

    /**
     * Returns the unstructured additional info
     *
     * return object|null
     */
    public function getAdditionalInfo()
    {
        return
            isset($this->jsonObject->additional_info) ?
            $this->jsonObject->additional_info :
            null
        ;
    }

    /**
     * Returns the quantity of still existing units of this variant.
     * Please mind, that this quantity doesn't need to be up to date.
     * You should check via live_variant for the real quantity before
     * adding a product into the cart.
     *
     * @return int
     */
    public function getQuantity()
    {
        return isset($this->jsonObject->quantity) ?
            $this->jsonObject->quantity :
            0
        ;
    }

    protected static function parseFacetIds($jsonObject)
    {
        $ids = array();
        if (!empty($jsonObject->attributes)) {
            foreach ($jsonObject->attributes as $group => $aIds) {
                $gid = substr($group, 11); // rm prefix "attributs_"
                $ids[$gid] = $aIds;
            }
        }

        return $ids;
    }

    protected function generateFacetGroupSet()
    {
        $ids = self::parseFacetIds($this->jsonObject);
        $this->facetGroups = new FacetGroupSet($ids);
    }

    /**
     * @return array
     */
    public function getFacetIds()
    {
        return self::parseFacetIds($this->jsonObject);
    }

    /**
     * @return FacetGroupSet
     */
    public function getFacetGroupSet()
    {
        if (!$this->facetGroups) {
            $this->generateFacetGroupSet();
        }

        return $this->facetGroups;
    }

    /**
     * @param integer $groupId
     *
     * @return FacetGroup|null
     */
    public function getFacetGroup($groupId)
    {
        $groups = $this->getFacetGroupSet();

        return $groups->getGroup($groupId);
    }

    /**
     * @return \DateTime|null
     */
    public function getFirstActiveDate()
    {
        return isset($this->jsonObject->first_active_date) ?
            new \DateTime($this->jsonObject->first_active_date) :
            null
        ;
    }

    /**
     * @return \DateTime|null
     */
    public function getFirstSaleDate()
    {
        return isset($this->jsonObject->first_sale_date) ?
            new \DateTime($this->jsonObject->first_sale_date) :
            null
        ;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedDate()
    {
        return isset($this->jsonObject->created_date) ?
            new \DateTime($this->jsonObject->created_date) :
            null
        ;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedDate()
    {
        return isset($this->jsonObject->updated_date) ?
            new \DateTime($this->jsonObject->updated_date) :
            null
        ;
    }

    /**
     * @return FacetGroup|null
     */
    public function getColor()
    {
        return $this->getFacetGroup(Constants::FACET_COLOR);
    }

    /**
     * @return FacetGroup|null
     */
    public function getLength()
    {
        return $this->getFacetGroup(Constants::FACET_LENGTH);
    }

    /**
     * @return FacetGroup|null
     */
    public function getSize()
    {
        /**
         * @todo: Instance level caching
         */
        $groupId = $this->getSizeGroupId();

        if (!empty($groupId)) {
            return $this->getFacetGroup($groupId);
        }
    }

    /**
     * @return integer|null
     */
    private function getSizeGroupId()
    {
        $keys = array();

        $groups = $this->getFacetGroupSet()->getGroups();

        if (is_array($groups)) {
            foreach ($groups as $group) {
                $keys[$group->getName()] = $group->getGroupId();
            }
        }

        $sizeRun = $this->getFacetGroup(Constants::FACET_SIZE_RUN);

        if (!empty($sizeRun)) {
            foreach ($sizeRun->getFacets() as $facet) {
                $groupName = $facet->getValue();
                if (isset($keys[$groupName])) {
                    return $keys[$groupName];
                }
            }
        }
        if (isset($keys['size'])) {
            return $keys['size'];
        }
        if (isset($keys['size_run'])) {
            return $keys['size_run'];
        }

        return null;
    }

    /**
     * Returns the quantity per pack for this variant.
     * By default, this returns 1. But some items can have a bigger number.
     *
     * @return int quantity per pack
     */
    public function getQuantityPerPack()
    {
        $facetGroup = $this->getFacetGroup(Constants::FACET_QUANTITY_PER_PACK);

        if (!$facetGroup) {
            return 1;
        }

        $facets = $facetGroup->getFacets();

        if (!$facets) {
            return 1;
        }

        $facet = array_shift($facets);

        return $facet->getValue();
    }

   /**
     * get the season code e.g. "HW 14 / hw14"
     *
     * @return FacetGroup|null
     */
    public function getSeasonCode()
    {
        return $this->getFacetGroup(Constants::FACET_SEASON_CODE);
    }
}
