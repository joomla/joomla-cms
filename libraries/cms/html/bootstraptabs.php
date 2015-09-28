<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for Tabs elements.
 *
 * @since  1.6
 */
abstract class JHtmlBootstraptabs
{
	/**
	 * Loads the Javascript behavior and returns a JHtmlElement wrapper for the tabs
	 * This wrapper should be sent to the panel function.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array   $params  An array of option.
	 *
	 * @return  JHtmlElement
	 *
	 * @since   3.5
	 */
	public static function getWrapper($group = 'tabs', $params = array())
	{
		static::loadBehavior($group, $params);

		$wrapper = new JHtmlElement('dl', array('class', 'tabs', 'id' => $group));

		// Not sure why we are adding an empty dt/dd container, but whatever we'll keep it for now.
		$wrapper->addChild('dt', array('style', 'display:none;'))->addChild('dd', array('style', 'display:none'));
		return $wrapper;
	}

	/**
	 * Adds a new tab control and panel to the tab wrapper
	 * You can then use the returned object reference to populate
	 * the panel content using JHtmlElement::addInnerHtml or JHtmlElement::setInnerHtml
	 *
	 * @param   JHtmlElement  $wrapper  Wrapper element for the tab set
	 * @param   string        $text     Text to display.
	 * @param   string        $id       Identifier of the panel.
	 *
	 * @return  JHtmlElement  reference to a new panel object to put content into
	 *
	 * @since   1.6
	 */
	public static function panel(JHtmlElement $wrapper, $text, $id)
	{
		$wrapper->addChild('dt', array('class' => 'tabs' . $id))
				->addChild('span')
				->addChild('h3')
				->addChild('a', array('href' => 'javascript:void(0);'), $text);

		return $wrapper->addChild('dd', array('class' => 'tabs'));
	}

	/**
	 * Load the JavaScript behavior.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array   $params  Array of options.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected static function loadBehavior($group, $params = array())
	{
		static $loaded = array();

		if (!array_key_exists((string) $group, $loaded))
		{
			// Include MooTools framework
			JHtml::_('behavior.framework', true);

			$opt['onActive']            = (isset($params['onActive'])) ? '\\' . $params['onActive'] : null;
			$opt['onBackground']        = (isset($params['onBackground'])) ? '\\' . $params['onBackground'] : null;
			$opt['display']             = (isset($params['startOffset'])) ? (int) $params['startOffset'] : null;
			$opt['useStorage']          = (isset($params['useCookie']) && $params['useCookie']) ? 'true' : 'false';
			$opt['titleSelector']       = "dt.tabs";
			$opt['descriptionSelector'] = "dd.tabs";

			$options = JHtml::getJSObject($opt);

			$js = '	window.addEvent(\'domready\', function(){
						$$(\'dl#' . $group . '.tabs\').each(function(tabs){
							new JTabs(tabs, ' . $options . ');
						});
					});';

			$document = JFactory::getDocument();
			$document->addScriptDeclaration($js);
			JHtml::_('script', 'system/tabs.js', false, true);

			$loaded[(string) $group] = true;
		}
	}
}
