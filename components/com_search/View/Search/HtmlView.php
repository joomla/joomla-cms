<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Search\Site\View\Search;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Search\Administrator\Helper\SearchHelper;
use Joomla\String\StringHelper;

/**
 * HTML View class for the search component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $pageclass_sfx = '';

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination|null
	 * @since  4.0.0
	 */
	protected $pagination = null;

	/**
	 * The results of the search
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $results = array();

	/**
	 * The select box lists for result filtering
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $lists = array();

	/**
	 * The page parameters
	 *
	 * @var  \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * The ordering for the query
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $ordering = '';

	/**
	 * The search phrase used (after sanity checks)
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $searchword = '';

	/**
	 * The raw search phrase used (before sanity checks)
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $origkeyword = '';

	/**
	 * The search phrase matching preference
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $searchphrase = '';

	/**
	 * The available search 'areas' (plugins that are enabled to search). Key of the array should be the name used
	 * for the filter options and the value should be the language constant to be used for translation.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $searchareas = '';

	/**
	 * The total number of results for the search query
	 *
	 * @var    integer
	 * @since  4.0.0
	 */
	protected $total = 0;

	/**
	 * A translated error message to display to the user
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $error = '';

	/**
	 * The URL instance
	 *
	 * @var    Uri|null
	 * @since  4.0.0
	 */
	protected $action = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since 1.0
	 */
	public function display($tpl = null)
	{
		$app     = Factory::getApplication();
		$uri     = Uri::getInstance();
		$error   = null;
		$results = null;
		$total   = 0;

		// Get some data from the model
		$areas      = $this->get('areas');
		$state      = $this->get('state');
		$searchWord = $state->get('keyword');
		$params     = $app->getParams();

		$menu  = $app->getMenu()->getActive();

		if (!$menu)
		{
			$params->set('page_title', Text::_('COM_SEARCH_SEARCH'));
		}

		$title = $params->get('page_title');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($params->get('menu-meta_description'))
		{
			$this->document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$this->document->setMetaData('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$this->document->setMetaData('robots', $params->get('robots'));
		}

		// Built select lists
		$orders   = array();
		$orders[] = HTMLHelper::_('select.option', 'newest', Text::_('COM_SEARCH_NEWEST_FIRST'));
		$orders[] = HTMLHelper::_('select.option', 'oldest', Text::_('COM_SEARCH_OLDEST_FIRST'));
		$orders[] = HTMLHelper::_('select.option', 'popular', Text::_('COM_SEARCH_MOST_POPULAR'));
		$orders[] = HTMLHelper::_('select.option', 'alpha', Text::_('COM_SEARCH_ALPHABETICAL'));
		$orders[] = HTMLHelper::_('select.option', 'category', Text::_('JCATEGORY'));

		$lists             = array();
		$lists['ordering'] = HTMLHelper::_('select.genericlist', $orders, 'ordering', 'class="custom-select"', 'value', 'text', $state->get('ordering'));

		$searchphrases         = array();
		$searchphrases[]       = HTMLHelper::_('select.option', 'all', Text::_('COM_SEARCH_ALL_WORDS'));
		$searchphrases[]       = HTMLHelper::_('select.option', 'any', Text::_('COM_SEARCH_ANY_WORDS'));
		$searchphrases[]       = HTMLHelper::_('select.option', 'exact', Text::_('COM_SEARCH_EXACT_PHRASE'));
		$lists['searchphrase'] = HTMLHelper::_('select.radiolist', $searchphrases, 'searchphrase', '', 'value', 'text', $state->get('match'));

		// Log the search
		\Joomla\CMS\Helper\SearchHelper::logSearch($searchWord, 'com_search');

		// Limit search-word
		$lang        = Factory::getLanguage();
		$upper_limit = $lang->getUpperLimitSearchWord();
		$lower_limit = $lang->getLowerLimitSearchWord();

		if (SearchHelper::limitSearchWord($searchWord))
		{
			$error = Text::sprintf('COM_SEARCH_ERROR_SEARCH_MESSAGE', $lower_limit, $upper_limit);
		}

		// Sanitise search-word
		if (SearchHelper::santiseSearchWord($searchWord, $state->get('match')))
		{
			$error = Text::_('COM_SEARCH_ERROR_IGNOREKEYWORD');
		}

		if (!$searchWord && !empty($this->input) && count($this->input->post))
		{
			// $error = Text::_('COM_SEARCH_ERROR_ENTERKEYWORD');
		}

		// Put the filtered results back into the model
		// for next release, the checks should be done in the model perhaps...
		$state->set('keyword', $searchWord);

		if ($error === null)
		{
			$results    = $this->get('data');
			$total      = $this->get('total');
			$pagination = $this->get('pagination');

			// Flag indicates to not add limitstart=0 to URL
			$pagination->hideEmptyLimitstart = true;

			if ($state->get('match') === 'exact')
			{
				$searchWords = array($searchWord);
				$needle      = $searchWord;
			}
			else
			{
				$searchWordA = preg_replace('#\xE3\x80\x80#', ' ', $searchWord);
				$searchWords = preg_split("/\s+/u", $searchWordA);
				$needle      = $searchWords[0];
			}

			// Make sure there are no slashes in the needle
			$needle = str_replace('/', '\/', $needle);

			for ($i = 0, $count = count($results); $i < $count; ++$i)
			{
				$rowTitle = &$results[$i]->title;
				$rowTitleHighLighted = $this->highLight($rowTitle, $needle, $searchWords);
				$rowText = &$results[$i]->text;
				$rowTextHighLighted = $this->highLight($rowText, $needle, $searchWords);

				$result = &$results[$i];
				$created = '';

				if ($result->created)
				{
					$created = HTMLHelper::_('date', $result->created, Text::_('DATE_FORMAT_LC3'));
				}

				$result->title   = $rowTitleHighLighted;
				$result->text    = HTMLHelper::_('content.prepare', $rowTextHighLighted, '', 'com_search.search');
				$result->created = $created;
				$result->count   = $i + 1;
			}
		}

		// Check for layout override
		if (isset($menu->query['layout']))
		{
			$this->setLayout($menu->query['layout']);
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$this->pagination    = &$pagination;
		$this->results       = &$results;
		$this->lists         = &$lists;
		$this->params        = &$params;
		$this->ordering      = $state->get('ordering');
		$this->searchword    = $searchWord;
		$this->origkeyword   = $state->get('origkeyword');
		$this->searchphrase  = $state->get('match');
		$this->searchareas   = $areas;
		$this->total         = $total;
		$this->error         = $error;
		$this->action        = $uri;

		parent::display($tpl);
	}

	/**
	 * Method to control the highlighting of keywords
	 *
	 * @param   string  $string       text to be searched
	 * @param   string  $needle       text to search for
	 * @param   array   $searchWords  words to be searched
	 *
	 * @return  mixed  A string.
	 *
	 * @since   3.8.4
	 */
	public function highLight($string, $needle, $searchWords)
	{
		$hl1            = '<span class="highlight">';
		$hl2            = '</span>';
		$mbString       = extension_loaded('mbstring');
		$highlighterLen = strlen($hl1 . $hl2);

		// Doing HTML entity decoding here, just in case we get any HTML entities here.
		$quoteStyle   = version_compare(PHP_VERSION, '5.4', '>=') ? ENT_NOQUOTES | ENT_HTML401 : ENT_NOQUOTES;
		$row          = html_entity_decode($string, $quoteStyle, 'UTF-8');
		$row          = SearchHelper::prepareSearchContent($row, $needle);
		$searchWords  = array_values(array_unique($searchWords));
		$lowerCaseRow = $mbString ? mb_strtolower($row) : StringHelper::strtolower($row);

		$transliteratedLowerCaseRow = SearchHelper::remove_accents($lowerCaseRow);

		$posCollector = array();

		foreach ($searchWords as $highlightWord)
		{
			$found = false;

			if ($mbString)
			{
				$lowerCaseHighlightWord = mb_strtolower($highlightWord);

				if (($pos = mb_strpos($lowerCaseRow, $lowerCaseHighlightWord)) !== false)
				{
					$found = true;
				}
				elseif (($pos = mb_strpos($transliteratedLowerCaseRow, $lowerCaseHighlightWord)) !== false)
				{
					$found = true;
				}
			}
			else
			{
				$lowerCaseHighlightWord = StringHelper::strtolower($highlightWord);

				if (($pos = StringHelper::strpos($lowerCaseRow, $lowerCaseHighlightWord)) !== false)
				{
					$found = true;
				}
				elseif (($pos = StringHelper::strpos($transliteratedLowerCaseRow, $lowerCaseHighlightWord)) !== false)
				{
					$found = true;
				}
			}

			if ($found === true)
			{
				// Iconv transliterates '€' to 'EUR'
				// TODO: add other expanding translations?
				$eur_compensation = $pos > 0 ? substr_count($row, "\xE2\x82\xAC", 0, $pos) * 2 : 0;
				$pos -= $eur_compensation;

				// Collect pos and search-word
				$posCollector[$pos] = $highlightWord;
			}
		}

		if (count($posCollector))
		{
			// Sort by pos. Easier to handle overlapping highlighter-spans
			ksort($posCollector);
			$cnt                = 0;
			$lastHighlighterEnd = -1;

			foreach ($posCollector as $pos => $highlightWord)
			{
				$pos += $cnt * $highlighterLen;

				/*
				 * Avoid overlapping/corrupted highlighter-spans
				 * TODO $chkOverlap could be used to highlight remaining part
				 * of search-word outside last highlighter-span.
				 * At the moment no additional highlighter is set.
				 */
				$chkOverlap = $pos - $lastHighlighterEnd;

				if ($chkOverlap >= 0)
				{
					// Set highlighter around search-word
					if ($mbString)
					{
						$highlightWordLen = mb_strlen($highlightWord);
						$row              = mb_substr($row, 0, $pos) . $hl1 . mb_substr($row, $pos, $highlightWordLen)
							. $hl2 . mb_substr($row, $pos + $highlightWordLen);
					}
					else
					{
						$highlightWordLen = StringHelper::strlen($highlightWord);
						$row              = StringHelper::substr($row, 0, $pos)
							. $hl1 . StringHelper::substr($row, $pos, StringHelper::strlen($highlightWord))
							. $hl2 . StringHelper::substr($row, $pos + StringHelper::strlen($highlightWord));
					}

					$cnt++;
					$lastHighlighterEnd = $pos + $highlightWordLen + $highlighterLen;
				}
			}
		}

		return $row;
	}
}
