<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') || die;

use Joomla\CMS\Document\DocumentRenderer;

/**
 * HTML document renderer for the body bottom queue
 *
 * @since  __DEPLOY_VERSION__
 */
class BodyendRenderer extends DocumentRenderer
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
		return implode('', array_values($this->_doc->getBodyEndChunks()));
	}
}
