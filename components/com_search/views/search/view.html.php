<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * HTML View class for the search component
 *
 * @since  1.0
 */
class SearchViewSearch extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		JLoader::register('SearchHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/search.php');

		$app     = JFactory::getApplication();
		$uri     = JUri::getInstance();
		$error   = null;
		$rows    = null;
		$results = null;
		$total   = 0;

		// Get some data from the model
		$areas      = $this->get('areas');
		$state      = $this->get('state');
		$searchword = $state->get('keyword');
		$params     = $app->getParams();

		$menus = $app->getMenu();
		$menu  = $menus->getActive();

		// Because the application sets a default page title, we need to get it right from the menu item itself
		if (is_object($menu))
		{
			$menu_params = new Registry($menu->params);

			if (!$menu_params->get('page_title'))
			{
				$params->set('page_title', JText::_('COM_SEARCH_SEARCH'));
			}
		}
		else
		{
			$params->set('page_title', JText::_('COM_SEARCH_SEARCH'));
		}

		$title = $params->get('page_title');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($params->get('menu-meta_description'))
		{
			$this->document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$this->document->setMetadata('robots', $params->get('robots'));
		}

		// Built select lists
		$orders   = array();
		$orders[] = JHtml::_('select.option', 'newest', JText::_('COM_SEARCH_NEWEST_FIRST'));
		$orders[] = JHtml::_('select.option', 'oldest', JText::_('COM_SEARCH_OLDEST_FIRST'));
		$orders[] = JHtml::_('select.option', 'popular', JText::_('COM_SEARCH_MOST_POPULAR'));
		$orders[] = JHtml::_('select.option', 'alpha', JText::_('COM_SEARCH_ALPHABETICAL'));
		$orders[] = JHtml::_('select.option', 'category', JText::_('JCATEGORY'));

		$lists             = array();
		$lists['ordering'] = JHtml::_('select.genericlist', $orders, 'ordering', 'class="inputbox"', 'value', 'text', $state->get('ordering'));

		$searchphrases         = array();
		$searchphrases[]       = JHtml::_('select.option', 'all', JText::_('COM_SEARCH_ALL_WORDS'));
		$searchphrases[]       = JHtml::_('select.option', 'any', JText::_('COM_SEARCH_ANY_WORDS'));
		$searchphrases[]       = JHtml::_('select.option', 'exact', JText::_('COM_SEARCH_EXACT_PHRASE'));
		$lists['searchphrase'] = JHtml::_('select.radiolist', $searchphrases, 'searchphrase', '', 'value', 'text', $state->get('match'));

		// Log the search
		JSearchHelper::logSearch($searchword, 'com_search');

		// Limit searchword
		$lang        = JFactory::getLanguage();
		$upper_limit = $lang->getUpperLimitSearchWord();
		$lower_limit = $lang->getLowerLimitSearchWord();

		if (SearchHelper::limitSearchWord($searchword))
		{
			$error = JText::sprintf('COM_SEARCH_ERROR_SEARCH_MESSAGE', $lower_limit, $upper_limit);
		}

		// Sanitise searchword
		if (SearchHelper::santiseSearchWord($searchword, $state->get('match')))
		{
			$error = JText::_('COM_SEARCH_ERROR_IGNOREKEYWORD');
		}

		if (!$searchword && !empty($this->input) && count($this->input->post))
		{
			// $error = JText::_('COM_SEARCH_ERROR_ENTERKEYWORD');
		}

		// Put the filtered results back into the model
		// for next release, the checks should be done in the model perhaps...
		$state->set('keyword', $searchword);

		if ($error == null)
		{
			$results    = $this->get('data');
			$total      = $this->get('total');
			$pagination = $this->get('pagination');

			JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

			for ($i = 0, $count = count($results); $i < $count; $i++)
			{
				$row = & $results[$i]->text;

				if ($state->get('match') == 'exact')
				{
					$searchwords = array($searchword);
					$needle      = $searchword;
				}
				else
				{
					$searchworda = preg_replace('#\xE3\x80\x80#s', ' ', $searchword);
					$searchwords = preg_split("/\s+/u", $searchworda);
					$needle      = $searchwords[0];
				}

				$row          = SearchHelper::prepareSearchContent($row, $needle);
				$searchwords  = array_values(array_unique($searchwords));
				$srow         = strtolower(SearchHelper::remove_accents($row));
				$hl1          = '<span class="highlight">';
				$hl2          = '</span>';
				$posCollector = array();
				$mbString     = extension_loaded('mbstring');

				if ($mbString)
				{
					// E.g. german umlauts like ä are converted to ae and so
					// $pos calculated with $srow doesn't match for $row
					$correctPos     = (mb_strlen($srow) > mb_strlen($row));
					$highlighterLen = mb_strlen($hl1 . $hl2);
				}
				else
				{
					// E.g. german umlauts like ä are converted to ae and so
					// $pos calculated with $srow desn't match for $row
					$correctPos     = (StringHelper::strlen($srow) > StringHelper::strlen($row));
					$highlighterLen = StringHelper::strlen($hl1 . $hl2);
				}

				foreach ($searchwords as $hlword)
				{
					if ($mbString)
					{
						if (($pos = mb_strpos($srow, strtolower(SearchHelper::remove_accents($hlword)))) !== false)
						{
							// Iconv transliterates '€' to 'EUR'
							// TODO: add other expanding translations?
							$eur_compensation = $pos > 0 ? substr_count($row, "\xE2\x82\xAC", 0, $pos) * 2 : 0;
							$pos              -= $eur_compensation;

							if ($correctPos)
							{
								// Calculate necessary corrections from 0 to current $pos
								$ChkRow     = mb_substr($row, 0, $pos);
								$sChkRowLen = mb_strlen(strtolower(SearchHelper::remove_accents($ChkRow)));
								$ChkRowLen  = mb_strlen($ChkRow);

								// Correct $pos
								$pos -= ($sChkRowLen - $ChkRowLen);
							}

							// Collect pos and searchword
							$posCollector[$pos] = $hlword;
						}
					}
					else
					{
						if (($pos = StringHelper::strpos($srow, strtolower(SearchHelper::remove_accents($hlword)))) !== false)
						{
							// Iconv transliterates '€' to 'EUR'
							// TODO: add other expanding translations?
							$eur_compensation = $pos > 0 ? substr_count($row, "\xE2\x82\xAC", 0, $pos) * 2 : 0;
							$pos              -= $eur_compensation;

							if ($correctPos)
							{
								// Calculate necessary corrections from 0 to current $pos
								$ChkRow     = StringHelper::substr($row, 0, $pos);
								$sChkRowLen = StringHelper::strlen(strtolower(SearchHelper::remove_accents($ChkRow)));
								$ChkRowLen  = StringHelper::strlen($ChkRow);

								// Correct $pos
								$pos -= ($sChkRowLen - $ChkRowLen);
							}

							// Collect pos and searchword
							$posCollector[$pos] = $hlword;
						}
					}
				}

				if (count($posCollector))
				{
					// Sort by pos. Easier to handle overlapping highlighter-spans
					ksort($posCollector);
					$cnt                = 0;
					$lastHighlighterEnd = -1;

					foreach ($posCollector as  $pos => $hlword)
					{
						$pos += $cnt * $highlighterLen;

						/* Avoid overlapping/corrupted highlighter-spans
						 * TODO $chkOverlap could be used to highlight remaining part
						 * of searchword outside last highlighter-span.
						 * At the moment no additional highlighter is set.*/
						$chkOverlap = $pos - $lastHighlighterEnd;

						if ($chkOverlap >= 0)
						{
							// Set highlighter around searchword
							if ($mbString)
							{
								$hlwordLen = mb_strlen($hlword);
								$row       = mb_substr($row, 0, $pos) . $hl1 . mb_substr($row, $pos, $hlwordLen) . $hl2 . mb_substr($row, $pos + $hlwordLen);
							}
							else
							{
								$hlwordLen = StringHelper::strlen($hlword);
								$row = StringHelper::substr($row, 0, $pos) . $hl1 . StringHelper::substr($row, $pos, StringHelper::strlen($hlword))
									. $hl2 . StringHelper::substr($row, $pos + StringHelper::strlen($hlword));
							}

							$cnt++;
							$lastHighlighterEnd = $pos + $hlwordLen + $highlighterLen;
						}
					}
				}

				$result = & $results[$i];

				if ($result->created)
				{
					$created = JHtml::_('date', $result->created, JText::_('DATE_FORMAT_LC3'));
				}
				else
				{
					$created = '';
				}

				$result->text    = JHtml::_('content.prepare', $result->text, '', 'com_search.search');
				$result->created = $created;
				$result->count   = $i + 1;
			}
		}

		// Check for layout override
		$active = JFactory::getApplication()->getMenu()->getActive();

		if (isset($active->query['layout']))
		{
			$this->setLayout($active->query['layout']);
		}
var_dump($results);
		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$this->pagination    = &$pagination;
		$this->results       = &$results;
		$this->lists         = &$lists;
		$this->params        = &$params;
		$this->ordering      = $state->get('ordering');
		$this->searchword    = $searchword;
		$this->origkeyword   = $state->get('origkeyword');
		$this->searchphrase  = $state->get('match');
		$this->searchareas   = $areas;
		$this->total         = $total;
		$this->error         = $error;
		$this->action        = $uri;

		parent::display($tpl);
	}
}



/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search HTML view class for the Finder package.
 *
 * @since  2.5
 */
class FinderViewSearch extends JViewLegacy
{
	protected $query;

	protected $params;

	protected $state;

	protected $user;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  JError object on failure, void on success.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Get view data.
		$state = $this->get('State');
		$query = $this->get('Query');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderQuery') : null;
		$results = $this->get('Results');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderResults') : null;
		$total = $this->get('Total');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderTotal') : null;
		$pagination = $this->get('Pagination');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderPagination') : null;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Configure the pathway.
		if (!empty($query->input))
		{
			$app->getPathWay()->addItem($this->escape($query->input));
		}

		// Push out the view data.
		$this->state = &$state;
		$this->params = &$params;
		$this->query = &$query;
		$this->results = &$results;
		$this->total = &$total;
		$this->pagination = &$pagination;

		// Check for a double quote in the query string.
		if (strpos($this->query->input, '"'))
		{
			// Get the application router.
			$router =& $app::getRouter();

			// Fix the q variable in the URL.
			if ($router->getVar('q') !== $this->query->input)
			{
				$router->setVar('q', $this->query->input);
			}
		}

		// Log the search
		JSearchHelper::logSearch($this->query->input, 'com_finder');

		// Push out the query data.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$this->suggested = JHtml::_('query.suggested', $query);
		$this->explained = JHtml::_('query.explained', $query);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $app->getMenu()->getActive();
		if (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		$this->prepareDocument($query);

		JDEBUG ? JProfiler::getInstance('Application')->mark('beforeFinderLayout') : null;

		parent::display($tpl);

		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderLayout') : null;
	}

	/**
	 * Method to get hidden input fields for a get form so that control variables
	 * are not lost upon form submission
	 *
	 * @return  string  A string of hidden input form fields
	 *
	 * @since   2.5
	 */
	protected function getFields()
	{
		$fields = null;

		// Get the URI.
		$uri = JUri::getInstance(JRoute::_($this->query->toUri()));
		$uri->delVar('q');
		$uri->delVar('o');
		$uri->delVar('t');
		$uri->delVar('d1');
		$uri->delVar('d2');
		$uri->delVar('w1');
		$uri->delVar('w2');
		$elements = $uri->getQuery(true);

		// Create hidden input elements for each part of the URI.
		foreach ($elements as $n => $v)
		{
			if (is_scalar($v))
			{
				$fields .= '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
			}
		}

		return $fields;
	}

	/**
	 * Method to get the layout file for a search result object.
	 *
	 * @param   string  $layout  The layout file to check. [optional]
	 *
	 * @return  string  The layout file to use.
	 *
	 * @since   2.5
	 */
	protected function getLayoutFile($layout = null)
	{
		// Create and sanitize the file name.
		$file = $this->_layout . '_' . preg_replace('/[^A-Z0-9_\.-]/i', '', $layout);

		// Check if the file exists.
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));
		$exists = JPath::find($this->_path['template'], $filetofind);

		return ($exists ? $layout : 'result');
	}

	/**
	 * Prepares the document
	 *
	 * @param   FinderIndexerQuery  $query  The search query
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function prepareDocument($query)
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_FINDER_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($layout = $this->params->get('article_layout'))
		{
			$this->setLayout($layout);
		}

		// Configure the document meta-description.
		if (!empty($this->explained))
		{
			$explained = $this->escape(html_entity_decode(strip_tags($this->explained), ENT_QUOTES, 'UTF-8'));
			$this->document->setDescription($explained);
		}

		// Configure the document meta-keywords.
		if (!empty($query->highlight))
		{
			$this->document->setMetadata('keywords', implode(', ', $query->highlight));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Add feed link to the document head.
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			// Add the RSS link.
			$props = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$route = JRoute::_($this->query->toUri() . '&format=feed&type=rss');
			$this->document->addHeadLink($route, 'alternate', 'rel', $props);

			// Add the ATOM link.
			$props = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$route = JRoute::_($this->query->toUri() . '&format=feed&type=atom');
			$this->document->addHeadLink($route, 'alternate', 'rel', $props);
		}
	}
}
