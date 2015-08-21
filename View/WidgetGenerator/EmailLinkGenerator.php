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

use Doctrine\Common\Util\ClassUtils;
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
    public function generate($entity, array $options = array())
    {
        $this->validate($entity, $options);

        if (!$this->propertyAccessor->isReadable($entity, $options['email_property'])) {
            $message = sprintf(
                'Property "%s::$%s" is not readable. Make sure it has public access.',
                ClassUtils::getClass($entity),
                $options['email_property']
            );

            throw new WidgetGeneratorException($message);
        }

        return $this->render($options, array(
            'email' => $this->propertyAccessor->getValue($entity, $options['email_property']),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'email_link';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return 'DarvinAdminBundle:widget:email_link.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredOptions()
    {
        return array(
            'email_property',
        );
    }
}
