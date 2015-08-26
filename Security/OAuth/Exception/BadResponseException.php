<?php
/**
 * @author    Lev Semin <lev@darvin-studio.ru>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\OAuth\Exception;

use Darvin\AdminBundle\Security\OAuth\Response\DarvinAuthResponse;

/**
 * Bad response exception
 */
class BadResponseException extends \LogicException
{
    private $needClass;
    private $getClass;

    /**
     * BadResponseException constructor.
     *
     * @param object $object    Object
     * @param string $needClass Need class
     */
    public function __construct($object, $needClass = null)
    {
        $this->needClass = $needClass;
        $this->getClass = is_object($object) ? get_class($object) : 'not object';

        if ($needClass == null) {
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
