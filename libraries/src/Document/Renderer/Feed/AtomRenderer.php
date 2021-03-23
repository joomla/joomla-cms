<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Feed;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * AtomRenderer is a feed that implements the atom specification
 *
 * Please note that just by using this class you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * @link   http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * @since  3.5
 *
 * @property-read  \Joomla\CMS\Document\FeedDocument  $_doc  Reference to the Document object that instantiated the renderer
 */
class AtomRenderer extends DocumentRenderer
{
	/**
	 * Document mime type
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $_mime = 'application/atom+xml';

	/**
	 * Render the feed.
	 *
	 * @param   string  $name     The name of the element to render
	 * @param   array   $params   Array of values
	 * @param   string  $content  Override the output of the renderer
	 *
	 * @return  string  The output of the script
	 *
	 * @see     DocumentRenderer::render()
	 * @since   3.5
	 */
	public function render($name = '', $params = null, $content = null)
	{
		return LayoutHelper::render(
			'joomla.system.atom',
			[
				'document' => $this->_doc,
				'name'     => $name,
				'params'   => $params,
				'content'  => $content,
			]
		);
	}
}
