<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * HTML behavior class for JXtended Comments
 *
 * @package		Joomla.Administrator
 * @since		1.6
 */
class JHtmlComments
{
	/**
	 * Returns the options for a state select list for comments
	 *
	 * @param	string	The selected value for the list.
	 *
	 * @return	string
	 */
	public function commentStateOptions($selected = null)
	{
		// Published filter
		$options	= array();
		$options[]	= JHTML::_('select.option', '', JText::_('COMMENTS_ALL'));
		$options[]	= JHTML::_('select.option', '0', JText::_('COMMENTS_PENDING'));
		$options[]	= JHTML::_('select.option', '1', JText::_('COMMENTS_PUBLISHED'));
		$options[]	= JHTML::_('select.option', '2', JText::_('COMMENTS_SPAM'));

		return JHtml::_('select.options', $options, 'value', 'text', $selected, false);
	}

	public function commentContextOptions($selected = null)
	{
		$model		= &JModel::getInstance('Comments', 'CommentsModel', array('ignore_request' => true));
		$options	= array();
		$options[]	= JHTML::_('select.option', '*', JText::_('COMMENTS_ALL_CONTEXTS'));
		if ($contexts = $model->getContexts())
		{
			foreach ($contexts as $i => $context)
			{
				$contexts[$i]->text = JText::sprintf('COMMENTS_IN_CONTEXT', $context->value);
			}
			$options = array_merge($options, $contexts);
		}
		return JHtml::_('select.options', $options, 'value', 'text', $selected, false);
	}

	public function statelist($active)
	{
		$options	= array();
		$options[]	= JHTML::_('select.option', '0', JText::_('COMMENTS_INDEX_FILTER_BY_STATE'));
		$options[]	= JHTML::_('select.option', '1', JText::_('COMMENTS_STATE_PUBLISHED'));
		$options[]	= JHTML::_('select.option', '-1', JText::_('COMMENTS_STATE_UNPUBLISHED'));

		$attributes = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';

		return JHTML::_('select.genericlist', $options, 'filter_state', $attributes, 'value', 'text', $active);
	}

	/**
	 * Method to render a given parameters form.
	 *
	 * @param	string	$name	The name of the array for form elements.
	 * @param	string	$ini	An INI formatted string.
	 * @param	string	$file	The XML file to render.
	 * @return	string	A HTML rendered parameters form.
	 */
	public function params($name, $ini, $file)
	{
		jimport('joomla.html.parameter');

		// Load and render the parameters
		$path	= JPATH_COMPONENT.DS.$file;
		$params	= new JParameter($ini, $path);
		$output	= $params->render($name);

		return $output;
	}
}
