<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Lexik\Bundle\TranslationBundle\Entity\Translation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Clear translation cache event listener
 */
class ClearTranslationCacheListener
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $locales = [];

        foreach ($args->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Translation) {
                $locales[] = $entity->getLocale();
            }
        }
        if (!empty($locales)) {
            $this->getTranslator()->removeLocalesCacheFiles(array_unique($locales));
        }
    }

    /**
     * @return \Lexik\Bundle\TranslationBundle\Translation\Translator
     */
    private function getTranslator()
    {
        return $this->container->get('lexik_translation.translator');
    }
}
