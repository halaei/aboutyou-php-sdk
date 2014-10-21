<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Model\ImageSize;

class Image
{
    const MIN_WIDTH  = 50;
    const MIN_HEIGHT = 50;
    const MAX_WIDTH  = 1400;
    const MAX_HEIGHT = 2000;

    private static $baseUrl = '';

    /** @var null|string[] */
    protected $additionalItems;

    /** @var null|string */
    protected $angle;

    /** @var null|string */
    protected $background;

    /** @var null|string[] */
    protected $color;

    /** @var null|string */
    protected $ext;

    /** @var null|string */
    protected $gender;

    /** @var string */
    protected $hash;

    /** @var ImageSize */
    protected $imageSize;

    /** @var integer */
    protected $filesize;

    /** @var null|string */
    protected $focus;

    /** @var string */
    protected $mimetype;

    /** @var null|\stdClass */
    protected $modelData;

    /** @var null|integer */
    protected $nextDetailLevel;

    /** @var null|string */
    protected $preparation;

    /** @var null|array */
    protected $tags;

    /** @var null|string */
    protected $type;

    /** @var string */
    protected $view;

    protected function __construct()
    {
    }

    /**
     * @param \stdClass $jsonObject
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject)
    {
        $image = new static();

        $image->additionalItems = isset($jsonObject->additional_items) ? $jsonObject->additional_items : null;
        $image->angle           = isset($jsonObject->angle) ? $jsonObject->angle : null;
        $image->background      = isset($jsonObject->background) ? $jsonObject->background : null;
        $image->color           = isset($jsonObject->color) ? $jsonObject->color : null;
        $image->ext             = isset($jsonObject->ext) ? $jsonObject->ext : null;
        $image->filesize        = isset($jsonObject->size) ? (int)$jsonObject->size : 0;
        $image->focus           = isset($jsonObject->focus) ? $jsonObject->focus : null;
        $image->gender          = isset($jsonObject->gender) ? $jsonObject->gender : null;
        $image->hash            = $jsonObject->hash;
        $image->mimetype        = $jsonObject->mime;
        $image->modelData       = isset($jsonObject->model_data) ? $jsonObject->model_data : null;
        $image->nextDetailLevel = isset($jsonObject->next_detail_level) ? $jsonObject->next_detail_level : null;
        $image->preparation     = isset($jsonObject->preparation) ? $jsonObject->preparation : null;
        $image->tags            = isset($jsonObject->tags) ? $jsonObject->tags : null;
        $image->type            = isset($jsonObject->type) ? $jsonObject->type : null;
        $image->view            = isset($jsonObject->view) ? $jsonObject->view : null;


        $image->imageSize = new ImageSize((int)$jsonObject->image->width, (int)$jsonObject->image->height);

        return $image;
    }

    /**
     * returns null, if not set or an array of EAN codes
     *
     * @return null|\string[]
     */
    public function getAdditionalItems()
    {
        return $this->additionalItems;
    }

    /**
     * returns null, if not set or some of "rigth", "left"
     *
     * @return null|string
     */
    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * returns null, if not set or e.g. "grey", "white", "ambience" or "transparent"
     *
     * @return null|string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @return null|string[]
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return null|string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @return integer
     */
    public function getFileSize()
    {
        return $this->filesize;
    }

    /**
     * returns null, if not set or some of "combination", "product", "detail", "haptic"
     *
     * @return null|string
     */
    public function getFocus()
    {
        return $this->focus;
    }

    /**
     * returns null, if not set or some of "female", "male"
     *
     * @return null|string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return null|string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return ImageSize
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    /**
     * returns null, if not set or an object with human model specific data, e.g.
     * - firstname (string)
     * - lastname (string)
     * - gender ("female" or "female)
     * - height (integer)
     * - chest (integer)
     * - waist (integer)
     * - hips (integer)
     * - arms (integer)
     * - legs (integer)
     * all sizes are in metric unit mm
     *
     *
     * @return null|\stdClass
     */
    public function getModelData()
    {
        return $this->modelData;
    }

    /**
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * several pictures of one product may have the same detail_level
     * from small to large
     *
     * @return null|integer
     */
    public function getNextDetailLevel()
    {
        return $this->nextDetailLevel;
    }

    /**
     * returns null, if not set or some of "draped", "pleated", "opened"
     *
     * @return null|string
     */
    public function getPreparation()
    {
        return $this->preparation;
    }

    /**
     * @return null|array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * returns null, if not set or some of "model", "bust", "tray"
     *
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * returns null, if not set or some of "front", "back", "side", "top", "bottom"
     *
     * @return null|string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return string returns the relative url
     */
    public function getUrl($width = 200, $height = 200)
    {
        $width = max(min($width, self::MAX_WIDTH), self::MIN_WIDTH);
        $height = max(min($height, self::MAX_WIDTH), self::MIN_WIDTH);

        return $this->getBaseUrl() . '/' . $this->hash . '?width=' . $width . '&height=' . $height;
    }

    public function getBaseUrl()
    {
        return self::$baseUrl;
    }

    public static function setBaseUrl($baseUrl = '')
    {
        self::$baseUrl = $baseUrl ?: '';
    }
}