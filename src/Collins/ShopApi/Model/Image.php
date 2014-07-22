<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;

class Image
{
    const MIN_WIDTH  = 50;
    const MIN_HEIGHT = 50;
    const MAX_WIDTH  = 1400;
    const MAX_HEIGHT = 2000;

    private static $baseUrl = '';

    /** @var string */
    protected $hash;

    /** @var integer */
    protected $filesize;

    /** @var string */
    protected $ext;

    /** @var string */
    protected $mimetype;

    /** @var ImageSize */
    protected $imageSize;

    /** @var array|null */
    protected $tags;

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

        $image->hash     = $jsonObject->hash;
        $image->filesize = (int)$jsonObject->size;
        $image->ext      = $jsonObject->ext;
        $image->mimetype = $jsonObject->mime;
        $image->tags     = isset($jsonObject->tags) ? $jsonObject->tags : null;

        $image->imageSize = new ImageSize((int)$jsonObject->image->width, (int)$jsonObject->image->height);

        return $image;
    }

    /**
     * @return \Collins\ShopApi\Model\ImageSize
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getBaseUrl()
    {
        return self::$baseUrl;
    }

    public static function setBaseUrl($baseUrl = '')
    {
        self::$baseUrl = $baseUrl ?: '';
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return string returns the relative url
     */
    public function getUrl($width = 200, $height = 0)
    {
        $width = max(min($width, self::MAX_WIDTH), self::MIN_WIDTH);
        $height = max(min($height, self::MAX_WIDTH), self::MIN_WIDTH);

        return $this->getBaseUrl() . '/' . $this->hash . '?width=' . $width . '&height=' . $height;
    }
}