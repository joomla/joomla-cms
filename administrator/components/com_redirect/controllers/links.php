<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Redirect link list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 * @since       1.6
 */
class RedirectControllerLinks extends JControllerAdmin
{
	/*
	 * @var  string Model name
	*/
	protected $name = Link;

	/*
	 * @var  string   Model prefix
	*/
	protected $prefix = RedirectModel;

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	* @since  3.1
	*/
	protected $redirectUrl = 'index.php?option=com_redirect&view=links';


	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_redirect';

	/**
	 * @var     string  The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_REDIRECT_LINKS';


	/**
	 * Method to update a record.
	 * @since   1.6
	 */
	public function activate()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids     = $this->input->get('cid', array(), 'array');
		$newUrl  = $this->input->getString('new_url');
		$comment = $this->input->getString('comment');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_REDIRECT_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			JArrayHelper::toInteger($ids);

			// Remove the items.
			if (!$model->activate($ids, $newUrl, $comment))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else {
				$this->setMessage(JText::plural('COM_REDIRECT_N_LINKS_UPDATED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   string  $config  Array of configuration options
	 *
	 * @since   1.6
	 * @deprecated  3.5
	 */
	public function getModel($name = 'Link', $prefix = 'RedirectModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
