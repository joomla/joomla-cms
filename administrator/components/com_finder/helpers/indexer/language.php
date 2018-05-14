<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Language support class for the Finder indexer package.
 * Language teams should extend this class to support language
 * specific stemming and tokenisation
 *
 * @since  4.0
 */
class FinderIndexerLanguage
{
	/**
	 * Language support instances container.
	 *
	 * @var    FinderIndexerLanguage[]
	 * @since  4.0
	 */
	protected $instances = array();

	/**
	 * Method to get a language support object.
	 *
	 * @param   string  $language  The language of the support object.
	 *
	 * @return  FinderIndexerLanguage  A FinderIndexerLanguage instance.
	 *
	 * @since   4.0
	 */
	public static function getInstance($language)
	{
		if (isset($instances[$language]))
		{
			return $instances[$language];
		}

		if ($language == '*')
		{
			$instances[$language] = new FinderIndexerLanguage;

			return $instances[$language];
		}

		$class = 'FinderIndexerLanguage' . str_replace('-', '_', $language);
		$path = JPATH_ROOT . '/language/' . $language . '/' . $language . '.finder.php';

		if (is_file($path))
		{
			JLoader::register($class, $path);
		}

		if (class_exists($class))
		{
			$instances[$language] = new $class;
		}
		else
		{
			$instances[$language] = new FinderIndexerLanguage;
		}

		return $instances[$language];
	}

	/**
	 * Method to tokenise a text string.
	 *
	 * @param   string  $input  The input to tokenise.
	 *
	 * @return  array  An array of term strings.
	 *
	 * @since   4.0
	 */
	public function tokenise($input)
	{
		$quotes = html_entity_decode('&#8216;&#8217;&#39;', ENT_QUOTES, 'UTF-8');

		/*
		 * Parsing the string input into terms is a multi-step process.
		 *
		 * Regexes:
		 *  1. Remove everything except letters, numbers, quotes, apostrophe, plus, dash, period, and comma.
		 *  2. Remove plus, dash, period, and comma characters located before letter characters.
		 *  3. Remove plus, dash, period, and comma characters located after other characters.
		 *  4. Remove plus, period, and comma characters enclosed in alphabetical characters. Ungreedy.
		 *  5. Remove orphaned apostrophe, plus, dash, period, and comma characters.
		 *  6. Remove orphaned quote characters.
		 *  7. Replace the assorted single quotation marks with the ASCII standard single quotation.
		 *  8. Remove multiple space characters and replaces with a single space.
		 */
		$input = StringHelper::strtolower($input);
		$input = preg_replace('#[^\pL\pM\pN\p{Pi}\p{Pf}\'+-.,]+#mui', ' ', $input);
		$input = preg_replace('#(^|\s)[+-.,]+([\pL\pM]+)#mui', ' $1', $input);
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
	 * @since   4.0
	 */
	public function stem($token)
	{
		return $token;
	}
}
