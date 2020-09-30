<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Renderer;

use Symfony\Component\Form\FormInterface;

/**
 * Pagination form renderer
 */
interface PaginationFormRendererInterface
{
    /**
     * @param string                                     $entityClass Entity class
     * @param \Symfony\Component\Form\FormInterface|null $form        Repaginate form
     *
     * @return string
     */
    public function renderRepaginateForm(string $entityClass, ?FormInterface $form = null): string;
}
