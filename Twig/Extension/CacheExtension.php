<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Form\CacheFormFactory;

/**
 * Cache Twig extension
 */
class CacheExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Form\CacheFormFactory
     */
    private $cacheFormFactory;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @param \Darvin\AdminBundle\Form\CacheFormFactory $cacheFormFactory Cache form factory
     */
    public function __construct(CacheFormFactory $cacheFormFactory)
    {
        $this->cacheFormFactory = $cacheFormFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('admin_cache_clear_form', array($this, 'renderCacheClearForm'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @param string $template Template
     *
     * @return string
     */
    public function renderCacheClearForm($template = 'DarvinAdminBundle:cache/widget:clear_form.html.twig')
    {
        return $this->environment->render($template, array(
            'form' => $this->cacheFormFactory->createClearForm()->createView(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_cache_extension';
    }
}
