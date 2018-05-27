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
 * Request management controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyControllerRequest extends JControllerLegacy
{
	/**
	 * Method to export the data for a request.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function export()
	{
		$this->input->set('view', 'export');

		/** @var \Joomla\CMS\Document\XmlDocument $document */
		$document = \JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');
		/** @var PrivacyViewExport $view */
		$view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

		$view->document = $document;

		$cid = $this->input->post->get('cid', array(), 'array');

		$requestId = (int) (count($cid) ? $cid[0] : 0);

		// This document should always be downloaded
		$document->setDownload(true);
		$document->setName('export-request-' . $requestId);

		/** @var PrivacyModelExport $model */
		$model = $this->getModel('Export', 'PrivacyModel');
		$model->setState($model->getName() . '.request_id', $requestId);

		$view->setModel($model, true);

		$view->display();

		return $this;
	}
}
