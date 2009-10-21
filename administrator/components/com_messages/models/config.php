<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.model');

/**
 * Messages Component Config Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesModelConfig extends JModel
{
	/**
	 * The id of the current user.
	 *
	 * @var int
	 */
	public $user_id;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$user			= &JFactory::getUser();
		$this->user_id	= (int) $user->get('id');
	}

	public function getVars()
	{
		$query = 'SELECT cfg_name, cfg_value'
		. ' FROM #__messages_cfg'
		. ' WHERE user_id = '. $this->user_id
		;
		$this->_db->setQuery($query);
		$data = $this->_db->loadObjectList('cfg_name');

		// initialize values if they do not exist
		if (!isset($data['lock']->cfg_value)) {
			$data['lock']->cfg_value 		= 0;
		}
		if (!isset($data['mail_on_new']->cfg_value)) {
			$data['mail_on_new']->cfg_value = 0;
		}
		if (!isset($data['auto_purge']->cfg_value)) {
			$data['auto_purge']->cfg_value 	= 7;
		}

		$vars 					= array();
		$vars['lock'] 			= JHtml::_('select.booleanlist',  "vars[lock]", '', $data['lock']->cfg_value, 'yes', 'no', 'varslock');
		$vars['mail_on_new'] 	= JHtml::_('select.booleanlist',  "vars[mail_on_new]", '', $data['mail_on_new']->cfg_value, 'yes', 'no', 'varsmail_on_new');
		$vars['auto_purge'] 	= $data['auto_purge']->cfg_value;

		return $vars;
	}

	public function save($data)
	{
		$user =& JFactory::getUser();

		$query = 'DELETE FROM #__messages_cfg'
		. ' WHERE user_id = '. $this->user_id
		;
		$this->_db->setQuery($query);
		$this->_db->query();

		foreach ($data as $k=>$v) {
			$v = $this->_db->getEscaped($v);
			$query = 'INSERT INTO #__messages_cfg'
			. ' (user_id, cfg_name, cfg_value)'
			. ' VALUES ('.(int) $user->get('id').', '.$this->_db->Quote($k).', '.$this->_db->Quote($v).')'
			;
			$this->_db->setQuery($query);
			$this->_db->query();
		}
	}
}