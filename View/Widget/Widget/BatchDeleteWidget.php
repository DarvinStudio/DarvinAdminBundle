<?php declare(strict_types=1);
/**
 * @author    DmitryK limov <FDmnkdd@yandex.ru>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * Batch delete view widget
 */
class BatchDeleteWidget extends AbstractWidget
{
    public const ALIAS = 'batch_delete';

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface $adminRouter Admin router
     */
    public function __construct(AdminRouterInterface $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        if (!$this->adminRouter->exists($entity, AdminRouterInterface::TYPE_BATCH_DELETE)) {
            return null;
        }

        $meta = $this->metadataManager->getMetadata($entity);

        return $this->render([
            'entity'             => $entity,
            'identifier'         => $meta->getIdentifier(),
            'translation_prefix' => $meta->getBaseTranslationPrefix(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::CREATE_DELETE;
    }
}
