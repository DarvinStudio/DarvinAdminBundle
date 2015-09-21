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

use Darvin\AdminBundle\Form\CacheFormManager;

/**
 * Cache Twig extension
 */
class CacheExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Form\CacheFormManager
     */
    private $cacheFormManager;

    /**
     * @param \Darvin\AdminBundle\Form\CacheFormManager $cacheFormManager Cache form manager
     */
    public function __construct(CacheFormManager $cacheFormManager)
    {
        $this->cacheFormManager = $cacheFormManager;
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
     * @return string
     */
    public function renderCacheClearForm()
    {
        return $this->cacheFormManager->renderClearForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_cache_extension';
    }
}
