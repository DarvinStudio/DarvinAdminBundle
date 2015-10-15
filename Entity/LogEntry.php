<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Entity;

use Darvin\UserBundle\Entity\User;
use Darvin\Utils\Mapping\Annotation as Darvin;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * Log entry
 *
 * @ORM\Entity(repositoryClass="Darvin\AdminBundle\Repository\LogEntryRepository")
 * @ORM\Table(name="log", indexes={
 *      @ORM\Index(name="log_class_lookup_idx",   columns={"object_class"}),
 *      @ORM\Index(name="log_date_lookup_idx",    columns={"logged_at"}),
 *      @ORM\Index(name="log_user_lookup_idx",    columns={"username"}),
 *      @ORM\Index(name="log_version_lookup_idx", columns={"object_id", "object_class", "version"}),
 * })
 */
class LogEntry extends AbstractLogEntry
{
    const LOG_ENTRY_CLASS = __CLASS__;

    const ACTION_CREATE = 'create';
    const ACTION_REMOVE = 'remove';
    const ACTION_UPDATE = 'update';

    /**
     * @var array
     */
    private static $actions = array(
        self::ACTION_CREATE => 'log.entity.actions.create',
        self::ACTION_REMOVE => 'log.entity.actions.remove',
        self::ACTION_UPDATE => 'log.entity.actions.update',
    );

    /**
     * @var object
     *
     * @Darvin\CustomObject(classPropertyPath="objectClass", initPropertyValuePath="objectId")
     */
    private $object;

    /**
     * @var \Darvin\UserBundle\Entity\User
     *
     * @Darvin\CustomObject(class="Darvin\UserBundle\Entity\User", initProperty="email", initPropertyValuePath="username")
     */
    private $user;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->loggedAt instanceof \DateTime ? $this->loggedAt->format('d.m.Y H:i:s') : '';
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return isset(self::$actions[$this->action]) ? self::$actions[$this->action] : $this->action;
    }

    /**
     * @return array
     */
    public static function getActions()
    {
        return self::$actions;
    }

    /**
     * @param object $object object
     *
     * @return LogEntry
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param \Darvin\UserBundle\Entity\User $user user
     *
     * @return LogEntry
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Darvin\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
