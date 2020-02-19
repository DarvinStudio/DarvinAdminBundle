<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Factory\Cache\Clear;

use Darvin\AdminBundle\Cache\Clear\CacheClearerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Cache form factory
 */
class ListFormFactory implements ListFormFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface
     */
    private $cacheClearer;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $genericFormFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Darvin\AdminBundle\Cache\Clear\CacheClearerInterface $cacheClearer       Cache clearer
     * @param \Symfony\Component\Form\FormFactoryInterface          $genericFormFactory Generic form factory
     * @param \Symfony\Component\Routing\RouterInterface            $router             Router
     */
    public function __construct(CacheClearerInterface $cacheClearer, FormFactoryInterface $genericFormFactory, RouterInterface $router)
    {
        $this->cacheClearer = $cacheClearer;
        $this->genericFormFactory = $genericFormFactory;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function createClearForm(array $options = []): FormInterface
    {
        $builder = $this->genericFormFactory->createNamedBuilder('darvin_admin_cache_clear', FormType::class, null, array_merge([
            'csrf_token_id' => md5(__FILE__.__METHOD__.'darvin_admin_cache_clear'),
            'action'        => $this->router->generate('darvin_admin_cache_clear'),
            'attr'          => [
                'autocomplete' => 'off',
            ],
        ], $options));

        $aliases = $this->cacheClearer->getCommandAliases('list');

        $builder->add('ids', ChoiceType::class, [
            'expanded'    => true,
            'multiple'    => true,
            'choices'     => array_combine($aliases, $aliases),
            'constraints' => new Count([
                'min' => 1,
            ]),
        ]);

        return $builder->getForm();
    }
}
