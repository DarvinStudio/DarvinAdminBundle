<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple link view widget
 */
class SimpleLinkWidget extends AbstractWidget
{
    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $url = $this->getPropertyValue($entity, $options['property']);

        if (null === $url) {
            return null;
        }

        $title       = $url;
        $preparedUrl = $options['add_http_prefix'] && !preg_match('/^https*:\/\//', $url) ? sprintf('http://%s', $url) : $url;

        return sprintf('<a href="%s" target="_blank">%s</a>', $preparedUrl, $title);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('add_http_prefix', false);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
