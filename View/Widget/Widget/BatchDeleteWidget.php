<?php
/**
 * @author    DmitryK limov <FDmnkdd@yandex.ru>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * Batch delete view widget
 */
class BatchDeleteWidget extends AbstractWidget
{
    const ALIAS = 'batch_delete';

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter $adminRouter Admin router
     */
    public function setAdminRouter(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options, $property)
    {
        $meta = $this->metadataManager->getMetadata($entity);

        return $this->adminRouter->exists($entity, AdminRouter::TYPE_BATCH_DELETE)
            ? $this->render($options, [
                'entity'             => $entity,
                'identifier'         => $meta->getIdentifier(),
                'translation_prefix' => $meta->getBaseTranslationPrefix(),
            ])
            : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::CREATE_DELETE,
        ];
    }
}
