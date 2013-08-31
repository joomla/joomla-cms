<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/articles.php';

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
class ContentControllerFeatured extends ContentControllerArticles
{
	protected $redirectUrl = 'index.php?option=com_content&view=featured';

	/*
	 * @var  string Model name
	*/
	protected $name = 'Feature';
	/*
	 * @var  string   Model prefix
	*/
	protected $prefix = 'ContentModel';
	/*
	 * @var $contextPrefix  Dot separated context not including the id. Used for featuring.
	*/
	protected $contextPrefix = 'com_content.featured.';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_content';

	/**
	 * Removes an item
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();
		$ids  = $this->input->get('cid', array(), 'array');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.delete', 'com_content.article.'.(int) $id))
			{
				// Prune items that you can't delete.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->featured($ids, 0))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect($this->redirectUrl);
	}


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Feature', $prefix = 'ContentModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	public function featured()
	{
		parent::featured();
	}
}
