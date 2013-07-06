<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/helper/component.php';

/**
 * View for the component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigViewApplicationJson extends JViewCms
{

	public $state;

	public $data;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Layout
	 *
	 * @return  string
	 */
	public function render()
	{

		$this->data = $this->model->getData();

		try
		{
			$user = JFactory::getUser();
			$app = JFactory::getApplication();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		// Required data
		$requiredData = array("sitename" => null,
				"offline" => null,
				"access" => null,
				"list_limit" => null,
				"MetaDesc" => null,
				"MetaKeys" => null,
				"MetaRights" => null,
				"sef" => null,
				"sitename_pagetitles" => null,
				"debug" => null,
				"debug_lang" =>null,
				"error_reporting" => null,
				"mailfrom" => null,
				"fromname" => null
		);

		$this->data = array_intersect_key($this->data,$requiredData);

		return json_encode($this->data);
	}

}
