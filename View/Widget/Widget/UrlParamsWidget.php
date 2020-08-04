<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * URL params view widget
 */
class UrlParamsWidget extends AbstractWidget
{
    /**
     * {@inheritDoc}
     */
    protected function createContent(object $entity, array $options): ?string
    {
        $url = trim((string)$this->getPropertyValue($entity, $options['property']));

        if ('' === $url) {
            return null;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if (null === $query) {
            return null;
        }

        $params = [];

        foreach (explode('&', $query) as $expr) {
            $parts = explode('=', $expr);

            $params[$parts[0]] = $parts[1] ?? null;
        }
        if (empty($params)) {
            return null;
        }

        return $this->render([
            'params' => $params,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
