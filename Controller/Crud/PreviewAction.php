<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darvin\AdminBundle\Controller\Crud;

use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD controller preview action
 */
class PreviewAction extends AbstractAction
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        return new Response();
    }
}
