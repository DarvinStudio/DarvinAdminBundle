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

use Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Twig\Environment;

/**
 * Widget cache clear form renderer
 */
class WidgetFormRenderer implements WidgetFormRendererInterface
{
    private const DEFAULT_TEMPLATE = '@DarvinAdmin/cache/clear/widget/form.html.twig';

    /**
     * @var \Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Form\Factory\Cache\Clear\WidgetFormFactoryInterface $formFactory Widget cache clear form factory
     * @param \Twig\Environment                                                       $twig        Twig
     */
    public function __construct(WidgetFormFactoryInterface $formFactory, Environment $twig)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderForm(?FormInterface $form = null, ?string $template = null): string
    {
        if (null === $form) {
            $form = $this->formFactory->createForm();
        }
        if (null === $template) {
            $template = self::DEFAULT_TEMPLATE;
        }

        return $this->twig->render($template, [
            'form' => $form->createView(),
        ]);
    }
}
