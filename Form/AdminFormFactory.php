<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\AdminBundle\Form\Type\BaseType;
use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Admin form factory
 */
class AdminFormFactory
{
    const SUBMIT_EDIT  = 'submit_edit';
    const SUBMIT_INDEX = 'submit_index';
    const SUBMIT_NEW   = 'submit_new';

    /**
     * @var array
     */
    private static $submitButtons = array(
        self::SUBMIT_EDIT  => 'submit.edit',
        self::SUBMIT_INDEX => 'submit.index',
        self::SUBMIT_NEW   => 'submit.new',
    );

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    private $identifierAccessor;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @param object $entity Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createDeleteForm($entity)
    {
        $id = $this->identifierAccessor->getValue($entity);

        $builder = $this->formFactory->createNamedBuilder(
            sprintf('delete_%d', $id),
            'form',
            array(
                'id' => $id,
            ),
            array(
                'action'             => $this->adminRouter->generate($entity, AdminRouter::TYPE_DELETE),
                'intention'          => md5(__FILE__.ClassUtils::getClass($entity).$id),
                'translation_domain' => 'admin',
            )
        )->add('id', 'hidden');

        return $builder->getForm();
    }

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter           $adminRouter        Admin router
     * @param \Symfony\Component\Form\FormFactoryInterface    $formFactory        Form factory
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\MetadataManager    $metadataManager    Metadata manager
     */
    public function __construct(
        AdminRouter $adminRouter,
        FormFactoryInterface $formFactory,
        IdentifierAccessor $identifierAccessor,
        MetadataManager $metadataManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->formFactory = $formFactory;
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
    }

    /**
     * @param object $entity        Entity
     * @param string $action        Action
     * @param string $formAction    Form action
     * @param array  $submitButtons Submit button names
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createEntityForm($entity, $action, $formAction, array $submitButtons)
    {
        $meta = $this->metadataManager->getByEntity($entity);
        $configuration = $meta->getConfiguration();

        $type = $configuration['form'][$action]['type'];

        if (empty($type)) {
            $type = new BaseType($action, $meta);
        }

        $builder = $this->formFactory->createBuilder($type, $entity, array(
            'action'            => $formAction,
            'validation_groups' => array(
                'Default',
                ucfirst($action),
            ),
        ));

        foreach ($submitButtons as $name) {
            $builder->add($name, 'submit', array(
                'label' => self::$submitButtons[$name],
            ));
        }

        return $builder->getForm();
    }
}
