<?php
/**
 * Created by PhpStorm.
 * User: levsemin
 * Date: 25.08.15
 * Time: 18:35
 */

namespace Darvin\AdminBundle\Security\OAuth\Exception;


use Darvin\AdminBundle\Security\OAuth\Response\DarvinAuthResponse;

class BadResponseException extends \LogicException
{
    private $needClass;
    private $getClass;

    /**
     * BadResponseException constructor.
     * @param object $object
     * @param $needClass
     */
    public function __construct($object, $needClass=null)
    {
        $this->needClass = $needClass;
        $this->getClass = is_object($object) ? get_class($object) : 'not object';

        if ($needClass==null){
            $this->needClass = DarvinAuthResponse::CLASS_NAME;
        }

        parent::__construct(sprintf(
            "OAuth response must be instance of %s, %s given",
            $this->needClass,
            $this->getClass
        ));
    }

    /**
     * @return string
     */
    public function getNeedClass()
    {
        return $this->needClass;
    }

    /**
     * @return string
     */
    public function getGetClass()
    {
        return $this->getClass;
    }
}