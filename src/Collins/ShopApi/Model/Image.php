<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Image
{
    const MIN_WIDTH  = 50;
    const MIN_HEIGHT = 50;
    const MAX_WIDTH  = 1400;
    const MAX_HEIGHT = 2000;

    /** @var string */
    protected $hash;

    /** @var string */
    protected $name;

    /** @var integer */
    protected $filesize;

    /** @var string */
    protected $ext;

    /** @var string */
    protected $mimetype;

    /** @var ImageSize */
    protected $imageSize;

    public function __construct($jsonObject)
    {
        $this->fromJson($jsonObject);
    }

    public function fromJson($jsonObject)
    {
        $this->hash = $jsonObject->hash;
        $this->name = $jsonObject->name;
        $this->filesize = (int)$jsonObject->size;
        $this->ext = $jsonObject->ext;
        $this->mimetype = $jsonObject->mime;

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
    public function getFilesize()
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getImageUrl($width = 200, $height = 0)
    {
        $width = max(min($width, self::MAX_WIDTH), self::MIN_WIDTH);
        $height = max(min($height, self::MAX_WIDTH), self::MIN_WIDTH);
        return '/mmdb/file/' . $this->hash . '?width=' . $width . '&height=' . $height;
    }
} 