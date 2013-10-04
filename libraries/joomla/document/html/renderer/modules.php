<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument Modules renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererModules extends JDocumentRenderer
{
	/**
	 * Renders multiple modules script and returns the results as a string
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $params    Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render($position, $params = array(), $content = null)
	{
		$renderer = $this->_doc->loadRenderer('module');
		$buffer = '';

		$app = JFactory::getApplication();
		$frontediting = $app->get('frontediting', 1);
		$user = JFactory::getUser();

		$canEdit = $user->id && $frontediting && !($app->isAdmin() && $frontediting < 2) && $user->authorise('core.edit', 'com_modules');
		$menusEditing = ($frontediting == 2) && $user->authorise('core.edit', 'com_menus');

		foreach (JModuleHelper::getModules($position) as $mod)
		{
			$moduleHtml = trim($renderer->render($mod, $params, $content));

			if ($canEdit && $moduleHtml != '')
				// Once FR http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=28638&start=200 && PR https://github.com/joomla/joomla-cms/pull/1930/files are merged:
				//  +    && $user->authorise('core.edit', 'com_modules.module.' . $mod->id)
			{
				JLayoutHelper::render('joomla.edit.frontediting_modules', array('moduleHtml' => &$moduleHtml, 'module' => $mod, 'position' => $position, 'menusediting' => $menusEditing));
			}
			$buffer .= $moduleHtml;
		}
		return $buffer;
	}
}
