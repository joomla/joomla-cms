<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Export view class
 *
 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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

		$export = new SimpleXMLElement("<data-export />");

		foreach ($exportData as $domain)
		{
			$xmlDomain = $export->addChild('domain');
			$xmlDomain->addAttribute('name', $domain->name);
			$xmlDomain->addAttribute('description', $domain->description);

			foreach ($domain->getItems() as $item)
			{
				$xmlItem = $xmlDomain->addChild('item');

				if ($item->id)
				{
					$xmlItem->addAttribute('id', $item->id);
				}

				foreach ($item->getFields() as $field)
				{
					$xmlItem->addChild($field->name, $field->value);
				}
			}
		}

		$dom = new DOMDocument;
		$dom->loadXML($export->asXML());
		$dom->formatOutput = true;

		echo $dom->saveXML();
	}
}
