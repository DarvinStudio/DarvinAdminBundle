<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create controllers compiler pass
 */
class CreateControllersPass implements CompilerPassInterface
{
    const PARENT_ID = 'darvin_admin.crud.controller';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = [];

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            $definitions[$section->getControllerId()] = (new ChildDefinition(self::PARENT_ID))
                ->addArgument($section->getEntity());
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private function getSectionConfiguration(ContainerInterface $container)
    {
        return $container->get('darvin_admin.configuration.section');
    }
}
