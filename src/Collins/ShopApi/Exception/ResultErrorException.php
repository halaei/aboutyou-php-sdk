<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Exception;

use Collins\ShopApi\Model\ResultErrorTrait;

class ResultErrorException extends ApiErrorException
{
    use ResultErrorTrait;

    public function __construct(\stdClass $jsonObject)
    {
        $this->parseErrorResult($jsonObject);
        // TODO: Remove redundants, eg. errorMessage and message
        parent::__construct(
            is_array($this->errorMessage) ? join(PHP_EOL, $this->errorMessage) : $this->errorMessage,
            $this->errorCode
        );
//        parent::__construct(null);
    }
} 