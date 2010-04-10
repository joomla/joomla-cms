<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controlleradmin' );

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesControllerCategories extends JControllerAdmin
{
	protected $_context = 'com_categories';
	
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register proxy tasks.
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
		$this->setURL('index.php?option=com_categories&view=categories');
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Category', $prefix = 'CategoriesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	/**
	 * Rebuild the nested set tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_categories&view=categories');

		// Initialise variables.
		$model = &$this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('CATEGORIES_REBUILD_SUCCESS'));
			return true;
		}
		else {
			// Rebuild failed.
			$this->setMessage(JText::sprintf('CATEGORIES_REBUILD_FAILED'));
			return false;
		}
	}
}