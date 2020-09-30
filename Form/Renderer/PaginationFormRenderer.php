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

use Darvin\AdminBundle\Form\Factory\PaginationFormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Twig\Environment;

/**
 * Pagination form renderer
 */
class PaginationFormRenderer implements PaginationFormRendererInterface
{
    /**
     * @var \Darvin\AdminBundle\Form\Factory\PaginationFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Form\Factory\PaginationFormFactoryInterface $formFactory Pagination form factory
     * @param \Twig\Environment                                               $twig        Twig
     */
    public function __construct(PaginationFormFactoryInterface $formFactory, Environment $twig)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderRepaginateForm(string $entityClass, ?FormInterface $form = null): string
    {
        if (null === $form) {
            $form = $this->formFactory->createRepaginateForm($entityClass);
        }

        return $this->twig->render('@DarvinAdmin/pagination/repaginate.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
