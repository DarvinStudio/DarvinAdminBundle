<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index;

use Darvin\AdminBundle\Metadata\Metadata;
use Symfony\Component\Form\FormInterface;

/**
 * Index view factory
 */
interface IndexViewFactoryInterface
{
    /**
     * @param object[]                              $entities Entities
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     *
     * @return \Darvin\AdminBundle\View\Factory\Index\IndexView
     */
    public function createView(array $entities, Metadata $meta): IndexView;

    /**
     * @param \Symfony\Component\Form\FormInterface $form        Form
     * @param object                                $entity      Entity
     * @param string                                $entityClass Entity class
     * @param string                                $property    Property name
     *
     * @return string
     */
    public function renderPropertyForm(FormInterface $form, $entity, string $entityClass, string $property): string;
}
