<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Renderer\Cache;

use Symfony\Component\Form\FormInterface;

/**
 * Cache form renderer interface
 */
interface WidgetFormRendererInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface|null $form Form
     *
     * @return string
     */
    public function renderClearForm(?FormInterface $form = null): string;
}
