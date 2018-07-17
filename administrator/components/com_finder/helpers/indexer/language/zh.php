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
 * Chinese (simplified) language support class for the Finder indexer package.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderIndexerLanguagezh extends FinderIndexerLanguage
{
	/**
	 * Language locale of the class
	 * 
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $language = 'zh';

	/**
	 * Spacer between terms
	 * 
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $spacer = '';

	/**
	 * Method to tokenise a text string.
	 *
	 * @param   string  $input  The input to tokenise.
	 *
	 * @return  array  An array of term strings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function tokenise($input)
	{
		$terms = parent::tokenise($input);

		// Iterate through the terms and test if they contain Chinese.
		for ($i = 0, $n = count($terms); $i < $n; $i++)
		{
			$charMatches = array();
			$charCount = preg_match_all('#[\p{Han}]#mui', $terms[$i], $charMatches);

			// Split apart any groups of Chinese characters.
			for ($j = 0; $j < $charCount; $j++)
			{
				$tSplit = StringHelper::str_ireplace($charMatches[0][$j], '', $terms[$i], false);
				if (!empty($tSplit))
				{
					$terms[$i] = $tSplit;
				}
				else
				{
					unset($terms[$i]);
				}
				$terms[] = $charMatches[0][$j];
			}
		}

		return $terms;
	}
}
