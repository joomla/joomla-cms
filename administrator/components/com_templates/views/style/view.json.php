<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a template style.
 *
 * @since  1.6
 */
class TemplatesViewStyle extends JViewLegacy
{
	/**
	 * The JObject (on success, false on failure)
	 *
	 * @var   JObject
	 */
	protected $item;

	/**
	 * The form object
	 *
	 * @var   JForm
	 */
	protected $form;

	/**
	 * The model state
	 *
	 * @var   JObject
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		try
		{
			$this->item		= $this->get('Item');
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$paramsList = $this->item->getProperties();

		unset($paramsList['xml']);

		$paramsList = json_encode($paramsList);

		return $paramsList;

	}

}
