<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Show;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\View\Factory\AbstractViewFactory;
use Darvin\Utils\Strings\StringsUtil;

/**
 * Show view factory
 */
class ShowViewFactory extends AbstractViewFactory implements ShowViewFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createView($entity, Metadata $meta): ShowView
    {
        $this->validateConfiguration($meta, $entity, 'show');

        $view = new ShowView();

        $configuration = $meta->getConfiguration();
        $translationPrefix = $meta->getEntityTranslationPrefix();

        foreach ($configuration['view']['show']['fields'] as $field => $attr) {
            if ($this->fieldBlacklistManager->isFieldBlacklisted($meta, $field, '[view][show]')
                || $this->isFieldContentHidden($attr, $entity)
            ) {
                continue;
            }

            $label = $translationPrefix.StringsUtil::toUnderscore($field);

            $content = $this->getFieldContent($entity, $field, $attr, $meta->getMappings());

            $view->addItem(new Item($label, $content));
        }

        return $view;
    }
}
