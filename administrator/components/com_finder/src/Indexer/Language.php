<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

use Joomla\String\StringHelper;
use Wamania\Snowball\NotFoundException;
use Wamania\Snowball\Stemmer\Stemmer;
use Wamania\Snowball\StemmerFactory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Language support class for the Finder indexer package.
 *
 * @since  4.0.0
 */
class Language
{
    /**
     * Language support instances container.
     *
     * @var    Language[]
     * @since  4.0.0
     */
    protected static $instances = [];

    /**
     * Language locale of the class
     *
     * @var    string
     * @since  4.0.0
     */
    public $language;

    /**
     * Spacer to use between terms
     *
     * @var    string
     * @since  4.0.0
     */
    public $spacer = ' ';

    /**
     * The stemmer object.
     *
     * @var    Stemmer
     * @since  4.0.0
     */
    protected $stemmer = null;

    /**
     * Method to construct the language object.
     *
     * @since   4.0.0
     */
    public function __construct($locale = null)
    {
        if ($locale !== null) {
            $this->language = $locale;
        }

        // Use our generic language handler if no language is set
        if ($this->language === null) {
            $this->language = '*';
        }

        try {
            foreach (StemmerFactory::LANGS as $classname => $isoCodes) {
                if (\in_array($this->language, $isoCodes)) {
                    $this->stemmer = StemmerFactory::create($this->language);
                    break;
                }
            }
        } catch (NotFoundException) {
            // We don't have a stemmer for the language
        }
    }

    /**
     * Method to get a language support object.
     *
     * @param   string  $language  The language of the support object.
     *
     * @return  Language  A Language instance.
     *
     * @since   4.0.0
     */
    public static function getInstance($language)
    {
        if (isset(self::$instances[$language])) {
            return self::$instances[$language];
        }

        $locale = '*';

        if ($language !== '*') {
            $locale = Helper::getPrimaryLanguage($language);
            $class  = '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Language\\' . ucfirst($locale);

            if (class_exists($class)) {
                self::$instances[$language] = new $class();

                return self::$instances[$language];
            }
        }

        self::$instances[$language] = new self($locale);

        return self::$instances[$language];
    }

    /**
     * Method to tokenise a text string.
     *
     * @param   string  $input  The input to tokenise.
     *
     * @return  array  An array of term strings.
     *
     * @since   4.0.0
     */
    public function tokenise($input)
    {
        $quotes = html_entity_decode('&#8216;&#8217;&#39;', ENT_QUOTES, 'UTF-8');

        /*
         * Parsing the string input into terms is a multi-step process.
         *
         * Regexes:
         *  1. Remove everything except letters, numbers, quotes, apostrophe, plus, dash, period, and comma.
         *  2. Remove plus, dash, and comma characters located before letter characters.
         *  3. Remove plus, dash, period, and comma characters located after other characters.
         *  4. Remove plus, period, and comma characters enclosed in alphabetical characters. Ungreedy.
         *  5. Remove orphaned apostrophe, plus, dash, period, and comma characters.
         *  6. Remove orphaned quote characters.
         *  7. Replace the assorted single quotation marks with the ASCII standard single quotation.
         *  8. Remove multiple space characters and replaces with a single space.
         */
        $input = StringHelper::strtolower($input);
        $input = preg_replace('#[^\pL\pM\pN\p{Pi}\p{Pf}\'+-.,]+#mui', ' ', $input);
        $input = preg_replace('#(^|\s)[+-,]+([\pL\pM]+)#mui', ' $1', $input);
        $input = preg_replace('#([\pL\pM\pN]+)[+-.,]+(\s|$)#mui', '$1 ', $input);
        $input = preg_replace('#([\pL\pM]+)[+.,]+([\pL\pM]+)#muiU', '$1 $2', $input);
        $input = preg_replace('#(^|\s)[\'+-.,]+(\s|$)#mui', ' ', $input);
        $input = preg_replace('#(^|\s)[\p{Pi}\p{Pf}]+(\s|$)#mui', ' ', $input);
        $input = preg_replace('#[' . $quotes . ']+#mui', '\'', $input);
        $input = preg_replace('#\s+#mui', ' ', $input);
        $input = trim($input);

        // Explode the normalized string to get the terms.
        $terms = explode(' ', $input);

        return $terms;
    }

    /**
     * Method to stem a token.
     *
     * @param   string  $token  The token to stem.
     *
     * @return  string  The stemmed token.
     *
     * @since   4.0.0
     */
    public function stem($token)
    {
        if ($this->stemmer !== null) {
            return $this->stemmer->stem($token);
        }

        return $token;
    }
}
