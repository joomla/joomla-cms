<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

\defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Token class for the Finder indexer package.
 *
 * @since  2.5
 */
class Token
{
	/**
	 * This is the term that will be referenced in the terms table and the
	 * mapping tables.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $term;

	/**
	 * The stem is used to match the root term and produce more potential
	 * matches when searching the index.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $stem;

	/**
	 * If the token is numeric, it is likely to be short and uncommon so the
	 * weight is adjusted to compensate for that situation.
	 *
	 * @var    boolean
	 * @since  2.5
	 */
	public $numeric;

	/**
	 * If the token is a common term, the weight is adjusted to compensate for
	 * the higher frequency of the term in relation to other terms.
	 *
	 * @var    boolean
	 * @since  2.5
	 */
	public $common;

	/**
	 * Flag for phrase tokens.
	 *
	 * @var    boolean
	 * @since  2.5
	 */
	public $phrase;

	/**
	 * The length is used to calculate the weight of the token.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	public $length;

	/**
	 * The weight is calculated based on token size and whether the token is
	 * considered a common term.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	public $weight;

	/**
	 * The simple language identifier for the token.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $language;

	/**
	 * The container for matches.
	 *
	 * @var    array
	 * @since  3.8.12
	 */
	public $matches = array();

	/**
	 * Is derived token (from individual words)
	 *
	 * @var    boolean
	 * @since  3.8.12
	 */
	public $derived;

	/**
	 * The suggested term
	 *
	 * @var    string
	 * @since  3.8.12
	 */
	public $suggestion;

	/**
	 * Method to construct the token object.
	 *
	 * @param   mixed   $term    The term as a string for words or an array for phrases.
	 * @param   string  $lang    The simple language identifier.
	 * @param   string  $spacer  The space separator for phrases. [optional]
	 *
	 * @since   2.5
	 */
	public function __construct($term, $lang, $spacer = ' ')
	{
		if (!$lang)
		{
			$this->language = '*';
		}
		else
		{
			$this->language = $lang;
		}

		// Tokens can be a single word or an array of words representing a phrase.
		if (is_array($term))
		{
			// Populate the token instance.
			$this->term = implode($spacer, $term);
			$this->stem = implode($spacer, array_map(array(Helper::class, 'stem'), $term, array($lang)));
			$this->numeric = false;
			$this->common = false;
			$this->phrase = true;
			$this->length = StringHelper::strlen($this->term);

			/*
			 * Calculate the weight of the token.
			 *
			 * 1. Length of the token up to 30 and divide by 30, add 1.
			 * 2. Round weight to 4 decimal points.
			 */
			$this->weight = (($this->length >= 30 ? 30 : $this->length) / 30) + 1;
			$this->weight = round($this->weight, 4);
		}
		else
		{
			// Populate the token instance.
			$this->term = $term;
			$this->stem = Helper::stem($this->term, $lang);
			$this->numeric = (is_numeric($this->term) || (bool) preg_match('#^[0-9,.\-\+]+$#', $this->term));
			$this->common = $this->numeric ? false : Helper::isCommon($this->term, $lang);
			$this->phrase = false;
			$this->length = StringHelper::strlen($this->term);

			/*
			 * Calculate the weight of the token.
			 *
			 * 1. Length of the token up to 15 and divide by 15.
			 * 2. If common term, divide weight by 8.
			 * 3. If numeric, multiply weight by 1.5.
			 * 4. Round weight to 4 decimal points.
			 */
			$this->weight = ($this->length >= 15 ? 15 : $this->length) / 15;
			$this->weight = $this->common === true ? $this->weight / 8 : $this->weight;
			$this->weight = $this->numeric === true ? $this->weight * 1.5 : $this->weight;
			$this->weight = round($this->weight, 4);
		}
	}
}
