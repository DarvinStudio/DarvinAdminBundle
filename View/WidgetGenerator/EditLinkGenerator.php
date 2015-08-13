<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11.08.15
 * Time: 9:56
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

/**
 * Edit link view widget generator
 */
class EditLinkGenerator extends AbstractWidgetGenerator
{
    const ALIAS = 'edit_link';

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        return $this->render($options, array(
            'entity'             => $entity,
            'translation_prefix' => $this->metadataManager->getByEntity($entity)->getBaseTranslationPrefix(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return 'DarvinAdminBundle:widget:edit_link.html.twig';
    }
}
