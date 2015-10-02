<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Form type abstract implementation
 */
abstract class AbstractFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->setLabels($view->children);
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    abstract protected function getMetadata();

    /**
     * @param array $options Field options
     *
     * @return array
     */
    protected function resolveFieldOptionValues(array $options)
    {
        foreach ($options as &$value) {
            if (!is_array($value)) {
                continue;
            }
            if (is_callable($value)) {
                $value = $value();

                continue;
            }

            $value = $this->resolveFieldOptionValues($value);
        }

        unset($value);

        return $options;
    }

    /**
     * @param \Symfony\Component\Form\FormView[] $fields Form view fields
     */
    private function setLabels(array $fields)
    {
        $translationPrefix = $this->getMetadata()->getEntityTranslationPrefix();

        foreach ($fields as $name => $field) {
            if (null === $field->vars['label']) {
                $field->vars['label'] = $translationPrefix.StringsUtil::toUnderscore($name);
            }
            if (!empty($field->children)) {
                $this->setLabels($field->children);
            }
        }
    }
}
