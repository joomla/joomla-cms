<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\Renderer;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Factory;

/**
 * HTML error page renderer
 *
 * @since  4.0
 * @todo   Change this renderer to use JDocumentHtml instead of JDocumentError, the latter is only used for B/C at this time
 */
class HtmlRenderer extends AbstractRenderer
{
	/**
	 * The format (type) of the error page
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type = 'error';

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public function render(\Throwable $error): string
	{
		$app = Factory::getApplication();

		// Get the current template from the application
		$template = $app->getTemplate();

		// Push the error object into the document
		$this->getDocument()->setError($error);

		if (ob_get_contents())
		{
			ob_end_clean();
		}

		$this->getDocument()->setTitle(\JText::_('Error') . ': ' . $error->getCode());

		return $this->getDocument()->render(
			false,
			[
				'template'  => $template,
				'directory' => JPATH_THEMES,
				'debug'     => JDEBUG
			]
		);
	}
}
