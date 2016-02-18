<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Show;

use Darvin\AdminBundle\Event\Events;
use Darvin\AdminBundle\Event\ShowViewEvent;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\AbstractEntityToViewTransformer;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Entity to show view transformer
 */
class EntityToShowViewTransformer extends AbstractEntityToViewTransformer
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param object                                $entity Entity
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta   Metadata
     *
     * @return \Darvin\AdminBundle\View\Show\ShowView
     */
    public function transform($entity, Metadata $meta)
    {
        $this->validateConfiguration($meta, $entity, 'show');

        $this->eventDispatcher->dispatch(Events::PRE_SHOW_VIEW_CREATING, new ShowViewEvent($entity));

        $view = new ShowView();

        $configuration = $meta->getConfiguration();
        $translationPrefix = $meta->getEntityTranslationPrefix();

        foreach ($configuration['view']['show']['fields'] as $field => $attr) {
            $label = $translationPrefix.StringsUtil::toUnderscore($field);

            $content = $this->getFieldContent($entity, $field, $attr, $meta->getMappings());

            $view->addItem(new Item($label, $content));
        }

        return $view;
    }
}
