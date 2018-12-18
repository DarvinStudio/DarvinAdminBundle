<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index;

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\Factory\AbstractViewFactory;
use Darvin\AdminBundle\View\Factory\Index\Body\Body;
use Darvin\AdminBundle\View\Factory\Index\Body\BodyRow;
use Darvin\AdminBundle\View\Factory\Index\Body\BodyRowItem;
use Darvin\AdminBundle\View\Factory\Index\Head\Head;
use Darvin\AdminBundle\View\Factory\Index\Head\HeadItem;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Index view factory
 */
class IndexViewFactory extends AbstractViewFactory implements IndexViewFactoryInterface
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
     * @param \Darvin\AdminBundle\Form\AdminFormFactory                                    $adminFormFactory     Admin form factory
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Symfony\Component\Templating\EngineInterface                                $templating           Templating
     */
    public function __construct(AdminFormFactory $adminFormFactory, AuthorizationCheckerInterface $authorizationChecker, EngineInterface $templating)
    {
        $this->adminFormFactory = $adminFormFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function createView(array $entities, Metadata $meta): IndexView
    {
        $view = new IndexView();

        if (empty($entities)) {
            $view->setHead(new Head());
            $view->setBody(new Body());

            return $view;
        }

        $this->validateConfiguration($meta, reset($entities), 'index');

        $view->setHead($this->createHead($meta));
        $view->setBody($this->createBody($meta, $entities));

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    public function renderPropertyForm(FormInterface $form, $entity, string $entityClass, string $property): string
    {
        $view = $form->createView();

        $value = null;

        if (!$view->children[$property]->vars['compound']) {
            $value = $this->propertyAccessor->getValue($entity, $property);

            if (is_array($value)) {
                $parts = [];

                /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
                foreach ($view->children[$property]->vars['choices'] as $choice) {
                    if (in_array($choice->value, $value)) {
                        $parts[] = $choice->value;
                    }
                }

                $value = json_encode($parts);
            }
        }

        return $this->templating->render('@DarvinAdmin/widget/index/property_form/form.html.twig', [
            'entity'         => $entity,
            'entity_class'   => $entityClass,
            'form'           => $view,
            'original_value' => $value,
            'property'       => $property,
        ]);
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Darvin\AdminBundle\View\Factory\Index\Head\Head
     */
    private function createHead(Metadata $meta): Head
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

                $headItem->setSortable(true);
                $headItem->setSortablePropertyPath($sortablePropertyPath);
            }

            $head->addItem($field, $headItem);
        }

        return $head;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param array                                 $entities Entities
     *
     * @return \Darvin\AdminBundle\View\Factory\Index\Body\Body
     */
    private function createBody(Metadata $meta, array $entities): Body
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
            foreach ($configuration['view']['index']['fields'] as $field => $params) {
                if ($this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[view][index]')) {
                    continue;
                }

                $content = null;

                if (!$this->isFieldContentHidden($params, $entity)) {
                    if (!$this->isPropertyViewField($meta, 'index', $field)
                        || !array_key_exists($field, $configuration['form']['index']['fields'])
                        || $this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[view][index]')
                        || $this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[form][index]')
                    ) {
                        $content = $this->getFieldContent($entity, $field, $params, $mappings);
                    } else {
                        $form = $this->adminFormFactory->createPropertyForm($meta, $field, $entity);

                        $content = $this->renderPropertyForm($form, $entity, $meta->getEntityClass(), $field);
                    }
                }

                $bodyRow->addItem($field, new BodyRowItem($content, $this->buildBodyRowItemAttr($field, $params['attr'], $meta)));
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
    private function buildBodyRowAttr($entity, Metadata $meta): array
    {
        $attr = [];

        foreach ($meta->getConfiguration()['index_view_row_attr'] as $property) {
            $attr['data-'.$property] = $this->propertyAccessor->getValue($entity, $property);
        }

        return $attr;
    }

    /**
     * @param string                                $field Field name
     * @param array                                 $attr  Base HTML attributes
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta  Metadata
     *
     * @return array
     */
    private function buildBodyRowItemAttr(string $field, array $attr, Metadata $meta): array
    {
        $class = 'name_'.$field;

        $mappings = $meta->getMappings();

        if (isset($mappings[$field]) && !isset($mappings[$field]['targetEntity'])) {
            $class .= ' type_'.$mappings[$field]['type'];
        }

        $attr['class'] = trim(isset($attr['class']) ? $attr['class'].' '.$class : $class);

        return $attr;
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\Body $body Body to normalize
     */
    private function normalizeBody(Body $body): void
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
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\BodyRow[] $rows         Body rows
     * @param array                                                 $rowLengths   Body row lengths
     * @param int                                                   $maxRowLength Max body row length
     */
    private function normalizeBodyRows(array $rows, array $rowLengths, int $maxRowLength): void
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
