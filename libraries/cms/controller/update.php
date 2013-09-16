<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/
class JControllerDisplay extends JControllerBase
{
	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix = 'Content';

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Check for request forgeries.
		if(!JSession::checkToken())
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JINVALID_TOKEN'));
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}
		$viewName     = $this->input->getWord('view', 'article');
		$viewFormat   = $document->getType();
		$layoutName   = $this->input->getWord('layout', 'edit');

		$model = new $this->prefix . 'Model' . $viewName ;
		$data  = $this->input->post->get('jform', array(), 'array');

		// Complete data array if needed
		$oldData = $model->getData();
		$data = array_replace($oldData, $data);

		// Get request type
		$saveFormat   = JFactory::getDocument()->getType();

		// Handle service requests
		if ($saveFormat == 'json')
		{
			$return = $model->save($data);

			return $return;
		}

		// Must load after serving service-requests
		$form  = $model->getForm();

		// Validate the posted data.
		$return = $model->validate($form, $data);
	}
}