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

use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Show link view widget generator
 */
class ShowLinkGenerator extends AbstractWidgetGenerator
{
    const ALIAS = 'show_link';

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        if (isset($options['entity_property'])) {
            $entityProperty = $options['entity_property'];

            if (!$this->propertyAccessor->isReadable($entity, $entityProperty)) {
                throw new WidgetGeneratorException(
                    sprintf('Entity property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $entityProperty)
                );
            }

            $entity = $this->propertyAccessor->getValue($entity, $entityProperty);

            if (empty($entity) || !$this->metadataManager->hasMetadataForEntity($entity)) {
                return '';
            }
        }
        if (!$this->isGranted(Permission::VIEW, $entity)) {
            return '';
        }

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
}
