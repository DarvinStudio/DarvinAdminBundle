<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2019-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Factory\Cache\Clear;

use Symfony\Component\Form\FormInterface;

/**
 * List cache clear form factory
 */
interface ListFormFactoryInterface
{
    /**
     * @param array $options Options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createForm(array $options = []): FormInterface;
}
