<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * JDocument head renderer
 *
 * @since  4.0.0
 */
class ScriptsRenderer extends DocumentRenderer
{
	/**
	 * Renders the document script tags and returns the results as a string
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   4.0.0
	 */
	public function render($head, $params = [], $content = null)
	{
		$this->_doc->renderedSrc = [];

		return LayoutHelper::render(
			'joomla.system.scripts',
			[
				'document' => $this->_doc,
				'params'   => $params,
				'content'  => $content,
			]
		);
	}
}
