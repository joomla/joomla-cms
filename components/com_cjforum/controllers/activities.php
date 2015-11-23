<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerActivities extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function getModel($name = 'ActivityForm', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	protected function getReturnPage ()
	{
		$return = $this->input->get('return', null, 'base64');
		
		if (empty($return) || ! JUri::isInternal(base64_decode($return)))
		{
			return JRoute::_('index.php?option=com_cjforum&view=activities');
		}
		else
		{
			return base64_decode($return);
		}
	}
	
	public function delete()
	{
		parent::delete();
		$this->setRedirect($this->getReturnPage());
	}
	
	public function publish()
	{
		parent::publish();
		$this->setRedirect($this->getReturnPage());
	}
	
	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
	}
}
