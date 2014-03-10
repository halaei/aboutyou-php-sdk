<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Image extends AbstractModel
{
    const MIN_WIDTH  = 50;
    const MIN_HEIGHT = 50;
    const MAX_WIDTH  = 1400;
    const MAX_HEIGHT = 2000;

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

    public function __construct($jsonObject)
    {
        $this->fromJson($jsonObject);
    }

    public function fromJson($jsonObject)
    {
        $this->hash     = $jsonObject->hash;
        $this->filesize = (int)$jsonObject->size;
        $this->ext      = $jsonObject->ext;
        $this->mimetype = $jsonObject->mime;
        $this->tags     = isset($jsonObject->tags) ? $jsonObject->tags : null;

        $this->imageSize = new ImageSize((int)$jsonObject->image->width, (int)$jsonObject->image->height);
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
        $api = $this->getShopApi();
        return $api ? $api->getBaseImageUrl() : '';
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