<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of tracks.
 *
 * @since  1.6
 */
class BannersViewTracks extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$basename = $this->get('BaseName');
		$filetype = $this->get('FileType');
		$mimetype = $this->get('MimeType');
		$content  = $this->get('Content');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$document = JFactory::getDocument();
		$document->setMimeEncoding($mimetype);
		JFactory::getApplication()
			->setHeader(
				'Content-disposition',
				'attachment; filename="' . $basename . '.' . $filetype . '"; creation-date="' . JFactory::getDate()->toRFC822() . '"',
				true
			);
		echo $content;
	}
}
