<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
