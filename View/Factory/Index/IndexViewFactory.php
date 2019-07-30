<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index;

use Darvin\AdminBundle\Form\AdminFormFactoryInterface;
use Darvin\AdminBundle\Form\Renderer\PropertyFormRendererInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\Factory\AbstractViewFactory;
use Darvin\AdminBundle\View\Factory\Index\Body\Body;
use Darvin\AdminBundle\View\Factory\Index\Body\BodyRow;
use Darvin\AdminBundle\View\Factory\Index\Body\BodyRowItem;
use Darvin\AdminBundle\View\Factory\Index\Head\Head;
use Darvin\AdminBundle\View\Factory\Index\Head\HeadItem;
use Darvin\AdminBundle\View\Widget\WidgetInterface;
use Darvin\Utils\Strings\StringsUtil;

/**
 * Index view factory
 */
class IndexViewFactory extends AbstractViewFactory implements IndexViewFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetInterface
     */
    private $actionsWidget;

    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactoryInterface
     */
    private $adminFormFactory;

    /**
     * @var \Darvin\AdminBundle\Form\Renderer\PropertyFormRendererInterface
     */
    private $propertyFormRenderer;

    /**
     * @param \Darvin\AdminBundle\View\Widget\WidgetInterface                 $actionsWidget        Actions view widget
     * @param \Darvin\AdminBundle\Form\AdminFormFactoryInterface              $adminFormFactory     Admin form factory
     * @param \Darvin\AdminBundle\Form\Renderer\PropertyFormRendererInterface $propertyFormRenderer Property form renderer
     */
    public function __construct(
        WidgetInterface $actionsWidget,
        AdminFormFactoryInterface $adminFormFactory,
        PropertyFormRendererInterface $propertyFormRenderer
    ) {
        $this->actionsWidget = $actionsWidget;
        $this->adminFormFactory = $adminFormFactory;
        $this->propertyFormRenderer = $propertyFormRenderer;
    }

    /**
     * {@inheritDoc}
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

        $this->normalizeView($view);

        return $view;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta Metadata
     *
     * @return \Darvin\AdminBundle\View\Factory\Index\Head\Head
     */
    private function createHead(Metadata $meta): Head
    {
        $head        = new Head();
        $config      = $meta->getConfiguration();
        $transPrefix = $meta->getEntityTranslationPrefix();

        $head->addItem('action_widgets', new HeadItem('common.actions', [
            'data-type' => 'actions',
        ]));

        foreach ($config['view']['index']['fields'] as $field => $params) {
            $item = new HeadItem($transPrefix.StringsUtil::toUnderscore($field), $params['attr']);

            if (array_key_exists($field, $config['sortable_fields'])) {
                $sortablePropertyPath = !empty($config['sortable_fields'][$field]) ? $config['sortable_fields'][$field] : $field;

                $item->setSortable(true);
                $item->setSortablePropertyPath($sortablePropertyPath);
            }

            $head->addItem($field, $item);
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
        $body     = new Body();
        $config   = $meta->getConfiguration();
        $mappings = $meta->getMappings();

        foreach ($entities as $entity) {
            $row     = new BodyRow($this->buildBodyRowAttr($entity, $meta));
            $actions = $this->actionsWidget->getContent($entity, [
                'view_type' => 'index',
            ]);

            $row->addItem('action_widgets', new BodyRowItem($actions, [
                'data-type' => 'actions',
            ]));

            foreach ($config['view']['index']['fields'] as $field => $params) {
                $content = null;

                if (!$this->isFieldContentHidden($params, $entity)) {
                    if (!array_key_exists($field, $config['form']['index']['fields'])) {
                        $content = $this->getFieldContent($entity, $field, $params, $mappings);
                    } else {
                        $form = $this->adminFormFactory->createPropertyForm($meta, $field, $entity);

                        $content = $this->propertyFormRenderer->renderPropertyForm($form, $entity, $meta->getEntityClass(), $field);
                    }
                }

                $row->addItem($field, new BodyRowItem($content, array_merge($this->buildBodyRowItemAttr($field, $params['attr'], $meta), $params['attr'])));
            }

            $body->addRow($row);
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
            $attr[sprintf('data-%s', $property)] = $this->propertyAccessor->getValue($entity, $property);
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
        $parts    = [sprintf('name-%s', $field)];
        $mappings = $meta->getMappings();

        if (isset($mappings[$field]) && !isset($mappings[$field]['targetEntity'])) {
            $parts[] = sprintf('type-%s', $mappings[$field]['type']);
        }
        if (isset($attr['class'])) {
            $parts[] = $attr['class'];
        }

        $attr['class'] = implode(' ', $parts);

        return $attr;
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\IndexView $view View
     */
    private function normalizeView(IndexView $view): void
    {
        $nonEmptyFields = [];

        foreach ($view->getBody()->getRows() as $row) {
            foreach ($row->getItems() as $field => $item) {
                if (!isset($nonEmptyFields[$field]) && '' !== $item->getContent()) {
                    $nonEmptyFields[$field] = $field;
                }
            }
        }
        foreach ($view->getHead()->getItems() as $field => $item) {
            if (!isset($nonEmptyFields[$field])) {
                $view->getHead()->removeItem($field);
            }
        }
        foreach ($view->getBody()->getRows() as $row) {
            foreach ($row->getItems() as $field => $item) {
                if (!isset($nonEmptyFields[$field])) {
                    $row->removeItem($field);
                }
            }
        }
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\Body $body Body to normalize
     */
    private function normalizeBody(Body $body): void
    {
        if (1 === $body->getLength()) {
            return;
        }

        $rows = $body->getRows();

        $firstRow = $rows[0];

        $lengths             = [];
        $maxLength           = $firstRow->getLength();
        $normalizationNeeded = false;

        foreach ($rows as $key => $row) {
            $length = $row->getLength();

            $lengths[$key] = $length;

            if ($length !== $maxLength) {
                $normalizationNeeded = true;
            }
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        if ($normalizationNeeded) {
            $this->normalizeBodyRows($rows, $lengths, $maxLength);
        }
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\BodyRow[] $rows      Body rows
     * @param array                                                 $lengths   Body row lengths
     * @param int                                                   $maxLength Max body row length
     */
    private function normalizeBodyRows(array $rows, array $lengths, int $maxLength): void
    {
        foreach ($lengths as $key => $length) {
            if ($length === $maxLength) {
                continue;
            }
            for ($i = 0; $i < $maxLength - $length; $i++) {
                $rows[$key]->addItem(null, new BodyRowItem());
            }
        }
    }
}
