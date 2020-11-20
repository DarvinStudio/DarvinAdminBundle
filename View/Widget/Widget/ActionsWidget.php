<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Actions view widget
 */
class ActionsWidget extends AbstractWidget
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    private $widgetPool;

    /**
     * @param \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface $widgetPool View widget pool
     */
    public function __construct(ViewWidgetPoolInterface $widgetPool)
    {
        $this->widgetPool = $widgetPool;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $actions = [];
        $config  = $this->metadataManager->getConfiguration($entity);

        $widgets = array_merge(
            $config['view'][$options['view_type']]['action_widgets'],
            $config['view'][$options['view_type']]['extra_action_widgets']
        );

        foreach ($widgets as $widgetAlias => $widgetOptions) {
            if (!isset($widgetOptions['style'])) {
                $widgetOptions['style'] = $options['style'];
            }

            $action = (string)$this->widgetPool->getWidget($widgetAlias)->getContent($entity, $widgetOptions);

            if ('' !== $action) {
                $actions[$widgetAlias] = $action;
            }
        }
        if (empty($actions)) {
            return null;
        }

        return $this->render([
            'actions' => $actions,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('view_type')
            ->setAllowedTypes('view_type', 'string');
    }
}
