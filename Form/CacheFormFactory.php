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

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache form factory
 */
class CacheFormFactory
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
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory Form factory
     * @param \Symfony\Component\Routing\RouterInterface   $router      Router
     */
    public function __construct(FormFactoryInterface $formFactory, RouterInterface $router)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createClearForm()
    {
        return $this->formFactory->createNamed('cache_clear', 'form', null, array(
            'action'    => $this->router->generate('darvin_admin_cache_clear'),
            'intention' => md5(__FILE__),
        ));
    }
}
