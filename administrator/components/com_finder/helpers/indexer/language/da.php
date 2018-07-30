<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Danish language support class for the Finder indexer package.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderIndexerLanguageda extends FinderIndexerLanguage
{
	/**
	 * Language locale of the class
	 * 
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $language = 'da';

	/**
	 * The danish stemmer object.
	 *
	 * @var    \Wamania\Snowball\Danish
	 * @since  __DEPLOY_VERSION__
	 */
	protected $stemmer = null;

	/**
	 * Method to construct the language object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->stemmer = new \Wamania\Snowball\Danish;
	}

	/**
	 * Method to stem a token.
	 *
	 * @param   string  $token  The token to stem.
	 *
	 * @return  string  The stemmed token.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stem($token)
	{
		return $this->stemmer->stem($token);
	}
}
