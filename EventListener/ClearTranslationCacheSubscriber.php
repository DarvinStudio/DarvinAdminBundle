<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Lexik\Bundle\TranslationBundle\Entity\Translation;
use Lexik\Bundle\TranslationBundle\Translation\Translator;
use Psr\Container\ContainerInterface;

/**
 * Clear translation cache event subscriber
 */
class ClearTranslationCacheSubscriber implements EventSubscriber
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @param \Psr\Container\ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args): void
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
    private function getTranslator(): Translator
    {
        return $this->container->get('lexik_translation.translator');
    }
}
