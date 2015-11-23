<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerMigrate extends JControllerAdmin
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
		$extension = $app->input->getCmd('extension');
		
		if(!$user->authorise('core.admin', 'com_cjforum'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		try 
		{
			if(!in_array($extension, array('kunena', 'cjblog')))
			{
				throw new Exception(JText::_('COM_CJFORUM_SELECT_MIGRATION_EXTENSION'));
			}
			
			$model = $this->getModel('Migrate'.JString::ucfirst($extension));
			
			if(! JFile::exists(JPATH_ROOT.'/tmp/migrate_'.$extension.'.json'))
			{
				if(!$model->analyse())
				{
					throw new Exception('Err: 1 - '.JText::_('COM_CJFORUM_ERROR_MIGRATION_FAILED'));
				}
				
// 				$model2 = $this->getModel('Migrate');
// 				$model2->rebuildAssets();
			}
			
			$step = $app->input->getInt('step', 0);
			$return = false;
			
			switch ($step)
			{
				case -1:
					$return = $model->syncTopics();
					break;
					
				case -3:
					$return = $model->rebuildAssets();
					break;
					
				case -2:
					$return = $model->syncUsers();
					break;
				
				default:
					$return = $model->migrate($step);
					break;
			}
			
			if($return === false)
			{
				throw new Exception('Err: 2 - '.JText::_('COM_CJFORUM_ERROR_MIGRATION_FAILED'));
			}
			else if($return === 1)
			{
				echo new JResponseJson(-1, "<i class='fa fa-spinner fa-spin'></i> Syncing data now. This may take some time depending on the amount of data present.");
			}
			else 
			{
				switch ($step)
				{
					case -1:
						echo new JResponseJson(-2, "<i class='fa fa-users'></i> Syncing of asset ids started.");
						break;
						
					case -2:
						$app->enqueueMessage("<i class='fa fa-file-text-o'></i> Syncing of topics data is completed.");
						echo new JResponseJson(-3, "<i class='fa fa-users'></i> Syncing of users data started.");
						break;
						
					case -3:
						throw new Exception(JText::_('COM_CJFORUM_MIGRATION_COMPLETED'));
						break;
						
					default:
						echo new JResponseJson($model->getMessage());
						break;
				}
			}
		}
		catch(Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	public function getModel ($name = 'MigrateKunena', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}
}