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

use Darvin\AdminBundle\Metadata\MetadataManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * View widget generator abstract implementation
 */
abstract class AbstractWidgetGenerator implements WidgetGeneratorInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                                 $metadataManager      Metadata manager
     * @param \Symfony\Component\Templating\EngineInterface                                $templating           Templating
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        MetadataManager $metadataManager,
        EngineInterface $templating
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->metadataManager = $metadataManager;
        $this->templating = $templating;
    }

    /**
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return sprintf('DarvinAdminBundle:widget:%s.html.twig', $this->getAlias());
    }

    /**
     * @param string $permission Permission
     * @param object $entity     Entity
     *
     * @return bool
     */
    protected function isGranted($permission, $entity)
    {
        return $this->authorizationChecker->isGranted($permission, $entity);
    }

    /**
     * @param array $options        Options
     * @param array $templateParams Template parameters
     *
     * @return string
     */
    protected function render(array $options, array $templateParams = array())
    {
        $template = isset($options['template']) ? $options['template'] : $this->getDefaultTemplate();

        return $this->templating->render($template, $templateParams);
    }

    /**
     * @param object $entity  Entity
     * @param array  $options Options
     *
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    protected function validate($entity, array $options)
    {
        $requiredEntityClass = $this->getRequiredEntityClass();

        if (!empty($requiredEntityClass) && !$entity instanceof $requiredEntityClass) {
            $message = sprintf(
                'View widget generator "%s" requires entity to be instance of "%s".',
                $this->getAlias(),
                $requiredEntityClass
            );

            throw new WidgetGeneratorException($message);
        }
        foreach ($this->getRequiredOptions() as $requiredOption) {
            if (!isset($options[$requiredOption])) {
                throw new WidgetGeneratorException(
                    sprintf('View widget generator "%s" requires option "%s".', $this->getAlias(), $requiredOption)
                );
            }
        }
    }

    /**
     * @return string
     */
    protected function getRequiredEntityClass()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getRequiredOptions()
    {
        return array();
    }
}
