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

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\AbstractEntityToViewTransformer;
use Darvin\AdminBundle\View\Index\Body\Body;
use Darvin\AdminBundle\View\Index\Body\BodyRow;
use Darvin\AdminBundle\View\Index\Body\BodyRowItem;
use Darvin\AdminBundle\View\Index\Head\Head;
use Darvin\AdminBundle\View\Index\Head\HeadItem;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Entities to index view transformer
 */
class EntitiesToIndexViewTransformer extends AbstractEntityToViewTransformer
{
    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactory
     */
    private $adminFormFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactory $adminFormFactory Admin form factory
     */
    public function setAdminFormFactory(AdminFormFactory $adminFormFactory)
    {
        $this->adminFormFactory = $adminFormFactory;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating Templating
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param array                                 $entities Entities
     *
     * @return \Darvin\AdminBundle\View\Index\IndexView
     * @throws \Darvin\AdminBundle\View\ViewException
     */
    public function transform(Metadata $meta, array $entities)
    {
        $view = new IndexView();

        if (empty($entities)) {
            return $view
                ->setHead(new Head())
                ->setBody(new Body());
        }

        $this->validateConfiguration($meta, reset($entities), 'index');

        $view
            ->setHead($this->getHead($meta))
            ->setBody($this->getBody($meta, $entities));

        return $view;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form        Form
     * @param object                                $entity      Entity
     * @param string                                $entityClass Entity class
     * @param string                                $property    Property name
     *
     * @return string
     */
    public function renderPropertyForm(FormInterface $form, $entity, $entityClass, $property)
    {
        $formView = $form->createView();

        return $this->templating->render('DarvinAdminBundle:Widget/index/property_form:form.html.twig', [
            'entity'         => $entity,
            'entity_class'   => $entityClass,
            'form'           => $formView,
            'original_value' => $formView->children[$property]->vars['value'],
            'property'       => $property,
        ]);
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

        if (!empty($configuration['view']['index']['action_widgets'])) {
            $head->addItem('action_widgets', new HeadItem('interface.actions'));
        }
        foreach ($configuration['view']['index']['fields'] as $field => $attr) {
            if ($this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[view][index]')) {
                continue;
            }

            $content = $translationPrefix.StringsUtil::toUnderscore($field);

            $headItem = new HeadItem($content);

            if (array_key_exists($field, $configuration['sortable_fields'])) {
                $sortablePropertyPath = !empty($configuration['sortable_fields'][$field])
                    ? $configuration['sortable_fields'][$field]
                    : $field;

                $headItem
                    ->setSortable(true)
                    ->setSortablePropertyPath($sortablePropertyPath);
            }

            $head->addItem($field, $headItem);
        }

        return $head;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param array                                 $entities Entities
     *
     * @return \Darvin\AdminBundle\View\Index\Body\Body
     */
    private function getBody(Metadata $meta, array $entities)
    {
        $body = new Body();

        $configuration = $meta->getConfiguration();
        $mappings = $meta->getMappings();

        foreach ($entities as $entity) {
            $bodyRow = new BodyRow($this->buildBodyRowAttr($entity, $meta));

            if (!empty($configuration['view']['index']['action_widgets'])) {
                $actionWidgets = '';

                foreach ($configuration['view']['index']['action_widgets'] as $widgetAlias => $widgetOptions) {
                    $actionWidgets .= $this->widgetPool->getWidget($widgetAlias)->getContent(
                        $entity,
                        $widgetOptions
                    );
                }

                $bodyRow->addItem('action_widgets', new BodyRowItem($actionWidgets));
            }
            foreach ($configuration['view']['index']['fields'] as $field => $attr) {
                if ($this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[view][index]')) {
                    continue;
                }

                $content = null;

                if (!$this->isFieldContentHidden($attr, $entity)) {
                    if (!$this->isPropertyViewField($meta, 'index', $field)
                        || !array_key_exists($field, $configuration['form']['index']['fields'])
                        || $this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[view][index]')
                        || $this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[form][index]')
                    ) {
                        $content = $this->getFieldContent($entity, $field, $attr, $mappings);
                    } else {
                        $form = $this->adminFormFactory->createPropertyForm($meta, $field, $entity);

                        $content = $this->renderPropertyForm($form, $entity, $meta->getEntityClass(), $field);
                    }
                }

                $bodyRow->addItem($field, new BodyRowItem($content));
            }

            $body->addRow($bodyRow);
        }

        $this->normalizeBody($body);

        return $body;
    }

    /**
     * @param object                                $entity Entity
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta   Metadata
     *
     * @return array
     */
    private function buildBodyRowAttr($entity, Metadata $meta)
    {
        $attr = [];

        foreach ($meta->getConfiguration()['index_view_row_attr'] as $property) {
            $attr['data-'.$property] = $this->propertyAccessor->getValue($entity, $property);
        }

        return $attr;
    }

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\Body $body Body to normalize
     */
    private function normalizeBody(Body $body)
    {
        if (empty($body) || 1 === $body->getLength()) {
            return;
        }

        $rows = $body->getRows();

        $firstRow = $rows[0];
        $maxRowLength = $firstRow->getLength();

        $normalizationNeeded = false;

        $rowLengths = [];

        foreach ($rows as $key => $row) {
            $rowLength = $row->getLength();
            $rowLengths[$key] = $rowLength;

            if ($rowLength !== $maxRowLength) {
                $normalizationNeeded = true;
            }
            if ($rowLength > $maxRowLength) {
                $maxRowLength = $rowLength;
            }
        }
        if ($normalizationNeeded) {
            $this->normalizeBodyRows($rows, $rowLengths, $maxRowLength);
        }
    }

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRow[] $rows         Body rows
     * @param array                                         $rowLengths   Body row lengths
     * @param int                                           $maxRowLength Max body row length
     */
    private function normalizeBodyRows(array $rows, array $rowLengths, $maxRowLength)
    {
        foreach ($rowLengths as $key => $rowLength) {
            if ($rowLength === $maxRowLength) {
                continue;
            }
            for ($i = 0; $i < $maxRowLength - $rowLength; $i++) {
                $row = $rows[$key];
                $row->addItem(null, new BodyRowItem());
            }
        }
    }
}
