<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Renderer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;

/**
 * Property form renderer
 */
class PropertyFormRenderer implements PropertyFormRendererInterface
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Twig\Environment                                           $twig             Twig
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor, Environment $twig)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderPropertyForm(FormInterface $form, $entity, string $entityClass, string $property): ?string
    {
        $view = $form->createView();

        if (!isset($view->children[$property])) {
            return null;
        }

        $value = null;

        if (!$view->children[$property]->vars['compound'] && !is_object($view->children[$property]->vars['value'])) {
            $value = $view->children[$property]->vars['value'];

            if (is_array($value)) {
                $value = json_encode(array_values($value));
            }
        }

        return $this->twig->render('@DarvinAdmin/property_form/form.html.twig', [
            'entity'         => $entity,
            'entity_class'   => $entityClass,
            'form'           => $view,
            'original_value' => $value,
            'property'       => $property,
        ]);
    }
}
