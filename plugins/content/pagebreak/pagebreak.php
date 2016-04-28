<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.utilities.utility');

/**
 * Page break plugin
 *
 * <b>Usage:</b>
 * <code><hr class="system-pagebreak" /></code>
 * <code><hr class="system-pagebreak" title="The page title" /></code>
 * or
 * <code><hr class="system-pagebreak" alt="The first page" /></code>
 * or
 * <code><hr class="system-pagebreak" title="The page title" alt="The first page" /></code>
 * or
 * <code><hr class="system-pagebreak" alt="The first page" title="The page title" /></code>
 *
 * @since  1.6
 */
class PlgContentPagebreak extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Plugin that adds a pagebreak into the text and truncates text at that point
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   &$row     The article object.  Note $article->text is also available
	 * @param   mixed    &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  Always returns void or true
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		$app   	 = JFactory::getApplication();
		$view  	 = $app->input->get('view');
		$print 	 = $app->input->getBool('print');
		$showall = $app->input->getBool('showall');
		$full 	 = $app->input->getBool('fullview');

		if ($context != 'com_content.article')
		{
			return;
		}

		// Expression to search for.
		$regex = '#<hr(.*)class="system-pagebreak"(.*)\/>#iU';

		if ($print)
		{
			$row->text = preg_replace($regex, '<br />', $row->text);

			return true;
		}

		// Simple performance check to determine whether bot should process further.
		if (JString::strpos($row->text, 'class="system-pagebreak') === false)
		{
			return true;
		}

		if (!$page)
		{
			$page = 0;
		}

		if ($params->get('intro_only') || $params->get('popup') || $full || $view != 'article')
		{
			$row->text = preg_replace($regex, '', $row->text);

			return;
		}

		// Find all instances of plugin and put in $matches.
		$matches = array();
		preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);

		if ($showall && $this->params->get('showall', 1))
		{
			$hasToc = $this->params->get('multipage_toc', 1);

			if ($hasToc)
			{
				// Display TOC.
				$page = 1;
				$this->_createToc($row, $matches, $page);
			}
			else
			{
				$row->toc = '';
			}

			$row->text = preg_replace($regex, '<br />', $row->text);

			return true;
		}

		// Split the text around the plugin.
		$text = preg_split($regex, $row->text);

		if (!isset($text[$page]))
		{
			throw new Exception(JText::_('JERROR_PAGE_NOT_FOUND'), 404);
		}

		// Count the number of pages.
		$n = count($text);

		// We have found at least one plugin, therefore at least 2 pages.
		if ($n > 1)
		{
			$title  = $this->params->get('title', 1);
			$hasToc = $this->params->get('multipage_toc', 1);

			// Adds heading or title to <site> Title.
			if ($title && $page && $matches[$page - 1][2])
			{
				$attrs = JUtility::parseAttributes($matches[$page - 1][1]);

				if (isset($attrs['title']))
				{
					$row->page_title = $attrs['title'];
				}
				else
				{
					$row->page_title = JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $page + 1);
				}
			}

			// Reset the text, we already hold it in the $text array.
			$row->text = '';

			$style = $this->params->get('style', 'pages');

			if ($style == 'pages')
			{
				// Display TOC.
				if ($hasToc)
				{
					$this->_createToc($row, $matches);
				}
				else
				{
					$row->toc = '';
				}

				// Traditional mos page navigation
				$pageNav = new JPagination($n, $page, 1);

				// Page counter.
				$row->text .= '<div class="pagenavcounter">';
				$row->text .= $pageNav->getPagesCounter();
				$row->text .= '</div>';

				// Page text.
				$text[$page] = str_replace('<hr id="system-readmore" />', '', $text[$page]);
				$row->text .= $text[$page];

				// Adds navigation between pages to bottom of text.
				if ($hasToc)
				{
					$this->_createNavigation($row, $page, $n);
				}

				// Page links shown at bottom of page if TOC disabled.
				if (!$hasToc)
				{
					$row->text .= '<div class="pager">';
					$row->text .= $pageNav->getPagesLinks();
					$row->text .= '</div>';
				}
			}

			if ($style == 'tabs' || $style == 'sliders')
			{
				$t[] = $text[0];

				$t[] = (string) JHtml::_($style . '.start', 'article' . $row->id . '-' . $style);

				foreach ($text as $key => $subtext)
				{
					if ($key >= 1)
					{
						$match = $matches[$key - 1];
						$match = (array) JUtility::parseAttributes($match[0]);

						if (isset($match['alt']))
						{
							$title = stripslashes($match['alt']);
						}
						elseif (isset($match['title']))
						{
							$title = stripslashes($match['title']);
						}
						else
						{
							$title = JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $key + 1);
						}

						$t[] = (string) JHtml::_($style . '.panel', $title, 'article' . $row->id . '-' . $style . $key);
					}

					$t[] = (string) $subtext;
				}

				$t[] = (string) JHtml::_($style . '.end');

				$row->text = implode(' ', $t);
			}

			if ($style == 'newtabs')
			{
				$path = JPluginHelper::getLayoutPath('content', 'pagebreak', 'newtabs');

				include $path;
			}

			if ($style == 'newsliders')
			{
				$path = JPluginHelper::getLayoutPath('content', 'pagebreak', 'newsliders');

				include $path;
			}
		}

		return true;
	}

	/**
	 * Creates a Table of Contents for the pagebreak
	 *
	 * @param   object  &$row      The article object.  Note $article->text is also available
	 * @param   array   &$matches  Array of matches of a regex in onContentPrepare
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function _createToc(&$row, &$matches)
	{
		$path = JPluginHelper::getLayoutPath('content', 'pagebreak', 'tableofcontent');

		ob_start();
		include $path;
		$row->toc = ob_get_clean();
	}

	/**
	 * Creates the navigation for the item
	 *
	 * @param   object  &$row  The article object.  Note $article->text is also available
	 * @param   int     $page  The page number
	 * @param   int     $n     The total number of pages
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _createNavigation(&$row, $page, $n)
	{
		// We need a next button for all pages, exept the last one
		if ($page < $n - 1)
		{
			$page_next = $page + 1;
			$link_next = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=&limitstart=' . ($page_next));
		}
		else
		{
			$page_next = '';
			$link_next = null;
		}

		// We need a prev button for all pages exept the first one
		if ($page > 0)
		{
			$page_prev = $page - 1 == 0 ? '' : $page - 1;
			$link_prev = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=&limitstart=' . ($page_prev));
		}
		else
		{
			$page_prev = '';
			$link_prev = null;
		}

		// Collect data for the layout
		$data       = array(
						'page_next' => $page_next,
						'link_next' => $link_next,
						'page_prev' => $page_prev,
						'link_prev' => $link_prev
					);

		// JLayout
		$layout     = new JLayoutFile('plugins.content.pagebreak.navigation', $basePath = null);
		$row->text .= $layout->render($data);
	}
}
