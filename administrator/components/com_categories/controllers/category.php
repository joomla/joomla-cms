<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesControllerCategory extends JControllerForm
{
	/**
	 * @var		string	The extension for which the categories apply.
	 * @since	1.6
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Guess the JText message prefix. Defaults to the option.
		if (empty($this->extension)) {
			$this->extension = JRequest::getCmd('extension', 'com_content');
		}
	}

	/**
	 * Method to check if you can add a new record.
 	 *
	 * Extended classes can override this if necessary.
 	 *
	 * @param	array	An array of input data.
 	 *
	 * @return	boolean
	 * @since	1.6
 	 */
	protected function allowAdd($data = array())
 	{
		return JFactory::getUser()->authorise('core.create', $this->extension);
 	}

 	/**
	 * Method to check if you can edit a record.
 	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
 	 */
	protected function allowEdit($data = array(), $key = 'parent_id')
 	{
		return JFactory::getUser()->authorise('core.edit', $this->extension.'.category.'.$data[$key]);
 	}

 	/**
	 * Method to run batch opterations.
 	 *
 	 * @return	void
 	 */
	public function batch()
 	{
 		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

 		// Initialise variables.
 		$app	= JFactory::getApplication();
 		$model	= $this->getModel('Category');
		$vars	= JRequest::getVar('batch', array(), 'post', 'array');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

 		$extension = JRequest::getCmd('extension', '');
 		if ($extension) {
 			$extension = '&extension='.$extension;
 		}

		// Preset the redirect
		$this->setRedirect('index.php?option=com_categories&view=categories'.$extension);

		// Attempt to run the batch operation.
		if ($model->batch($vars, $cid)) {
			$this->setMessage(JText::_('Categories_Batch_success'));
			return true;
 		} else {
			$this->setMessage(JText::_(JText::sprintf('COM_CATEGORIES_ERROR_BATCH_FAILED', $model->getError())));
			return false;
 		}
	}

 	/**
	 * Gets the URL arguments to append to an item redirect.
 	 *
	 * @param	int		$recordId	The primary key id for the item.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 * @since	1.6
 	 */
	protected function getRedirectToItemAppend($recordId = null)
 	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension='.$this->extension;

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 * @since	1.6
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension='.$this->extension;

		return $append;
 	}
}
