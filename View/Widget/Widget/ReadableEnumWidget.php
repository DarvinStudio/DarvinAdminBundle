<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Readable enum view widget
 */
class ReadableEnumWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options, $property)
    {
        $value = $this->getPropertyValue($entity, $property);

        if (empty($value)) {
            return '';
        }

        return $this->render($options, [
            'value' => $value,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('enum_type')
            ->setAllowedTypes('enum_type', 'string');
    }
}
