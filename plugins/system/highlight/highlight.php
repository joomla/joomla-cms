<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Highlight
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
		if (JFactory::getApplication()->isClient('administrator'))
		{
			return true;
		}

		// Set the variables.
		$doc = JFactory::getDocument();
		$input = JFactory::getApplication()->input;
		$extension = $input->get('option', '', 'cmd');

		// Check if the highlighter is enabled.
		if (!JComponentHelper::getParams($extension)->get('highlight_terms', 1))
		{
			return true;
		}

		// Check if the highlighter should be activated in this environment.
		if ($doc->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component')
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
		$filter     = JFilterInput::getInstance();

		$cleanTerms = array();

		foreach ($terms as $term)
		{
			$cleanTerms[] = htmlspecialchars($filter->clean($term, 'string'));
		}

		// Activate the highlighter.
		JHtml::_('behavior.highlight', $cleanTerms);

		// Adjust the component buffer.
		$buf = $doc->getBuffer('component');
		$buf = '<div class="js-highlighter" >' . $buf . '</div>';
		$doc->setBuffer($buf, 'component');

		return true;
	}
}
