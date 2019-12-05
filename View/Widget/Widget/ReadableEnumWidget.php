<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
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
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $value = $this->getPropertyValue($entity, $options['property']);

        if (null === $value) {
            return null;
        }

        return $this->render([
            'value' => $value,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('enum_type')
            ->setAllowedTypes('enum_type', 'string');
    }
}
