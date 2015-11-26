<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\Utils\Templating\TemplatingProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache form manager
 */
class CacheFormManager
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Darvin\Utils\Templating\TemplatingProviderInterface
     */
    private $templatingProvider;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface         $formFactory        Form factory
     * @param \Symfony\Component\Routing\RouterInterface           $router             Router
     * @param \Darvin\Utils\Templating\TemplatingProviderInterface $templatingProvider Templating provider
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        TemplatingProviderInterface $templatingProvider
    ) {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->templatingProvider = $templatingProvider;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createClearForm()
    {
        return $this->formFactory->createNamed(
            'cache_clear',
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            null,
            array(
                'action'    => $this->router->generate('darvin_admin_cache_clear'),
                'intention' => md5(__FILE__),
            )
        );
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form Cache clear form
     *
     * @return string
     */
    public function renderClearForm(FormInterface $form = null)
    {
        if (empty($form)) {
            $form = $this->createClearForm();
        }

        return $this->templatingProvider->getTemplating()->render('DarvinAdminBundle:Cache/widget:clear_form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
