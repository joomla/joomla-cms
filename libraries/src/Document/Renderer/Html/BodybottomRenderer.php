<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Factory;

/**
 * HTML document renderer for the body bottom queue
 *
 * @since  __DEPLOY_VERSION__
 */
class BodybottomRenderer extends DocumentRenderer
{
	/**
	 * Renders the body bottom queue and returns it as a string
	 *
	 * @param   string  $name     Not used.
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  Not used.
	 *
	 * @return  string  The output of the script
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function render($name, $params = array(), $content = null)
	{
		$buffer  = '';
		$content = Factory::getDocument()->getBodyBottom();

		foreach ($content as $key => $value)
		{
			$buffer .= $value;
		}

		return $buffer;
	}
}
