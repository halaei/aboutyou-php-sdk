<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Image
{
    /** @var string */
    protected $hash;

    /** @var string */
    protected $name;

    /** @var int */
    protected $filesize;

    /** @var string */
    protected $ext;

    /** @var string */
    protected $mimetype;

    /** @var Dimension */
    protected $dimension;

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

        $this->dimension = new Dimension((int)$jsonObject->image->width, (int)$jsonObject->image->height);
    }

    /**
     * @return \Collins\ShopApi\Model\Dimension
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @return int
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

    public function getImageUrl()
    {

    }
} 