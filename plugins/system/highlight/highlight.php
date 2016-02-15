<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Highlight
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * System plugin to highlight terms.
 *
 * @since  2.5
 */
class PlgSystemHighlight extends JPlugin
{
	/**
	 * Method to catch the onAfterDispatch event.
	 *
	 * This is where we setup the click-through content highlighting for.
	 * The highlighting is done with JavaScript so we just
	 * need to check a few parameters and the JHtml behavior will do the rest.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5
	 */
	public function onAfterDispatch()
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		// Set the variables.
		$input = JFactory::getApplication()->input;
		$extension = $input->get('option', '', 'cmd');

		// Check if the highlighter is enabled.
		if (!JComponentHelper::getParams($extension)->get('highlight_terms', 1))
		{
			return true;
		}

		// Check if the highlighter should be activated in this environment.
		if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component')
		{
			return true;
		}

		// Get the terms to highlight from the request.
		$terms = $input->request->get('highlight', null, 'base64');
		$terms = $terms ? json_decode(base64_decode($terms)) : null;

		// Check the terms.
		if (empty($terms))
		{
			return true;
		}

		// Clean the terms array.
		$filter = JFilterInput::getInstance();

		$cleanTerms = array();

		foreach ($terms as $term)
		{
			$cleanTerms[] = htmlspecialchars($filter->clean($term, 'string'));
		}

		// Activate the highlighter.
		JHtml::_('behavior.highlighter', $cleanTerms);

		// Adjust the component buffer.
		$doc = JFactory::getDocument();
		$buf = $doc->getBuffer('component');
		$buf = '<br id="highlighter-start" />' . $buf . '<br id="highlighter-end" />';
		$doc->setBuffer($buf, 'component');

		return true;
	}
}
