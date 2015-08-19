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
use Symfony\Component\Templating\EngineInterface;

/**
 * View widget generator abstract implementation
 */
abstract class AbstractWidgetGenerator implements WidgetGeneratorInterface
{
    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager  $metadataManager Metadata manager
     * @param \Symfony\Component\Templating\EngineInterface $templating      Templating
     */
    public function __construct(MetadataManager $metadataManager, EngineInterface $templating)
    {
        $this->metadataManager = $metadataManager;
        $this->templating = $templating;
    }

    /**
     * @return string
     */
    abstract protected function getDefaultTemplate();

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
     * @param array $options Options
     *
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    protected function validateOptions(array $options)
    {
        foreach ($this->getRequiredOptions() as $requiredOption) {
            if (!isset($options[$requiredOption])) {
                throw new WidgetGeneratorException(
                    sprintf('View widget generator "%s" requires option "%s".', $this->getAlias(), $requiredOption)
                );
            }
        }
    }

    /**
     * @return array
     */
    protected function getRequiredOptions()
    {
        return array();
    }
}
