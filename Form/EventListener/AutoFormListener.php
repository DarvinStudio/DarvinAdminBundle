<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\EventListener;

use A2lix\AutoFormBundle\Form\EventListener\AutoFormListener as BaseAutoFormListener;
use A2lix\AutoFormBundle\Form\Manipulator\FormManipulatorInterface;
use Symfony\Component\Form\FormEvent;

/**
 * Auto form event listener
 */
class AutoFormListener extends BaseAutoFormListener
{
    /**
     * @var \A2lix\AutoFormBundle\Form\Manipulator\FormManipulatorInterface
     */
    private $formManipulator;

    /**
     * {@inheritDoc}
     */
    public function __construct(FormManipulatorInterface $formManipulator)
    {
        parent::__construct($formManipulator);

        $this->formManipulator = $formManipulator;
    }

    /**
     * {@inheritDoc}
     */
    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $fieldsOptions = $this->formManipulator->getFieldsConfig($form);

        foreach ($fieldsOptions as $fieldName => $fieldConfig) {
            if (false !== strpos($fieldName, '.')) {
                if (!isset($fieldConfig['property_path'])) {
                    $fieldConfig['property_path'] = $fieldName;
                }

                $fieldName = str_replace('.', '_', $fieldName);
            }

            $fieldType = $fieldConfig['field_type'] ?? null;

            unset($fieldConfig['field_type']);

            $form->add($fieldName, $fieldType, $fieldConfig);
        }
    }
}
