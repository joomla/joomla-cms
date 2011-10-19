<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Highlight
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * Content plugin to highlight terms.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Highlight
 * @since       2.5
 */
class PlgContentHighlight extends JPlugin
{
	/**
	 * Method to catch the onAfterDispatch event.
	 *
	 * This is where we setup the click-through content highlighting for Finder
	 * search results. The highlighting is done with JavaScript so we just
	 * need to check a few parameters and the JHtml behavior will do the rest.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   object   &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		// Set the variables
		$input		= JFactory::getApplication()->input;
		$extension	= $input->get('option', '', 'cmd');

		// Check if the highlighter is enabled.
		//TODO: Set this to be reusable
		if (!JComponentHelper::getParams('com_finder')->get('highlight_terms', 1))
		{
			return true;
		}

		// Check if the highlighter should be activated in this environment.
		if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component')
		{
			return true;
		}

		// Get the terms to highlight from the request.
		$terms = $input->get('highlight', null);
		$terms = $terms ? unserialize(base64_decode($terms)) : null;

		// Check the terms.
		if (empty($terms))
		{
			return true;
		}

		// Activate the highlighter.
		JHtml::addIncludePath(JPATH_SITE.'/components/com_finder/helpers/html');
		JHtml::stylesheet('plugins/system/finder/media/css/highlight.css', false, false, false);
		JHtml::_('finder.highlighter', $terms);

		// Loop through the terms
		foreach ($terms as $term)
		{
			$article->text = JString::str_ireplace($term, '<br id="highlight-start" />'.$term.'<br id="highlight-end" />', $article->text);
		}

		return true;
	}
}
