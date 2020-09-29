<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Factory\Pagination;

use Symfony\Component\Form\FormInterface;

/**
 * Pagination form factory
 */
interface PaginationFormFactoryInterface
{
    /**
     * @param string $entityClass Entity class
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRepaginateForm(string $entityClass): FormInterface;
}
