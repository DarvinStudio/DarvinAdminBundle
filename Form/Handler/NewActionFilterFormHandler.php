<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Handler;

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * New action filter form handler
 */
class NewActionFilterFormHandler
{
    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private $adminFormFactory;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactory    $adminFormFactory Admin form factory
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager  Admin metadata manager
     */
    public function __construct(AdminFormFactory $adminFormFactory, MetadataManager $metadataManager) {
        $this->adminFormFactory = $adminFormFactory;
        $this->metadataManager = $metadataManager;
    }

    /**
     * @param object                                    $entity  Entity
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     */
    public function handleForm($entity, Request $request)
    {
        if (!$request->isMethod('get')) {
            return;
        }

        $meta = $this->metadataManager->getMetadata($entity);

        if (!$meta->isFilterFormEnabled()) {
            return;
        }

        $config = $meta->getConfiguration();

        $fields = $config['form']['new']['fields'];

        foreach ($config['form']['new']['field_groups'] as $fieldGroup) {
            $fields = array_replace($fields, $fieldGroup);
        }
        foreach ($fields as $field => $options) {
            if (!$meta->isAssociation($field) || ClassMetadata::MANY_TO_ONE !== $meta->getMappings()[$field]['type']) {
                unset($fields[$field]);
            }
        }

        $this->adminFormFactory->createFilterForm($meta, null, null, [
            'action'     => null,
            'data'       => $entity,
            'data_class' => $meta->getEntityClass(),
            'fields'     => array_keys($fields),
        ])->handleRequest($request);
    }
}
