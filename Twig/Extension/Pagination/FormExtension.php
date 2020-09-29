<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension\Pagination;

use Darvin\AdminBundle\Form\Renderer\PaginationFormRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Pagination form Twig extension
 */
class FormExtension extends AbstractExtension
{
    /**
     * @var \Darvin\AdminBundle\Form\Renderer\PaginationFormRendererInterface
     */
    private $renderer;

    /**
     * @param \Darvin\AdminBundle\Form\Renderer\PaginationFormRendererInterface $renderer Pagination form renderer
     */
    public function __construct(PaginationFormRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_repaginate_form', [$this->renderer, 'renderRepaginateForm'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
