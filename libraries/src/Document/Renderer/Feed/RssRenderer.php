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
 * RssRenderer is a feed that implements RSS 2.0 Specification
 *
 * @link   http://www.rssboard.org/rss-specification
 * @since  3.5
 *
 * @property-read  \Joomla\CMS\Document\FeedDocument  $_doc  Reference to the Document object that instantiated the renderer
 */
class RssRenderer extends DocumentRenderer
{
	/**
	 * Renderer mime type
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $_mime = 'application/rss+xml';

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
	public function render($name = '', $params = [], $content = null)
	{
		return LayoutHelper::render(
			'joomla.system.rss',
			[
				'document' => $this->_doc,
				'name'     => $name,
				'params'   => $params,
				'content'  => $content,
			]
		);
	}
}
