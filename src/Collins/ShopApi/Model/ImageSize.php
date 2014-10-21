<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Model;

final class ImageSize
{
    /** @var integer */
    private $width;

    /** @var integer */
    private $height;

    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }
}