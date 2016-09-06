<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Generate translations command
 */
class GenerateTranslationsCommand extends Command
{
    const DEFAULT_GENDER      = 'Male';
    const DEFAULT_YAML_INDENT = 4;
    const DEFAULT_YAML_INLINE = 4;

    const GENDER_FEMALE = 'f';
    const GENDER_MALE   = 'm';
    const GENDER_NEUTER = 'n';

    /**
     * @var array
     */
    private static $genders = [
        self::GENDER_FEMALE => 'Female',
        self::GENDER_MALE   => self::DEFAULT_GENDER,
        self::GENDER_NEUTER => 'Neuter',
    ];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var string
     */
    private $modelDir;

    /**
     * @param string                                               $name          Command name
     * @param \Doctrine\ORM\EntityManager                          $em            Entity manager
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface $entityNamer   Entity namer
     * @param string                                               $defaultLocale Default locale
     * @param string[]                                             $locales       Locales
     * @param string                                               $modelDir      Translations model directory
     */
    public function __construct($name, EntityManager $em, EntityNamerInterface $entityNamer, $defaultLocale, array $locales, $modelDir)
    {
        parent::__construct($name);

        $this->em = $em;
        $this->entityNamer = $entityNamer;
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->modelDir = $modelDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Generates translations using entity class doc comments.')
            ->setDefinition([
                new InputArgument('entity', InputArgument::REQUIRED),
                new InputArgument('yaml_indent', InputArgument::OPTIONAL, '', self::DEFAULT_YAML_INDENT),
                new InputArgument('yaml_inline', InputArgument::OPTIONAL, '', self::DEFAULT_YAML_INLINE),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $entity = $input->getArgument('entity');
        $yamlIndent = $input->getArgument('yaml_indent');
        $yamlInline = $input->getArgument('yaml_inline');
        $gender = $io->choice('Choose gender', self::$genders, self::DEFAULT_GENDER);
        $locale = $io->choice('Choose locale', $this->locales, $this->defaultLocale);

        $translations = $this->buildTranslations($this->em->getClassMetadata($entity), $gender, $locale);

        $output->writeln(str_replace('\'', '', Yaml::dump($translations, $yamlInline, $yamlIndent)));
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $meta   Doctrine meta
     * @param string                                  $gender Gender
     * @param string                                  $locale Locale
     *
     * @return array
     */
    private function buildTranslations(ClassMetadataInfo $meta, $gender, $locale)
    {
        $entityName = $this->entityNamer->name($meta->getName());

        $translations = $this->getModel($locale);
        $translations[$entityName] = $translations['@entity_name@'];
        unset($translations['@entity_name@']);
        $translations['entity'] = $this->getPropertyTranslations(
            array_merge($meta->getAssociationNames(), $meta->getFieldNames()),
            $meta->getReflectionClass()
        );

        $entityTranslation = $this->getClassTranslation($meta->getReflectionClass());

        return $this->replacePlaceholders($translations, [
            '@entity_name@'        => $entityName,
            '@entity_trans@'       => $entityTranslation,
            '@entity_trans_lower@' => mb_strtolower($entityTranslation),
        ], array_keys(self::$genders), $gender);
    }

    /**
     * @param array  $translations Translations
     * @param array  $replacements Placeholder replacements
     * @param array  $genders      Genders
     * @param string $gender       Gender
     *
     * @return array
     */
    private function replacePlaceholders(array $translations, array $replacements, array $genders, $gender)
    {
        foreach ($translations as $key => $value) {
            if (!is_array($value)) {
                $translations[$key] = strtr($value, $replacements);

                continue;
            }
            if (array_keys($value) === $genders) {
                $translations[$key] = strtr($value[$gender], $replacements);

                continue;
            }

            $translations[$key] = $this->replacePlaceholders($value, $replacements, $genders, $gender);
        }

        return $translations;
    }

    /**
     * @param \ReflectionClass $classReflection Class reflection
     *
     * @return string
     */
    private function getClassTranslation(\ReflectionClass $classReflection)
    {
        $translation = $this->parseTranslationFromDocComment($classReflection->getDocComment());

        if (!empty($translation)) {
            return $translation;
        }

        $parts = explode('\\', $classReflection->getName());

        return $this->humanize(!empty($parts) ? array_pop($parts) : $classReflection->getName());
    }

    /**
     * @param string[]         $properties      Properties
     * @param \ReflectionClass $classReflection Class reflection
     *
     * @return array
     */
    private function getPropertyTranslations(array $properties, \ReflectionClass $classReflection)
    {
        $translations = [];

        foreach ($properties as $property) {
            $translation = $this->parseTranslationFromDocComment($classReflection->getProperty($property)->getDocComment());

            if (empty($translation)) {
                $translation = $this->humanize($property);
            }

            $translations[StringsUtil::toUnderscore($property)] = $translation;
        }

        return $translations;
    }

    /**
     * @param string $docComment Doc comment
     *
     * @return string
     */
    private function parseTranslationFromDocComment($docComment)
    {
        if (empty($docComment)) {
            return null;
        }

        $parts = explode("\n", $docComment);

        if (!isset($parts[1])) {
            return null;
        }

        $translation = preg_replace('/\*\s*/', '', trim($parts[1]));

        if (0 === strpos($translation, '@')) {
            return null;
        }

        return $translation;
    }

    /**
     * @param string $text Text
     *
     * @return string
     */
    private function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }

    /**
     * @param string $locale Locale
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getModel($locale)
    {
        $pathname = sprintf('%s/../%s/model.%s.yml', __DIR__, $this->modelDir, $locale);

        $content = @file_get_contents($pathname);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Unable to get content of translations model file "%s".', $pathname));
        }

        return (array) Yaml::parse($content);
    }
}
