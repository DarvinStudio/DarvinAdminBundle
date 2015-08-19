<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Doctrine\ORM\EntityManager;

/**
 * Child links view widget generator
 */
class ChildLinksGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    private $identifierAccessor;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor $identifierAccessor Identifier accessor
     */
    public function setIdentifierAccessor(IdentifierAccessor $identifierAccessor)
    {
        $this->identifierAccessor = $identifierAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        $this->validate($entity, $options);

        $childClass = $options['child_entity'];

        $parentMeta = $this->metadataManager->getByEntity($entity);

        if (!$parentMeta->hasChild($childClass)) {
            throw new WidgetGeneratorException(
                sprintf('Entity "%s" is not child of entity "%s".', $childClass, $parentMeta->getEntityClass())
            );
        }

        $childMeta = $parentMeta->getChild($childClass);
        $association = $childMeta->getAssociation();

        $parentId = $this->identifierAccessor->getValue($entity);

        $childrenCount = (int) $this->em->getRepository($childClass)->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where(sprintf('o.%s = :%1$s', $association))
            ->setParameter($association, $parentId)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render($options, array(
            'child_class'        => $childClass,
            'children_count'     => $childrenCount,
            'association'        => $association,
            'parent_id'          => $parentId,
            'translation_prefix' => $childMeta->getMetadata()->getBaseTranslationPrefix(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'child_links';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return 'DarvinAdminBundle:widget:child_links.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredOptions()
    {
        return array(
            'child_entity',
        );
    }
}
