<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component HTML Helper
 *
 * @package     Joomla.CMS
 * @subpackage  JHTML
 * @since       3.2
 */
abstract class JHtmlIcon
{
	/**
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object     $categoryId   The category information
	 * @param   JRegistry  $params       The item parameters
	 * @param   array      $attribs      Optional attributes for the link
	 * @param   boolean    $legacy       True to use legacy images, false to use icomoon based graphic
	 * @param   array      $urlSegments  Segments of the form url
	 * @param   string     $tooltip      Language key for display in the tooltip.
	 *
	 * @return  string  The HTML markup for the create item link
	 *
	 * @since  3.1
	 */
	public static function create($categoryId, $params = array(), $attribs = array(), $legacy = false, $urlSegments, $tooltip = '')
	{
		$uri = JURI::getInstance();

		$url = 'index.php?option='. $urlSegments[0] . '&task=' . $urlSegments[1] . '.add&return=' . base64_encode($uri) . '&' . $urlSegments[2] .'=0&catid=' . $categoryId;

		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true);
			}
			else
			{
				$text = '<span class="icon-plus"></span>&#160;' . JText::_('JNEW') . '&#160;';
			}
		}
		else
		{
			$text = JText::_('JNEW') . '&#160;';
		}

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] = $attribs['class'] . ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = JHtml::_('link', JRoute::_($url), $text, $attribs);

		$output = '<span class="hasTip" title="' . JText::sprintf($tooltip) . '">' . $button . '</span>';

		return $output;
	}

	/**
	 * Method to generate a link to the email item page for the given item
	 *
	 * @param   object     $contentItem  The item information
	 * @param   JRegistry  $params       The item parameters
	 * @param   array      $attribs      Attributes for the link
	 * @param   boolean    $legacy       True to use legacy images, false to use icomoon based graphic
	 * @param   string     $router       Custom Router for the page in form Class::Method
	 *
	 * @return  string  The HTML markup for the email item link
	 *
	 * @since  3.1
	 */
	public static function email($contentItem, $params, $attribs, $legacy = false, $urlSegments, $router = '')
	{
		require_once JPATH_SITE . '/components/com_mailto/helpers/mailto.php';

		$uri      = JURI::getInstance();
		$base     = $uri->toString(array('scheme', 'host', 'port'));
		$template = JFactory::getApplication()->getTemplate();

		$typeAlias = $urlSegments[0] . '.' . $urlSegments[1];

		$link = JHelperRoute::getItemRoute($contentItem->id, $contentItem->alias, $contentItem->CatId, $contentItem->language, $typeAlias, $routerName);

		$url      = 'index.php?option=com_mailto&tmpl=component&template=' . $template . '&link=' . MailToHelper::addLink($link);

		$status = 'width=400,height=350,menubar=yes,resizable=yes';

		if ($params['show_icons'])
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/emailButton.png', JText::_('JGLOBAL_EMAIL'), null, true);
			}
			else
			{
				$text = '<span class="icon-envelope"></span> ' . JText::_('JGLOBAL_EMAIL');
			}
		}
		else
		{
			$text = JText::_('JGLOBAL_EMAIL');
		}

		$attribs['title']   = JText::_('JGLOBAL_EMAIL');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);

		return $output;
	}

	/**
	 * Display an edit icon for the content item.
	 *
	 * This icon will not display in a popup window or if the item is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object     $contentItem  The content item information
	 * @param   JRegistry  $params       The item parameters
	 * @param   array      $attribs      Optional attributes for the link
	 * @param   boolean    $legacy       True to use legacy images, false to use icomoon based graphic
	 * @param   array      $urlSegments  Url segments for the edit form
	 *
	 * @return  string  The HTML for the edit icon.
	 *
	 * @since   3.1
	 */
	public static function edit($contentItem, $params, $attribs = array(), $legacy = false,  $urlSegments = array(), $router = '', $tipstring)
	{
		$user = JFactory::getUser();
		$uri  = JURI::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($contentItem->state < 0)
		{
			return;
		}

		JHtml::_('behavior.tooltip');

		// Show checked_out icon if the item is checked out by a different user
		if (property_exists($contentItem, 'checked_out') && property_exists($contentItem, 'checked_out_time') && $contentItem->checked_out > 0
			&& $contentItem->checked_out != $user->get('id'))
		{
			$checkoutUser = JFactory::getUser($contentItem->checked_out);
			$button       = JHtml::_('image', 'system/checked_out.png', null, null, true);
			$date         = JHtml::_('date', $contentItem->checked_out_time);
			$tooltip      = JText::_('JLIB_HTML_CHECKED_OUT') . ' :: ' . JText::sprintf('JLIB_HTML_CHECKED_OUT', $checkoutUser->name) . ' <br /> ' . $date;

			return '<span class="hasTip" title="' . htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8') . '">' . $button . '</span>';
		}

		$url = 'index.php?option=' . $urlSegments[0] . '&task=' . $urlSegments[1] . '.edit&'. $urlSegments[2] . '=' . $contentItem->id . '&return=' . base64_encode($uri);

		if ($contentItem->state == 0)
		{
			$overlib = JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = JText::_('JPUBLISHED');
		}

		$date   = JHtml::_('date', $contentItem->created);
		$author = $contentItem->created_by_alias ? $contentItem->created_by_alias : $contentItem->author;

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= JText::sprintf('JAUTHOR' . ':', htmlspecialchars($author, ENT_COMPAT, 'UTF-8'));

		if ($legacy)
		{
			$icon = $contentItem->state ? 'edit.png' : 'edit_unpublished.png';
			$text = JHtml::_('image', 'system/' . $icon, JText::_('JGLOBAL_EDIT'), null, true);
		}
		else
		{
			$icon = $contentItem->state ? 'edit' : 'eye-close';
			$text = '<span class="hasTip icon-' . $icon . ' tip" title="' . JText::_('JEDIT_ITEM') . ' :: ' . $overlib . '"></span>&#160;' . JText::_('JGLOBAL_EDIT') . '&#160;';
		}

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);

		return $output;
	}

	/**
	 * Method to generate a popup link to print an item
	 *
	 * @param   object     $contentItem  The content item information
	 * @param   JRegistry  $params       The item parameters
	 * @param   array      $attribs      Optional attributes for the link
	 * @param   boolean    $legacy       True to use legacy images, false to use icomoon based graphic
	 * @param   array      $urlSegments  Url segments for the edit form
	 * @param   string     $router       Custom Router for the page in form Class::Method
	 *
	 * @return  string  The HTML markup for the popup link
	 *
	 * @since  3.1
	 */
	public static function print_popup($contentItem, $params = array(), $attribs, $legacy = false, $urlSegments, $router = '')
	{

		// Make a type alias from the $urlSegments

		$typeAlias = $urlSegments[0] . $urlSegments[1];


		$url  = JHelperRoute::getItemRoute($contentItem->id, $contentItem->alias, $contentItem->catid, $contentItem->language, $router, $typeAlias);
		$url .= '&tmpl=component&print=1&layout=default&page=' . @ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($params['show_icons'])
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
			}
			else
			{
				$text = '<span class="icon-print"></span>&#160;' . JText::_('JGLOBAL_PRINT') . '&#160;';
			}
		}
		else
		{
			$text = JText::_('JGLOBAL_PRINT');
		}

		$attribs['title']   = JText::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	/**
	 * Method to generate a link to print an item
	 *
	 * @param   object     $contentItem  Content item
	 * @param   JRegistry  $params       The item parameters
	 * @param   array      $attribs      Optional attributes for the icon
	 * @param   boolean    $legacy       True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 *
	 * @since  3.1
	 */
	public static function print_screen($contentItem, $params, $attribs = array(), $legacy = false, $urlSegments,  $router = '')
	{
		// Checks template image directory for image, if none found default are loaded
		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
			}
			else
			{
				$text = $text = '<span class="icon-print"></i>&#160;' . JText::_('JGLOBAL_PRINT') . '&#160;';
			}
		}
		else
		{
			$text = JText::_('JGLOBAL_PRINT');
		}

		return '<a href="#" onclick="window.print();return false;">' . $text . '</a>';
	}

}
