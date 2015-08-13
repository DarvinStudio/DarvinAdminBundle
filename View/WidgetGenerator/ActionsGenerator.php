<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 12.08.15
 * Time: 17:20
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

/**
 * Actions view widget generator
 */
class ActionsGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    private $widgetGeneratorPool;

    /**
     * @param \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool $widgetGeneratorPool View widget generator pool
     */
    public function setWidgetGeneratorPool(WidgetGeneratorPool $widgetGeneratorPool)
    {
        $this->widgetGeneratorPool = $widgetGeneratorPool;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        $this->validateOptions($options);

        $actions = array();

        $configuration = $this->metadataManager->getConfigurationByEntity($entity);

        foreach ($configuration['view'][$options['view_type']]['action_widgets'] as $widgetGeneratorAlias) {
            $actions[] = $this->widgetGeneratorPool->get($widgetGeneratorAlias)->generate($entity);
        }

        return $this->render($options, array(
            'actions' => $actions,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'actions';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return 'DarvinAdminBundle:widget:actions.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredOptions()
    {
        return array(
            'view_type',
        );
    }
}
