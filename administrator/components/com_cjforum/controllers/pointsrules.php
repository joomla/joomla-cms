<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerPointsrules extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFORUM';
	
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	public function getModel ($name = 'Pointsrule', $prefix = 'CjForumModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}

	protected function postDeleteHook (JModelLegacy $model, $ids = null)
	{
	}
	
	public function scan()
	{
		CjForumHelper::scanRules();
		$this->setRedirect(JRoute::_('index.php?option=com_cjforum&view=pointsrules', false), JText::_('COM_CJFORUM_OPERATION_SUCCESSFULLY_COMPLETED'));
	}
}