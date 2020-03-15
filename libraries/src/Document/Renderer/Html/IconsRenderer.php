<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * HTML document renderer for the inline svg icons output
 *
 * @since  4.0.0
 */
class IconsRenderer extends DocumentRenderer
{
	/**
	 * Renders the inline svg icons and returns the results as a string
	 *
	 * @param   string  $name     Not used.
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  Not used.
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.5
	 */
	public function render($name, $params = array(), $content = null)
	{
		$files = [];

		// Generate the file and load the stylesheet link
		foreach ($this->_doc->getIcons() as $key => $icon)
		{
			$file = HTMLHelper::image('vendor/' . $icon['provider'] . '/' . $icon['group'] . '/' . $icon['icon'] . '.svg', '', null, true, 1);

			if ($file)
			{
				$content = @file_get_contents(JPATH_ROOT . substr($file, strpos($file, '/media')));
				$content = str_replace(
					'<svg',
					'<svg id="' . $icon['provider'] . '-' . $icon['group'] . '-' . $icon['icon'] . '"'
					. ' title="' . $icon['text'] . '"'
					. ' role="img"',
					$content
				);

				$files[] = $content;
			}
		}

		if (!empty($files))
		{
			// Reset the icons registry
			$this->_doc->setIcons([]);

			return '<div style="display:none">' . implode('', $files) . '</div>';
		}

		return '';
	}
}

