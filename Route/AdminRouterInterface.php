<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Route;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Admin router
 */
interface AdminRouterInterface
{
    public const OPTION_ENTITY_CLASS = 'admin_entity_class';
    public const OPTION_ROUTE_TYPE   = 'admin_route_type';

    public const TYPE_BATCH_DELETE    = 'batch-delete';
    public const TYPE_COPY            = 'copy';
    public const TYPE_DELETE          = 'delete';
    public const TYPE_EDIT            = 'edit';
    public const TYPE_INDEX           = 'index';
    public const TYPE_NEW             = 'new';
    public const TYPE_PREVIEW         = 'preview';
    public const TYPE_SHOW            = 'show';
    public const TYPE_UPDATE_PROPERTY = 'update-property';

    /**
     * @param object|null $entity         Entity
     * @param string|null $class          Entity class
     * @param string      $routeType      Route type
     * @param array       $params         Parameters
     * @param bool        $preserveFilter Whether to preserve filter data
     *
     * @return string
     */
    public function generateAbsolute(?object $entity = null, ?string $class = null, string $routeType = self::TYPE_SHOW, array $params = [], bool $preserveFilter = true): string;

    /**
     * @param object|null $entity         Entity
     * @param string|null $class          Entity class
     * @param string      $routeType      Route type
     * @param array       $params         Parameters
     * @param mixed       $referenceType  Reference type
     * @param bool        $preserveFilter Whether to preserve filter data
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generate(?object $entity = null, ?string $class = null, string $routeType = self::TYPE_SHOW, array $params = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH, bool $preserveFilter = true): string;

    /**
     * @param object|string $entity    Entity
     * @param string        $routeType Route type
     *
     * @return bool
     */
    public function exists($entity, string $routeType): bool;
}
