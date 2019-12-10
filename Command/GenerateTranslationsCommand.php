<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Generate translations command
 */
class GenerateTranslationsCommand extends Command
{
    private const CASE_API_TIMEOUT = 3;
    private const CASE_API_URL     = 'https://ws3.morpher.ru/russian/declension?s=';

    private const DEFAULT_GENDER      = 'Male';
    private const DEFAULT_YAML_INDENT = 4;
    private const DEFAULT_YAML_INLINE = 4;

    private const GENDER_FEMALE = 'f';
    private const GENDER_MALE   = 'm';
    private const GENDER_NEUTER = 'n';

    private const GENDERS = [
        self::GENDER_FEMALE => 'Female',
        self::GENDER_MALE   => self::DEFAULT_GENDER,
        self::GENDER_NEUTER => 'Neuter',
    ];

    private const IGNORE_DOC_COMMENT_LOCALES = [
        'en',
    ];

    private const RANGE_DATA_TYPES = [
        Types::DATE_MUTABLE,
        Types::DATETIME_MUTABLE,
        Types::INTEGER,
        Types::SMALLINT,
    ];

    private const RANGE_SUFFIXES = [
        '_from' => 'range.from',
        '_to'   => 'range.to',
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
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

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
     * @param string                                                          $name                Command name
     * @param \Doctrine\ORM\EntityManager                                     $em                  Entity manager
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface            $entityNamer         Entity namer
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     * @param \Symfony\Contracts\Translation\TranslatorInterface              $translator          Translator
     * @param string                                                          $defaultLocale       Default locale
     * @param string[]                                                        $locales             Locales
     * @param string                                                          $modelDir            Translations model directory
     */
    public function __construct(
        string $name,
        EntityManager $em,
        EntityNamerInterface $entityNamer,
        TranslatableManagerInterface $translatableManager,
        TranslatorInterface $translator,
        string $defaultLocale,
        array $locales,
        string $modelDir
    ) {
        parent::__construct($name);

        $this->em = $em;
        $this->entityNamer = $entityNamer;
        $this->translatableManager = $translatableManager;
        $this->translator = $translator;
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->modelDir = $modelDir;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
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
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $entity = $input->getArgument('entity');
        $yamlIndent = (int)$input->getArgument('yaml_indent');
        $yamlInline = (int)$input->getArgument('yaml_inline');
        $gender = $io->choice('Choose gender', self::GENDERS, self::DEFAULT_GENDER);
        $locale = count($this->locales) > 1
            ? $io->choice('Choose locale', $this->locales, $this->defaultLocale)
            : $this->defaultLocale;

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
    private function buildTranslations(ClassMetadataInfo $meta, string $gender, string $locale): array
    {
        $entityName = $this->entityNamer->name($meta->getName());

        $entityTranslatable = $this->translatableManager->isTranslatable($meta->getName());

        $parseDocComments = !in_array($locale, self::IGNORE_DOC_COMMENT_LOCALES);

        $translations = [
            $entityName => $this->getModel($locale),
        ];
        $translations[$entityName]['entity'] = $this->getPropertyTranslations(
            $meta,
            $parseDocComments,
            $locale,
            $entityTranslatable ? ['translations'] : []
        );

        if ($entityTranslatable) {
            $translations[$entityName]['entity'] = array_merge($translations[$entityName]['entity'], $this->getPropertyTranslations(
                $this->em->getClassMetadata($this->translatableManager->getTranslationClass($meta->getName())),
                $parseDocComments,
                $locale,
                [
                    'locale',
                    'translatable',
                ]
            ));
        }

        $translations[$entityName]['entity'] = $this->normalizePropertyTranslations($translations[$entityName]['entity']);

        $entityTranslation = $this->getClassTranslation($meta->getReflectionClass(), $parseDocComments);

        $cases             = $this->getCases($entityTranslation);
        $startsWithAcronym = $this->startsWithAcronym($entityTranslation);

        return $this->replacePlaceholders($translations, [
            '@trans@'                  => $entityTranslation,
            '@trans_accusative@'       => $cases['accusative'],
            '@trans_genitive@'         => $cases['genitive'],
            '@trans_multiple@'         => $cases['multiple'],
            '@trans_lower@'            => $startsWithAcronym ? $entityTranslation : StringsUtil::lowercaseFirst($entityTranslation),
            '@trans_lower_accusative@' => $startsWithAcronym ? $cases['accusative'] : StringsUtil::lowercaseFirst($cases['accusative']),
            '@trans_lower_genitive@'   => $startsWithAcronym ? $cases['genitive'] : StringsUtil::lowercaseFirst($cases['genitive']),
            '@trans_lower_multiple@'   => $startsWithAcronym ? $cases['multiple'] : StringsUtil::lowercaseFirst($cases['multiple']),
            '@trans_title@'            => $parseDocComments || $startsWithAcronym ? $entityTranslation : mb_convert_case($entityTranslation, MB_CASE_TITLE),
            '@trans_title_accusative@' => $parseDocComments || $startsWithAcronym ? $cases['accusative'] : mb_convert_case($cases['accusative'], MB_CASE_TITLE),
            '@trans_title_genitive@'   => $parseDocComments || $startsWithAcronym ? $cases['genitive'] : mb_convert_case($cases['genitive'], MB_CASE_TITLE),
            '@trans_title_multiple@'   => $parseDocComments || $startsWithAcronym ? $cases['multiple'] : mb_convert_case($cases['multiple'], MB_CASE_TITLE),
        ], array_keys(self::GENDERS), $gender);
    }

    /**
     * @param string $locale Locale
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getModel(string $locale): array
    {
        $pathname = sprintf('%s/../%s/model.%s.yaml', __DIR__, $this->modelDir, $locale);

        $content = @file_get_contents($pathname);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Unable to get content of translations model file "%s".', $pathname));
        }

        return (array) Yaml::parse($content);
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $meta             Doctrine meta
     * @param bool                                    $parseDocComments Whether to parse translations from doc comments
     * @param string                                  $locale           Locale
     * @param string[]                                $propertiesToSkip Properties to skip
     *
     * @return array
     */
    private function getPropertyTranslations(ClassMetadataInfo $meta, bool $parseDocComments, string $locale, array $propertiesToSkip = []): array
    {
        $translations = [];

        foreach (array_merge($meta->getAssociationNames(), $meta->getFieldNames()) as $property) {
            if (in_array($property, $propertiesToSkip)) {
                continue;
            }

            $translation = '';

            if ($parseDocComments) {
                $translation = $this->parseTranslationFromDocComment($meta->getReflectionProperty($property)->getDocComment());
            }
            if ('' === $translation) {
                $translation = strlen($property) > 2 ? StringsUtil::humanize($property) : strtoupper($property);
            }

            $propertyUnderscore = StringsUtil::toUnderscore($property);

            $translations[$propertyUnderscore] = $translation;

            $mappings = $meta->getAssociationMappings();
            $mapping = isset($mappings[$property]) ? $mappings[$property] : $meta->getFieldMapping($property);

            if (in_array($mapping['type'], self::RANGE_DATA_TYPES) && !in_array($property, $meta->getIdentifier())) {
                foreach (self::RANGE_SUFFIXES as $suffix => $suffixTranslation) {
                    $translations[$propertyUnderscore.$suffix] = sprintf(
                        '%s (%s)',
                        $translation,
                        $this->translator->trans($suffixTranslation, [], 'admin', $locale)
                    );
                }
            }
        }

        return $translations;
    }

    /**
     * @param array $translations Property translations
     *
     * @return array
     */
    private function normalizePropertyTranslations(array $translations): array
    {
        $maxLength = 0;

        foreach ($translations as $property => $translation) {
            $length = strlen($property);

            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        foreach ($translations as $property => $translation) {
            $translations[$property] = str_repeat(' ', $maxLength - strlen($property)).$translation;
        }

        ksort($translations);

        return $translations;
    }

    /**
     * @param \ReflectionClass $classReflection  Class reflection
     * @param bool             $parseDocComments Whether to parse translations from doc comments
     *
     * @return string
     */
    private function getClassTranslation(\ReflectionClass $classReflection, bool $parseDocComments): string
    {
        if ($parseDocComments) {
            $translation = $this->parseTranslationFromDocComment($classReflection->getDocComment());

            if ('' !== $translation) {
                return $translation;
            }
        }

        return StringsUtil::humanize($this->entityNamer->name($classReflection->getName()));
    }

    /**
     * @param string $word Word
     *
     * @return array
     */
    private function getCases(string $word): array
    {
        $cases = array_fill_keys([
            'accusative',
            'genitive',
            'multiple',
        ], $word);

        if (!preg_match('/[а-яА-Я]+/', $word) || StringsUtil::isUppercase($word)) {
            return $cases;
        }

        $xml = @file_get_contents(self::CASE_API_URL.urlencode($word), false, stream_context_create([
            'http' => [
                'timeout' => self::CASE_API_TIMEOUT,
            ],
        ]));

        if (false === $xml) {
            return $cases;
        }

        $doc = simplexml_load_string($xml);

        if (false === $doc) {
            return $cases;
        }

        $doc = (array)$doc;

        if (isset($doc['code'])) {
            return $cases;
        }
        if (isset($doc['множественное'])) {
            $doc['множественное'] = (array)$doc['множественное'];
            $cases['multiple'] = $doc['множественное']['И'];
        }

        $cases['accusative'] = $doc['В'];
        $cases['genitive'] = $doc['Р'];

        return $cases;
    }

    /**
     * @param array  $translations Translations
     * @param array  $replacements Placeholder replacements
     * @param array  $genders      Genders
     * @param string $gender       Gender
     *
     * @return array
     */
    private function replacePlaceholders(array $translations, array $replacements, array $genders, string $gender): array
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
     * @param string $docComment Doc comment
     *
     * @return string
     */
    private function parseTranslationFromDocComment(string $docComment): string
    {
        if ('' === $docComment) {
            return '';
        }

        $parts = explode("\n", $docComment);

        if (!isset($parts[1])) {
            return '';
        }

        $translation = preg_replace('/\*\s*/', '', trim($parts[1]));

        if (0 === strpos($translation, '@')) {
            return '';
        }

        return $translation;
    }

    /**
     * @param string $text Text
     *
     * @return bool
     */
    private function startsWithAcronym(string $text): bool
    {
        return StringsUtil::isUppercase(preg_split('/\s+/', $text)[0]);
    }
}
