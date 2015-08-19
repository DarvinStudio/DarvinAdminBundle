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
