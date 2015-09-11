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

    const OBJECT_NAME_PREFIX = 'log.object.';

    /**
     * @var object
     *
     * @Darvin\CustomObject(classPropertyPath="objectClass", initProperty="id", initPropertyValuePath="objectId")
     */
    private $object;

    /**
     * @var \Darvin\AdminBundle\Entity\Administrator
     *
     * @Darvin\CustomObject(class="Darvin\AdminBundle\Entity\Administrator", initProperty="username", initPropertyValuePath="username")
     */
    private $administrator;

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
        return 'log.entity.actions.'.$this->action;
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
     * @param \Darvin\AdminBundle\Entity\Administrator $administrator administrator
     *
     * @return LogEntry
     */
    public function setAdministrator(Administrator $administrator = null)
    {
        $this->administrator = $administrator;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Entity\Administrator
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }
}
