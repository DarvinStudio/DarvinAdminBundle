<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11.08.15
 * Time: 10:51
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

/**
 * Show link view widget generator
 */
class ShowLinkGenerator extends AbstractWidgetGenerator
{
    const ALIAS = 'show_link';

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
        return 'DarvinAdminBundle:widget:show_link.html.twig';
    }
}
