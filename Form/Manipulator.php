<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use A2lix\AutoFormBundle\Form\Manipulator\DoctrineORMManipulator;
use Symfony\Component\Form\FormInterface;

/**
 * Form manipulator
 */
class Manipulator extends DoctrineORMManipulator
{
    /**
     * {@inheritDoc}
     */
    public function getFieldsConfig(FormInterface $form): array
    {
        $config       = parent::getFieldsConfig($form);
        $sortedConfig = [];

        foreach (array_keys($form->getConfig()->getOption('fields')) as $field) {
            if (isset($config[$field])) {
                $sortedConfig[$field] = $config[$field];
            }
        }

        return array_merge($sortedConfig, $config);
    }
}
