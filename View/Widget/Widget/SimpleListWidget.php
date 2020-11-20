<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Simple list view widget
 */
class SimpleListWidget extends AbstractWidget
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator Translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $property = $options['property'];

        $items = $this->getPropertyValue($entity, $property);

        if (empty($items)) {
            return null;
        }
        if (!is_iterable($items)) {
            $message = sprintf(
                'Value of property "%s::$%s" must be iterable, "%s" provided.',
                ClassUtils::getClass($entity),
                $property,
                gettype($items)
            );

            throw new \InvalidArgumentException($message);
        }
        if (!is_array($items)) {
            $items = iterator_to_array($items);
        }
        if (empty($items)) {
            return null;
        }

        $translator = $this->translator;

        return sprintf('<ul><li>%s</li></ul>', implode('</li><li>', array_map(function ($item) use ($options, $translator): string {
            return $translator->trans($options['trans_prefix'].(string)$item, [], 'admin');
        }, $items)));
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('trans_prefix', '')
            ->setAllowedTypes('trans_prefix', 'string');
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
