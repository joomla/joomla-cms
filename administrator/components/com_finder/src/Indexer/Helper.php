<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder\PrepareContentEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for the Finder indexer package.
 *
 * @since  2.5
 */
class Helper
{
    public const CUSTOMFIELDS_DONT_INDEX      = 0;
    public const CUSTOMFIELDS_ADD_TO_INDEX    = 1;
    public const CUSTOMFIELDS_ADD_TO_TAXONOMY = 2;
    public const CUSTOMFIELDS_ADD_TO_BOTH     = 3;

    /**
     * Method to parse input into plain text.
     *
     * @param   string  $input   The raw input.
     * @param   string  $format  The format of the input. [optional]
     *
     * @return  string  The parsed input.
     *
     * @since   2.5
     * @throws  \Exception on invalid parser.
     */
    public static function parse($input, $format = 'html')
    {
        // Get a parser for the specified format and parse the input.
        return Parser::getInstance($format)->parse($input);
    }

    /**
     * Method to tokenize a text string.
     *
     * @param   string   $input   The input to tokenize.
     * @param   string   $lang    The language of the input.
     * @param   boolean  $phrase  Flag to indicate whether input could be a phrase. [optional]
     *
     * @return  Token[]  An array of Token objects.
     *
     * @since   2.5
     */
    public static function tokenize($input, $lang, $phrase = false)
    {
        static $cache = [], $tuplecount;
        static $multilingual;
        static $defaultLanguage;

        if (!$tuplecount) {
            $params     = ComponentHelper::getParams('com_finder');
            $tuplecount = $params->get('tuplecount', 1);
        }

        if (\is_null($multilingual)) {
            $multilingual = Multilanguage::isEnabled();
            $config       = ComponentHelper::getParams('com_finder');

            if ($config->get('language_default', '') == '') {
                $defaultLang = '*';
            } elseif ($config->get('language_default', '') == '-1') {
                $defaultLang = self::getDefaultLanguage();
            } else {
                $defaultLang = $config->get('language_default');
            }

            /*
             * The default language always has the language code '*'.
             * In order to not overwrite the language code of the language
             * object that we are using, we are cloning it here.
             */
            $obj                       = Language::getInstance($defaultLang);
            $defaultLanguage           = clone $obj;
            $defaultLanguage->language = '*';
        }

        if (!$multilingual || $lang == '*') {
            $language = $defaultLanguage;
        } else {
            $language = Language::getInstance($lang);
        }

        if (!isset($cache[$lang])) {
            $cache[$lang] = [];
        }

        $tokens = [];
        $terms  = $language->tokenise($input);

        // @todo: array_filter removes any number 0's from the terms. Not sure this is entirely intended
        $terms = array_filter($terms);
        $terms = array_values($terms);

        /*
         * If we have to handle the input as a phrase, that means we don't
         * tokenize the individual terms and we do not create the two and three
         * term combinations. The phrase must contain more than one word!
         */
        if ($phrase === true && \count($terms) > 1) {
            // Create tokens from the phrase.
            $tokens[] = new Token($terms, $language->language, $language->spacer);
        } else {
            // Create tokens from the terms.
            for ($i = 0, $n = \count($terms); $i < $n; $i++) {
                if (isset($cache[$lang][$terms[$i]])) {
                    $tokens[] = $cache[$lang][$terms[$i]];
                } else {
                    $token                    = new Token($terms[$i], $language->language);
                    $tokens[]                 = $token;
                    $cache[$lang][$terms[$i]] = $token;
                }
            }

            // Create multi-word phrase tokens from the individual words.
            if ($tuplecount > 1) {
                for ($i = 0, $n = \count($tokens); $i < $n; $i++) {
                    $temp = [$tokens[$i]->term];

                    // Create tokens for 2 to $tuplecount length phrases
                    for ($j = 1; $j < $tuplecount; $j++) {
                        if ($i + $j >= $n || !isset($tokens[$i + $j])) {
                            break;
                        }

                        $temp[] = $tokens[$i + $j]->term;
                        $key    = implode('::', $temp);

                        if (isset($cache[$lang][$key])) {
                            $tokens[] = $cache[$lang][$key];
                        } else {
                            $token              = new Token($temp, $language->language, $language->spacer);
                            $token->derived     = true;
                            $tokens[]           = $token;
                            $cache[$lang][$key] = $token;
                        }
                    }
                }
            }
        }

        // Prevent the cache to fill up the memory
        while (\count($cache[$lang]) > 1024) {
            /**
             * We want to cache the most common words/tokens. At the same time
             * we don't want to cache too much. The most common words will also
             * be early in the text, so we are dropping all terms/tokens which
             * have been cached later.
             */
            array_pop($cache[$lang]);
        }

        return $tokens;
    }

    /**
     * Method to get the base word of a token.
     *
     * @param   string  $token  The token to stem.
     * @param   string  $lang   The language of the token.
     *
     * @return  string  The root token.
     *
     * @since   2.5
     */
    public static function stem($token, $lang)
    {
        static $multilingual;
        static $defaultStemmer;

        if (\is_null($multilingual)) {
            $multilingual = Multilanguage::isEnabled();
            $config       = ComponentHelper::getParams('com_finder');

            if ($config->get('language_default', '') == '') {
                $defaultStemmer = Language::getInstance('*');
            } elseif ($config->get('language_default', '') == '-1') {
                $defaultStemmer = Language::getInstance(self::getDefaultLanguage());
            } else {
                $defaultStemmer = Language::getInstance($config->get('language_default'));
            }
        }

        if (!$multilingual || $lang == '*') {
            $language = $defaultStemmer;
        } else {
            $language = Language::getInstance($lang);
        }

        return $language->stem($token);
    }

    /**
     * Method to add a content type to the database.
     *
     * @param   string  $title  The type of content. For example: PDF
     * @param   string  $mime   The mime type of the content. For example: PDF [optional]
     *
     * @return  integer  The id of the content type.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public static function addContentType($title, $mime = null)
    {
        static $types;

        $db    = Factory::getDbo();
        $query = $db->createQuery();

        // Check if the types are loaded.
        if (empty($types)) {
            // Build the query to get the types.
            $query->select('*')
                ->from($db->quoteName('#__finder_types'));

            // Get the types.
            $db->setQuery($query);
            $types = $db->loadObjectList('title');
        }

        // Check if the type already exists.
        if (isset($types[$title])) {
            return (int) $types[$title]->id;
        }

        // Add the type.
        $query->clear()
            ->insert($db->quoteName('#__finder_types'))
            ->columns([$db->quoteName('title'), $db->quoteName('mime')])
            ->values($db->quote($title) . ', ' . $db->quote($mime ?? ''));
        $db->setQuery($query);
        $db->execute();

        // Cache the result
        $type        = new \stdClass();
        $type->title = $title;
        $type->mime  = $mime ?? '';
        $type->id    = (int) $db->insertid();

        $types[$title] = $type;

        // Return the new id.
        return $type->id;
    }

    /**
     * Method to check if a token is common in a language.
     *
     * @param   string  $token  The token to test.
     * @param   string  $lang   The language to reference.
     *
     * @return  boolean  True if common, false otherwise.
     *
     * @since   2.5
     */
    public static function isCommon($token, $lang)
    {
        static $data = [], $default, $multilingual;

        if (\is_null($multilingual)) {
            $multilingual = Multilanguage::isEnabled();
            $config       = ComponentHelper::getParams('com_finder');

            if ($config->get('language_default', '') == '') {
                $default = '*';
            } elseif ($config->get('language_default', '') == '-1') {
                $default = self::getPrimaryLanguage(self::getDefaultLanguage());
            } else {
                $default = self::getPrimaryLanguage($config->get('language_default'));
            }
        }

        if (!$multilingual || $lang == '*') {
            $lang = $default;
        }

        // Load the common tokens for the language if necessary.
        if (!isset($data[$lang])) {
            $data[$lang] = self::getCommonWords($lang);
        }

        // Check if the token is in the common array.
        return \in_array($token, $data[$lang], true);
    }

    /**
     * Method to get an array of common terms for a language.
     *
     * @param   string  $lang  The language to use.
     *
     * @return  array  Array of common terms.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public static function getCommonWords($lang)
    {
        $db = Factory::getDbo();

        // Create the query to load all the common terms for the language.
        $query = $db->createQuery()
            ->select($db->quoteName('term'))
            ->from($db->quoteName('#__finder_terms_common'))
            ->where($db->quoteName('language') . ' = ' . $db->quote($lang));

        // Load all of the common terms for the language.
        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Method to get the default language for the site.
     *
     * @return  string  The default language string.
     *
     * @since   2.5
     */
    public static function getDefaultLanguage()
    {
        static $lang;

        // We need to go to com_languages to get the site default language, it's the best we can guess.
        if (empty($lang)) {
            $lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
        }

        return $lang;
    }

    /**
     * Method to parse a language/locale key and return a simple language string.
     *
     * @param   string  $lang  The language/locale key. For example: en-GB
     *
     * @return  string  The simple language string. For example: en
     *
     * @since   2.5
     */
    public static function getPrimaryLanguage($lang)
    {
        static $data = [];

        // Only parse the identifier if necessary.
        if (!isset($data[$lang])) {
            if (\is_callable(['Locale', 'getPrimaryLanguage'])) {
                // Get the language key using the Locale package.
                $data[$lang] = \Locale::getPrimaryLanguage($lang);
            } else {
                // Get the language key using string position.
                $data[$lang] = StringHelper::substr($lang, 0, StringHelper::strpos($lang, '-'));
            }
        }

        return $data[$lang];
    }

    /**
     * Method to get extra data for a content before being indexed. This is how
     * we add Comments, Tags, Labels, etc. that should be available to Finder.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public static function getContentExtras(Result $item)
    {
        $dispatcher = Factory::getApplication()->getDispatcher();

        // Load the finder plugin group.
        PluginHelper::importPlugin('finder', null, true, $dispatcher);

        $dispatcher->dispatch('onPrepareFinderContent', new PrepareContentEvent('onPrepareFinderContent', [
            'subject' => $item,
        ]));

        return true;
    }

    /**
     * Add custom fields for the item to the Result object
     *
     * @param   Result  $item     Result object to add the custom fields to
     * @param   string  $context  Context of the item in the custom fields
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public static function addCustomFields(Result $item, $context)
    {
        if (!ComponentHelper::getParams(strstr($context, '.', true))->get('custom_fields_enable', 1)) {
            return;
        }

        $obj     = new \stdClass();
        $obj->id = $item->id;

        $fields = FieldsHelper::getFields($context, $obj, true);

        foreach ($fields as $field) {
            $searchindex = $field->params->get('searchindex', 0);

            // We want to add this field to the search index
            if ($searchindex == self::CUSTOMFIELDS_ADD_TO_INDEX || $searchindex == self::CUSTOMFIELDS_ADD_TO_BOTH) {
                $name        = 'jsfield_' . $field->name;
                $item->$name = $field->value;
                $item->addInstruction(Indexer::META_CONTEXT, $name);
            }

            // We want to add this field as a taxonomy
            if (
                ($searchindex == self::CUSTOMFIELDS_ADD_TO_TAXONOMY || $searchindex == self::CUSTOMFIELDS_ADD_TO_BOTH)
                && $field->value
            ) {
                $item->addTaxonomy($field->title, $field->value, $field->state, $field->access, $field->language);
            }
        }
    }

    /**
     * Method to process content text using the onContentPrepare event trigger.
     *
     * @param   string    $text    The content to process.
     * @param   Registry  $params  The parameters object. [optional]
     * @param   ?Result   $item    The item which get prepared. [optional]
     *
     * @return  string  The processed content.
     *
     * @since   2.5
     */
    public static function prepareContent($text, $params = null, ?Result $item = null)
    {
        static $loaded;

        // Load the content plugins if necessary.
        if (empty($loaded)) {
            PluginHelper::importPlugin('content');
            $loaded = true;
        }

        // Instantiate the parameter object if necessary.
        if (!($params instanceof Registry)) {
            $registry = new Registry($params);
            $params   = $registry;
        }

        // Create a mock content object.
        $content       = Table::getInstance('Content');
        $content->text = $text;

        if ($item) {
            $content->bind((array) $item);
            $content->bind($item->getElements());
        }

        if ($item && !empty($item->context)) {
            $content->context = $item->context;
        }

        // Fire the onContentPrepare event.
        Factory::getApplication()->triggerEvent('onContentPrepare', ['com_finder.indexer', &$content, &$params, 0]);

        return $content->text;
    }
}
