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
		static $jsNotOut =true;

		$app = JFactory::getApplication();

		$renderer = $this->_doc->loadRenderer('module');
		$buffer = '';

		foreach (JModuleHelper::getModules($position) as $mod)
		{
			$moduleHtml = trim($renderer->render($mod, $params, $content));

			if (!$moduleHtml)
			{
				continue;
			}


			if ($app->isAdmin()
				|| !JFactory::getUser()->authorise('core.manage', 'com_modules')
				|| preg_match('/<(?:div|span|nav|ul) [^>]*class="[^"]* jmoddiv"/', $moduleHtml))
			{
				// Module isn't enclosing in a div with class or handles already module edit button:
				$buffer .= $moduleHtml;
				continue;
			}

			// Add css class jmoddiv and data attributes for module-editing URL and for the tooltip:
			$editUrl = JURI::base() . 'administrator/' . 'index.php?option=com_modules&view=module&layout=edit&id=' . (int) $mod->id;

			$buffer .= preg_replace('/^(<(?:div|span|nav|ul) [^>]*class="[^"]*)"/',
					'\\1 jmoddiv" data-jmodediturl="' . $editUrl . '" data-jmodtip="' . sprintf(JHtml::tooltipText('JLIB_HTML_EDIT_MODULE', 'JLIB_HTML_EDIT_MODULE_IN_POSITION'), htmlspecialchars($position)) . '"', $moduleHtml);

			if ($jsNotOut)
			{
				// Load once booststrap tooltip and add stylesheet and javascript to head:
				$jsNotOut = false;
				JHtml::_('bootstrap.tooltip');

				JFactory::getDocument()->addStyleSheet('media/system/css/frontediting.css')
					->addScript('media/system/js/frontediting.js');
			}

		}
		return $buffer;
	}
}
