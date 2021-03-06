<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Renderer\Cache\Clear;

use Darvin\AdminBundle\Form\Factory\Cache\Clear\ListFormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Twig\Environment;

/**
 * List cache clear form renderer
 */
class ListFormRenderer implements ListFormRendererInterface
{
    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\Clear\ListFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Form\Factory\Cache\Clear\ListFormFactoryInterface $formFactory List cache clear form factory
     * @param \Twig\Environment                                                     $twig        Twig
     */
    public function __construct(ListFormFactoryInterface $formFactory, Environment $twig)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderForm(?FormInterface $form = null): string
    {
        if (null === $form) {
            $form = $this->formFactory->createForm();
        }

        return $this->twig->render('@DarvinAdmin/cache/clear/list/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
