<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Crud\Controller;

use Darvin\AdminBundle\Event\Crud\AbstractEvent;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\UserBundle\Entity\BaseUser;

/**
 * CRUD controller event
 */
class ControllerEvent extends AbstractEvent
{
    /**
     * @var string
     */
    private $action;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     * @param \Darvin\UserBundle\Entity\BaseUser    $user     User
     * @param string                                $action   CRUD controller action
     */
    public function __construct(Metadata $metadata, BaseUser $user, string $action)
    {
        parent::__construct($metadata, $user);

        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
