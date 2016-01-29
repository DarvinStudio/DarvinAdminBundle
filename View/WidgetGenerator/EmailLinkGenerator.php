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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Email link view widget generator
 */
class EmailLinkGenerator extends AbstractWidgetGenerator
{
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
    protected function generateWidget($entity, array $options)
    {
        if (!$this->isGranted(Permission::VIEW, $entity)) {
            return '';
        }
        if (!$this->propertyAccessor->isReadable($entity, $options['email_property'])) {
            throw new WidgetGeneratorException(
                sprintf('Property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $options['email_property'])
            );
        }

        $email = $this->propertyAccessor->getValue($entity, $options['email_property']);

        if (empty($email)) {
            return '';
        }

        return $this->render($options, array(
            'email' => $email,
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array(
                'email_property',
            ))
            ->setAllowedTypes('email_property', 'string');
    }
}
