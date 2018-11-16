<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\CKEditor;

use Darvin\ContentBundle\Widget\AbstractWidget;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CKEditor widget abstract implementation
 */
abstract class AbstractCKEditorWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'icon'  => __DIR__.'/../Resources/images/ckeditor_stub.png',
            'title' => sprintf('ckeditor_widget.%s', $this->getName()),
        ]);
    }
}
