<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerUsers extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function find()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$keyword = $app->input->getString('q');

		$model->setState('filter.search', $keyword);
		$model->setState('list.limit', 50);
		$list = $model->getItems();
		
		if($list)
		{
			$users = array();
			foreach ($list as $item)
			{
				$user = new stdClass();
				$user->value = $item->id;
				$user->text = $item->name.' ('.$item->username.')';
				
				$users[] = $user;
			}
			
			echo json_encode($users);
		}
		
		jexit();
	}

	public function getModel($name = 'Users', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
