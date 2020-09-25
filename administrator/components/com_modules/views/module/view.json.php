<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a module.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       3.2
 */
class ModulesViewModule extends JViewLegacy
{
	protected $item;

	protected $form;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		try
		{
			$this->item = $this->get('Item');
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$paramsList = $this->item->getProperties();

		unset($paramsList['xml']);

		$paramsList = json_encode($paramsList);

		return $paramsList;

	}
}
