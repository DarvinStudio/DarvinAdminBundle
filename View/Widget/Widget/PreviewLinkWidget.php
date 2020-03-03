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

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ContentBundle\Controller\ContentControllerPoolInterface;
use Darvin\ContentBundle\Controller\ControllerNotExistsException;
use Doctrine\Common\Util\ClassUtils;

/**
 * Preview link view widget
 */
class PreviewLinkWidget extends AbstractWidget
{
    public const ALIAS = 'preview_link';

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Darvin\ContentBundle\Controller\ContentControllerPoolInterface
     */
    private $contentControllerPool;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface                  $adminRouter           Admin router
     * @param \Darvin\ContentBundle\Controller\ContentControllerPoolInterface $contentControllerPool Content controller pool
     */
    public function __construct(AdminRouterInterface $adminRouter, ContentControllerPoolInterface $contentControllerPool)
    {
        $this->adminRouter = $adminRouter;
        $this->contentControllerPool = $contentControllerPool;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent(object $entity, array $options): ?string
    {
        if (!$this->adminRouter->exists($entity, AdminRouterInterface::TYPE_PREVIEW)) {
            return null;
        }
        try {
            $this->contentControllerPool->getController(ClassUtils::getClass($entity));
        } catch (ControllerNotExistsException $ex) {
            return null;
        }

        return $this->render([
            'entity' => $entity,
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
