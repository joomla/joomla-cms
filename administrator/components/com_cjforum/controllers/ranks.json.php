<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerRanks extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	public function execute ($task)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$model = $this->getModel();
		
		if(!$user->authorise('core.admin'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$startId = $app->input->getInt('startId', 0);
		$endId = $app->input->getInt('endId', 0);
		
		try 
		{
			if($startId == 0 && $endId == 0)
			{
				$result = $model->getFirstAndLastUserId();
				
				if(empty($result->min_id) || empty($result->max_id))
				{
					throw new Exception(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
				}
				else 
				{
					echo new JResponseJson($result, "<i class='fa fa-spinner fa-spin'></i> Syncing data now. This may take some time depending on the amount of data present.");
				}
			}
			else if($model->syncRanks($startId, $endId))
			{
				echo new JResponseJson(-1, "<i class='fa fa-spinner fa-spin'></i> Syncing data now. This may take some time depending on the amount of data present.");
			}
			else 
			{
				throw new Exception(JText::_('COM_CJFORUM_OPERATION_SUCCESSFULLY_COMPLETED'));
			}
		}
		catch(Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	public function getModel ($name = 'Ranks', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}