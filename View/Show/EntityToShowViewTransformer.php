<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 12:38
 */

namespace Darvin\AdminBundle\View\Show;

use Darvin\AdminBundle\View\AbstractEntityToViewTransformer;

/**
 * Entity to show view transformer
 */
class EntityToShowViewTransformer extends AbstractEntityToViewTransformer
{
    /**
     * @param object $entity Entity
     *
     * @return \Darvin\AdminBundle\View\Show\ShowView
     */
    public function transform($entity)
    {
        $meta = $this->metadataManager->getByEntity($entity);

        $this->validateConfiguration($meta, $entity, 'show');

        $view = new ShowView();

        $configuration = $meta->getConfiguration();
        $translationPrefix = $meta->getEntityTranslationPrefix();

        foreach ($configuration['view']['show']['fields'] as $field => $attr) {
            $label = $this->translator->trans($translationPrefix.$field, array(), 'admin');

            $content = $this->getFieldContent($entity, $field, $attr, $meta->getMappings());

            $view->addItem(new Item($label, $content));
        }

        return $view;
    }
}
