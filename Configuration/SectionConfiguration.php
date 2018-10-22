<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Configuration;

use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Darvin\Utils\ORM\EntityResolverInterface;

/**
 * Section configuration
 */
class SectionConfiguration
{
    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    protected $entityResolver;

    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    protected $objectNamer;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Darvin\AdminBundle\Configuration\Section[]|null
     */
    protected $sections;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface      $entityResolver Entity resolver
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer    Object namer
     * @param array                                          $config         Configuration
     */
    public function __construct(EntityResolverInterface $entityResolver, ObjectNamerInterface $objectNamer, array $config)
    {
        $this->entityResolver = $entityResolver;
        $this->objectNamer = $objectNamer;
        $this->config = $config;

        $this->sections = null;
    }

    /**
     * @param string $entity Entity class
     *
     * @return \Darvin\AdminBundle\Configuration\Section
     * @throws \InvalidArgumentException
     */
    public function getSection(string $entity): Section
    {
        if (!$this->hasSection($entity)) {
            throw new \InvalidArgumentException(sprintf('Section for entity "%s" does not exist.', $entity));
        }

        return $this->getSections()[$entity];
    }

    /**
     * @param string $entity Entity class
     *
     * @return bool
     */
    public function hasSection(string $entity): bool
    {
        $sections = $this->getSections();

        return isset($sections[$entity]);
    }

    /**
     * @return \Darvin\AdminBundle\Configuration\Section[]
     */
    public function getSections(): array
    {
        if (null === $this->sections) {
            $this->sections = [];

            foreach ($this->config as $params) {
                if (!$params['enabled']) {
                    continue;
                }

                $alias  = $params['alias'];
                $entity = $params['entity'];

                if (empty($alias)) {
                    $alias = $this->objectNamer->name($entity);
                }

                $resolvedEntity = $this->entityResolver->resolve($entity);

                $this->sections[$resolvedEntity] = new Section($alias, $resolvedEntity, $params['config']);
            }
        }

        return $this->sections;
    }
}
