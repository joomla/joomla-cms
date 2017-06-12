<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Content Component HTML Helper
 *
 * @since  1.5
 */
abstract class Icon
{
	/**
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object    $category  The category information
	 * @param   Registry  $params    The item parameters
	 * @param   array     $attribs   Optional attributes for the link
	 * @param   boolean   $legacy    True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the create item link
	 */
	public static function create($category, $params, $attribs = array(), $legacy = false)
	{
		$uri = Uri::getInstance();

		$url = 'index.php?option=com_content&task=article.add&return=' . base64_encode($uri) . '&a_id=0&catid=' . $category->id;

		$text = LayoutHelper::render('joomla.content.icons.create', array('params' => $params, 'legacy' => $legacy));

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] .= ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = HtmlHelper::_('link', \JRoute::_($url), $text, $attribs);

		$output = '<span class="hasTooltip" title="' . HtmlHelper::_('tooltipText', 'COM_CONTENT_CREATE_ARTICLE') . '">' . $button . '</span>';

		return $output;
	}

	/**
	 * Method to generate a link to the email item page for the given article
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the email item link
	 */
	public static function email($article, $params, $attribs = array(), $legacy = false)
	{
		\JLoader::register('MailtoHelper', JPATH_SITE . '/components/com_mailto/helpers/mailto.php');

		$uri      = Uri::getInstance();
		$base     = $uri->toString(array('scheme', 'host', 'port'));
		$template = \JFactory::getApplication()->getTemplate();
		$link     = $base . \JRoute::_(\ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language), false);
		$url      = 'index.php?option=com_mailto&tmpl=component&template=' . $template . '&link=' . \MailtoHelper::addLink($link);

		$status = 'width=400,height=350,menubar=yes,resizable=yes';

		$text = LayoutHelper::render('joomla.content.icons.email', array('params' => $params, 'legacy' => $legacy));

		$attribs['title']   = \JText::_('JGLOBAL_EMAIL_TITLE');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';
		$attribs['class']   = 'dropdown-item';

		return HtmlHelper::_('link', \JRoute::_($url), $text, $attribs);
	}

	/**
	 * Display an edit icon for the article.
	 *
	 * This icon will not display in a popup window, nor if the article is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string	The HTML for the article edit icon.
	 *
	 * @since   1.6
	 */
	public static function edit($article, $params, $attribs = array(), $legacy = false)
	{
		$user = \JFactory::getUser();
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($article->state < 0)
		{
			return;
		}

		// Set the link class
		$attribs['class'] = 'dropdown-item';

		// Show checked_out icon if the article is checked out by a different user
		if (property_exists($article, 'checked_out')
			&& property_exists($article, 'checked_out_time')
			&& $article->checked_out > 0
			&& $article->checked_out != $user->get('id'))
		{
			$checkoutUser = \JFactory::getUser($article->checked_out);
			$date         = HtmlHelper::_('date', $article->checked_out_time);
			$tooltip      = \JText::_('JLIB_HTML_CHECKED_OUT') . ' :: ' . \JText::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name)
				. ' <br> ' . $date;

			$text = LayoutHelper::render('joomla.content.icons.edit_lock', array('tooltip' => $tooltip, 'legacy' => $legacy));

			$output = HtmlHelper::_('link', '#', $text, $attribs);

			return $output;
		}

		$contentUrl = \ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language);
		$url        = $contentUrl . '&task=article.edit&a_id=' . $article->id . '&return=' . base64_encode($uri);

		if ($article->state == 0)
		{
			$overlib = \JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = \JText::_('JPUBLISHED');
		}

		$date   = HtmlHelper::_('date', $article->created);
		$author = $article->created_by_alias ?: $article->author;

		$overlib .= '&lt;br&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br&gt;';
		$overlib .= \JText::sprintf('COM_CONTENT_WRITTEN_BY', htmlspecialchars($author, ENT_COMPAT, 'UTF-8'));

		$text = LayoutHelper::render('joomla.content.icons.edit', array('article' => $article, 'overlib' => $overlib, 'legacy' => $legacy));

		$attribs['title']   = \JText::_('JGLOBAL_EDIT_TITLE');
		$output = HtmlHelper::_('link', \JRoute::_($url), $text, $attribs);

		return $output;
	}

	/**
	 * Method to generate a popup link to print an article
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_popup($article, $params, $attribs = array(), $legacy = false)
	{
		$app = \JFactory::getApplication();
		$input = $app->input;
		$request = $input->request;

		$url  = \ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language);
		$url .= '&tmpl=component&print=1&layout=default&page=' . @ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		$text = LayoutHelper::render('joomla.content.icons.print_popup', array('params' => $params, 'legacy' => $legacy));

		$attribs['title']   = \JText::sprintf('JGLOBAL_PRINT_TITLE', htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'));
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';
		$attribs['class']   = 'dropdown-item';

		return HtmlHelper::_('link', \JRoute::_($url), $text, $attribs);
	}

	/**
	 * Method to generate a link to print an article
	 *
	 * @param   object    $article  Not used, @deprecated for 4.0
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Not used, @deprecated for 4.0
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_screen($article, $params, $attribs = array(), $legacy = false)
	{
		$text = LayoutHelper::render('joomla.content.icons.print_screen', array('params' => $params, 'legacy' => $legacy));

		return '<a href="#" onclick="window.print();return false;">' . $text . '</a>';
	}
}
