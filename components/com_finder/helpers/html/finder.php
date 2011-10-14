<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * String Behaviors for Finder.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class JHtmlFinder
{
	/**
	 * Method to setup the JavaScript highlight behavior.
	 *
	 * @param   array  $terms  An array of terms to highlight.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function highlighter($terms)
	{
		// Get the document object.
		$doc = JFactory::getDocument();

		// We only want to highlight text on regular html pages.
		if ($doc->getType() == 'html' && JFactory::getApplication()->input->get('tmpl', null, 'cmd') !== 'component')
		{
			// Add the highlighter media.
			JHtml::script('com_finder/highlighter.js', false, true);

			// Add the terms to highlight.
			$doc->addScriptDeclaration("window.highlight = [\"".implode('","', $terms)."\"];");
		}
	}
}
