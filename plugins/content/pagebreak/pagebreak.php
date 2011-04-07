<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

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
 * @package		Joomla.Plugin
 * @subpackage	Content.pagebreak
 * @since		1.6
 */
class plgContentPagebreak extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{  
		// Expression to search for.
		$regex = '#<hr(.*)class="system-pagebreak"(.*)\/>#iU';

		$print = JRequest::getBool('print');
		$showall = JRequest::getBool('showall');

		if (!$this->params->get('enabled', 1)) {
			$print = true;
		}

		if ($print) {
			$row->text = preg_replace($regex, '<br />', $row->text);
			return true;
		}

		// Simple performance check to determine whether bot should process further.
		if (JString::strpos($row->text, 'class="system-pagebreak') === false) {
			return true;
		}

		$db = JFactory::getDbo();
		$view = JRequest::getString('view');
		$full = JRequest::getBool('fullview');

		if (!$page) {
			$page = 0;
		}

		if ($params->get('intro_only') || $params->get('popup') || $full || $view != 'article') {
			$row->text = preg_replace($regex, '', $row->text);
			return;
		}

		// Find all instances of plugin and put in $matches.
		$matches = array();
		preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);

		if (($showall && $this->params->get('showall', 1))) {
			$hasToc = $this->params->get('multipage_toc', 1);
			if ($hasToc) {
				// Display TOC.
				$page = 1;
				$this->_createToc($row, $matches, $page);
			} else {
				$row->toc = '';
			}
			$row->text = preg_replace($regex, '<br />', $row->text);

			return true;
		}

		// Split the text around the plugin.
		$text = preg_split($regex, $row->text);

		// Count the number of pages.
		$n = count($text);

		// We have found at least one plugin, therefore at least 2 pages.
		if ($n > 1) {
			$title	= $this->params->get('title', 1);
			$hasToc = $this->params->get('multipage_toc', 1);

			// Adds heading or title to <site> Title.
			if ($title) {
				if ($page) {
					$page_text = $page + 1;

					if ($page && @$matches[$page-1][2]) {
						$attrs = JUtility::parseAttributes($matches[$page-1][1]);

						if (@$attrs['title']) {
							$row->page_title = $attrs['title'];
						}
					}
				}
			}

			// Reset the text, we already hold it in the $text array.
			$row->text = '';

			// Display TOC.
			if ($hasToc) {
				$this->_createToc($row, $matches, $page);
			} else {
				$row->toc = '';
			}

			// traditional mos page navigation
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($n, $page, 1);

			// Page counter.
			$row->text .= '<div class="pagenavcounter">';
			$row->text .= $pageNav->getPagesCounter();
			$row->text .= '</div>';

			// Page text.
			$text[$page] = str_replace('<hr id="system-readmore" />', '', $text[$page]);
			$row->text .= $text[$page];

			// $row->text .= '<br />';
			$row->text .= '<div class="pagination">';

			// Adds navigation between pages to bottom of text.
			if ($hasToc) {
				$this->_createNavigation($row, $page, $n);
			}

			// Page links shown at bottom of page if TOC disabled.
			if (!$hasToc) {
				$row->text .= $pageNav->getPagesLinks();
			}

			$row->text .= '</div>';
		}

		return true;
	}

	/**
	 * @return	void
	 * @return	1.6
	 */
	protected function _createTOC(&$row, &$matches, &$page)
	{   
		$heading = isset($row->title) ? $row->title : JText::_('PLG_CONTENT_PAGEBREAK_NO_TITLE');

          
		// TOC header.
		$row->toc .= '<div id="article-index">';
		
		
		if($this->params->get('article_index')==1)
		{
			$headingtext= JText::_('PLG_CONTENT_PAGEBREAK_ARTICLE_INDEX');
	        
			if($this->params->get('article_index_text'))
	        {
	        	htmlspecialchars($headingtext=$this->params->get('article_index_text'));
	       	 }
			$row->toc .='<h3>'.$headingtext.'</h3>';
		
		}

		// TOC first Page link.
		$row->toc .= '<ul>
		<li>
			
			<a href="'. JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&showall=&limitstart=') .'" class="toclink">'
			. $heading .
			'</a>
			
		</li>
		';

		$i = 2;

		foreach ($matches as $bot) {
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&showall=&limitstart='. ($i-1));


			if (@$bot[0]) {
				$attrs2 = JUtility::parseAttributes($bot[0]);

				if (@$attrs2['alt']) {
					$title	= stripslashes($attrs2['alt']);
				} elseif (@$attrs2['title']) {
					$title	= stripslashes($attrs2['title']);
				} else {
					$title	= JText::sprintf('Page #', $i);
				}
			} else {
				$title	= JText::sprintf('Page #', $i);
			}

			$row->toc .= '
				<li>
					
					<a href="'. $link .'" class="toclink">'
					. $title .
					'</a>
				
				</li>
				';
			$i++;
		}

		if ($this->params->get('showall')) {
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&showall=1&limitstart=');
			$row->toc .= '
			<li>
				
					<a href="'. $link .'" class="toclink">'
					. JText::_('PLG_CONTENT_PAGEBREAK_ALL_PAGES') .
					'</a>
			
			</li>
			';
		}
		$row->toc .= '</ul></div>';
	}

	/**
	 * @return	void
	 * @since	1.6
	 */
	protected function _createNavigation(&$row, $page, $n)
	{
		$pnSpace = '';
		if (JText::_('JGLOBAL_LT') || JText::_('JGLOBAL_LT')) {
			$pnSpace = ' ';
		}

		if ($page < $n-1) {
			$page_next = $page + 1;

			$link_next = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&showall=&limitstart='. ($page_next));
			// Next >>
			$next = '<a href="'. $link_next .'">' . JText::_('JNEXT') . $pnSpace . JText::_('JGLOBAL_GT') . JText::_('JGLOBAL_GT') .'</a>';
		} else {
			$next = JText::_('JNEXT');
		}

		if ($page > 0) {
			$page_prev = $page - 1 == 0 ? '' : $page - 1;

			$link_prev = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&showall=&limitstart='. ($page_prev));
			// << Prev
			$prev = '<a href="'. $link_prev .'">'. JText::_('JGLOBAL_LT') . JText::_('JGLOBAL_LT') . $pnSpace . JText::_('JPREV') .'</a>';
		} else {
			$prev = JText::_('JPREV');
		}

		$row->text .= '<ul><li>' . $prev . ' </li><li>' . $next .'</li></ul>';
	}
}
