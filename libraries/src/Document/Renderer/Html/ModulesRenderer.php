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

/**
 * HTML document renderer for a module position
 *
 * @since  3.5
 */
class ModulesRenderer extends DocumentRenderer
{
	/**
	 * Renders multiple modules script and returns the results as a string
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $attribs   Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.5
	 */
	public function render($position, $attribs = [], $content = null)
	{
		return LayoutHelper::render(
			'joomla.system.modules',
			[
				'position' => $position,
				'attribs'  => $attribs,
				'content'  => $content,
				'document' => $this->_doc,
			]
		);
	}
}
