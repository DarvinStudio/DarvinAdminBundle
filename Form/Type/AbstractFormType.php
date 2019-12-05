<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->setLabels($view->children, $this->getEntityTranslationPrefix($options));
    }

    /**
     * @param array $options Form options
     *
     * @return string
     */
    abstract protected function getEntityTranslationPrefix(array $options): string;

    /**
     * @param array $options Field options
     *
     * @return array
     */
    protected function resolveFieldOptionValues(array $options): array
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
     * @param \Symfony\Component\Form\FormView[] $fields            Form view fields
     * @param string                             $translationPrefix Translation prefix
     */
    private function setLabels(array $fields, string $translationPrefix): void
    {
        foreach ($fields as $name => $field) {
            if (!(null !== $field->vars['label']
                || (null !== $field->vars['label_format'] && null !== $field->parent->vars['label_format']))
            ) {
                $field->vars['label'] = $translationPrefix.StringsUtil::toUnderscore($name);
            }
            if (!empty($field->children)) {
                $this->setLabels($field->children, $translationPrefix);
            }
        }
    }
}
