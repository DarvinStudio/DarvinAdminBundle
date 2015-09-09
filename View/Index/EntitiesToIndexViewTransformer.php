<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Index;

use Darvin\AdminBundle\Form\Type\BaseType;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\AbstractEntityToViewTransformer;
use Darvin\AdminBundle\View\Index\Body\Body;
use Darvin\AdminBundle\View\Index\Body\BodyRow;
use Darvin\AdminBundle\View\Index\Body\BodyRowItem;
use Darvin\AdminBundle\View\Index\Head\Head;
use Darvin\AdminBundle\View\Index\Head\HeadItem;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Entities to index view transformer
 */
class EntitiesToIndexViewTransformer extends AbstractEntityToViewTransformer
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory Form factory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating Templating
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param array $entities Entities
     *
     * @return \Darvin\AdminBundle\View\Index\IndexView
     * @throws \Darvin\AdminBundle\View\ViewException
     */
    public function transform(array $entities)
    {
        $view = new IndexView();

        if (empty($entities)) {
            return $view
                ->setHead(new Head())
                ->setBody(new Body());
        }

        $firstEntity = reset($entities);

        $meta = $this->metadataManager->getByEntity($firstEntity);

        $this->validateConfiguration($meta, $firstEntity, 'index');

        $view
            ->setHead($this->getHead($meta))
            ->setBody($this->getBody($entities, $meta));

        return $view;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form           Form
     * @param object                                $entity         Entity
     * @param string                                $property       Property name
     * @param array                                 $templateParams Template parameters
     *
     * @return string
     */
    public function renderPropertyForm(FormInterface $form, $entity, $property, array $templateParams = array())
    {
        $formView = $form->createView();

        $templateParams = array_merge(array(
            'entity'         => $entity,
            'form'           => $formView,
            'original_value' => $formView->children[$property]->vars['value'],
            'property'       => $property,
        ), $templateParams);

        return $this->templating->render('DarvinAdminBundle:widget/index/property_form:form.html.twig', $templateParams);
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Darvin\AdminBundle\View\Index\Head\Head
     */
    private function getHead(Metadata $meta)
    {
        $head = new Head();

        $configuration = $meta->getConfiguration();
        $translationPrefix = $meta->getEntityTranslationPrefix();

        foreach ($configuration['view']['index']['fields'] as $field => $attr) {
            $content = $this->translator->trans($translationPrefix.StringsUtil::toUnderscore($field), array(), 'admin');

            $head->addItem($field, new HeadItem($content, empty($attr) && $meta->isMapped($field) && !$meta->isAssociation($field)));
        }

        $head->addItem(
            'action_widgets',
            new HeadItem(
                $this->translator->trans('interface.actions', array(), 'admin'),
                false,
                count($configuration['view']['index']['action_widgets'])
            )
        );

        return $head;
    }

    /**
     * @param array                                 $entities Entities
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     *
     * @return \Darvin\AdminBundle\View\Index\Body\Body
     */
    private function getBody(array $entities, Metadata $meta)
    {
        $body = new Body();

        $configuration = $meta->getConfiguration();
        $mappings = $meta->getMappings();

        $propertyForms = $this->getPropertyForms($meta);

        foreach ($entities as $entity) {
            $bodyRow = new BodyRow();

            foreach ($configuration['view']['index']['fields'] as $field => $attr) {
                if (isset($propertyForms[$field])) {
                    $form = $propertyForms[$field];
                    $form->setData($entity);

                    $content = $this->renderPropertyForm($form, $entity, $field);
                } else {
                    $content = $this->getFieldContent($entity, $field, $attr, $mappings);
                }

                $bodyRow->addItem($field, new BodyRowItem($content));
            }
            foreach ($configuration['view']['index']['action_widgets'] as $widgetGeneratorAlias) {
                $actionWidget = $this->widgetGeneratorPool->get($widgetGeneratorAlias)->generate($entity);

                if (!empty($actionWidget)) {
                    $bodyRow->addItem($widgetGeneratorAlias, new BodyRowItem($actionWidget));
                }
            }

            $body->addRow($bodyRow);
        }

        return $body;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Symfony\Component\Form\FormInterface[]
     */
    private function getPropertyForms(Metadata $meta)
    {
        $forms = array();

        if (!$this->authorizationChecker->isGranted(Permission::EDIT, $meta->getEntityClass())) {
            return $forms;
        }

        $configuration = $meta->getConfiguration();

        foreach ($configuration['view']['index']['fields'] as $field => $attr) {
            if (!empty($attr) || !array_key_exists($field, $configuration['form']['index']['fields'])) {
                continue;
            }

            $forms[$field] = $this->formFactory->create(new BaseType('index', $meta, $field));
        }

        return $forms;
    }
}
