<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Darvin\AdminBundle\EventListener\TranslatableSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replace translatable event subscriber compiler pass
 */
class ReplaceTranslatableSubscriberPass implements CompilerPassInterface
{
    const ID = 'knp.doctrine_behaviors.translatable_subscriber';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::ID)) {
            $container->getDefinition(self::ID)
                ->setClass(TranslatableSubscriber::class)
                ->addArgument(new Reference('darvin_utils.orm.entity_resolver'));
        }
    }
}
