<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;

/**
 * HTML document renderer for a single module
 *
 * @since  3.5
 */
class ModuleRenderer extends DocumentRenderer
{
	/**
	 * Renders a module script and returns the results as a string
	 *
	 * @param   string  $module   The name of the module to render
	 * @param   array   $attribs  Associative array of values
	 * @param   string  $content  If present, module information from the buffer will be used
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.5
	 */
	public function render($module, $attribs = [], $content = null)
	{
		return LayoutHelper::render(
			'joomla.system.module',
			[
				'module'  => $module,
				'attribs' => $attribs,
				'content' => $content,
			]
		);
	}
}
