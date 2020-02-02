<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Factory\Cache;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cache form factory
 */
class CacheFormFactory implements CacheFormFactoryInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $genericFormFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $caches;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $genericFormFactory Generic form factory
     * @param \Symfony\Component\Routing\RouterInterface   $router             Router
     */
    public function __construct(FormFactoryInterface $genericFormFactory, RouterInterface $router, array $caches)
    {
        $this->genericFormFactory = $genericFormFactory;
        $this->router = $router;
        $this->caches = $caches;
    }

    /**
     * {@inheritDoc}
     */
    public function createClearForm(): FormInterface
    {
        $builder = $this->genericFormFactory->createNamedBuilder('darvin_admin_cache_clear', FormType::class, null, [
            'csrf_token_id' => md5(__FILE__.__METHOD__.'darvin_admin_cache_clear'),
            'action'        => $this->router->generate('darvin_admin_cache_clear'),
        ]);

        $builder->add('ids', ChoiceType::class, [
            'expanded'     => true,
            'multiple'     => true,
            'choices'      => array_flip($this->caches),
        ]);

        return $builder->getForm();
    }

    /**
     * {@inheritDoc}
     */
    public function createFastClearForm(): FormInterface
    {
        return $this->genericFormFactory->createNamed('darvin_admin_cache_fast_clear', FormType::class, null, [
            'action'        => $this->router->generate('darvin_admin_cache_fast_clear'),
            'csrf_token_id' => md5(__FILE__.__METHOD__.'darvin_admin_cache_fast_clear'),
        ]);
    }
}
