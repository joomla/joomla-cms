<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyHelper', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/privacy.php');

/**
 * Export view class
 *
 * @since  3.9.0
 *
 * @property-read   \Joomla\CMS\Document\XmlDocument  $document
 */
class PrivacyViewExport extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   3.9.0
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		/** @var PrivacyModelExport $model */
		$model = $this->getModel();

		$exportData = $model->collectDataForExportRequest();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$requestId = $model->getState($model->getName() . '.request_id');

		// This document should always be downloaded
		$this->document->setDownload(true);
		$this->document->setName('export-request-' . $requestId);

		echo PrivacyHelper::renderDataAsXml($exportData);
	}
}
