<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Renderer;

use Symfony\Component\Form\FormInterface;

/**
 * Property form renderer
 */
interface PropertyFormRendererInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface $form        Form
     * @param object                                $entity      Entity
     * @param string                                $entityClass Entity class
     * @param string                                $property    Property name
     *
     * @return string|null
     */
    public function renderPropertyForm(FormInterface $form, object $entity, string $entityClass, string $property): ?string;
}
