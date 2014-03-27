<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Exception;

class ResultErrorException extends ApiErrorException
{
    /** @var string */
    protected $errorIdent;

    public function __construct(\stdClass $jsonObject)
    {
        $this->errorIdent   = isset($jsonObject->error_ident) ? (string)$jsonObject->error_ident : null;
        $errorCode    = isset($jsonObject->error_code) ? (int)$jsonObject->error_code : 0;
        $errorMessage = isset($jsonObject->error_message) ? $jsonObject->error_message : null;

        parent::__construct(
            is_array($errorMessage) ? join(PHP_EOL, $errorMessage) : $errorMessage,
            $errorCode
        );
    }

    /**
     * @return string
     */
    public function getErrorIdent()
    {
        return $this->errorIdent;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->getMessage();
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->getCode();
    }
}