<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darvin\AdminBundle\Event\Crud;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\UserBundle\Entity\BaseUser;
use Symfony\Component\Form\FormInterface;

/**
 * CRUD form event abstract implementation
 */
abstract class AbstractFormEvent extends AbstractEvent
{
    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     * @param \Darvin\UserBundle\Entity\BaseUser    $user     User
     * @param \Symfony\Component\Form\FormInterface $form     Form
     */
    public function __construct(Metadata $metadata, BaseUser $user, FormInterface $form)
    {
        parent::__construct($metadata, $user);

        $this->form = $form;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
