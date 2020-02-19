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
 * List cache clear form factory
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
    public function createForm(array $options = []): FormInterface
    {
        $commands = $this->cacheClearer->getCommandAliases('list');
        $builder  = $this->genericFormFactory->createNamedBuilder('darvin_admin_cache_clear_list', FormType::class, null, array_merge([
            'action' => $this->router->generate('darvin_admin_cache_clear_list'),
            'attr'   => [
                'autocomplete' => 'off',
            ],
        ], $options));

        $builder->add('commands', ChoiceType::class, [
            'choices'     => array_combine($commands, $commands),
            'expanded'    => true,
            'multiple'    => true,
            'constraints' => new Count(['min' => 1]),
        ]);

        return $builder->getForm();
    }
}
