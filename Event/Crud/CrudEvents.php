<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Crud;

/**
 * CRUD events
 */
final class CrudEvents
{
    public const COPIED  = 'darvin_admin.crud.copied';
    public const CREATED = 'darvin_admin.crud.created';
    public const DELETED = 'darvin_admin.crud.deleted';
    public const UPDATED = 'darvin_admin.crud.updated';
}
