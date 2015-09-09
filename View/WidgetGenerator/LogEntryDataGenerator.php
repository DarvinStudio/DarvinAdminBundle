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

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\Utils\Strings\Stringifier\StringifierInterface;
use Darvin\Utils\Strings\StringsUtil;

/**
 * Log entry data view widget generator
 */
class LogEntryDataGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Darvin\Utils\Strings\Stringifier\StringifierInterface
     */
    private $stringifier;

    /**
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface $stringifier Stringifier
     */
    public function setStringifier(StringifierInterface $stringifier)
    {
        $this->stringifier = $stringifier;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($entity, array $options = array())
    {
        $this->validate($entity, $options);

        $data = $entity->getData();

        if (empty($data)) {
            return '';
        }
        if (!$this->metadataManager->hasMetadata($entity->getObjectClass())) {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);

            if (false === $json) {
                throw new WidgetGeneratorException(
                    sprintf('Unable to encode data of log entry with ID "%d" to JSON.', $entity->getId())
                );
            }

            return $json;
        }

        $meta = $this->metadataManager->getByEntityClass($entity->getObjectClass());
        $mappings = $meta->getMappings();
        $translationPrefix = $meta->getEntityTranslationPrefix();

        $viewData = array();

        foreach ($data as $property => $value) {
            if (isset($mappings[$property])) {
                $value = $this->stringifier->stringify($value, $mappings[$property]['type']);
            }

            $viewData[$translationPrefix.StringsUtil::toUnderscore($property)] = $value;
        }

        return $this->render($options, array(
            'data' => $viewData,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'log_entry_data';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredEntityClass()
    {
        return LogEntry::LOG_ENTRY_CLASS;
    }
}
