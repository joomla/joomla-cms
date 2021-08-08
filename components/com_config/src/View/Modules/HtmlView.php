<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\View\Modules;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View to edit a module.
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The module to be rendered
	 *
	 * @var   array
	 * @since 3.2
	 */
	public $item;

	/**
	 * The form object
	 *
	 * @var   \JForm
	 * @since 3.2
	 */
	public $form;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('', JPATH_ADMINISTRATOR, $lang->getTag());
		$lang->load('com_modules', JPATH_ADMINISTRATOR, $lang->getTag());

		// TODO Move and clean up
		$module = (new \Joomla\Component\Modules\Administrator\Model\ModuleModel)->getItem(Factory::getApplication()->input->getInt('id'));

		$moduleData = $module->getProperties();
		unset($moduleData['xml']);

		/** @var Modules $model */
		$model = $this->getModel();

		// Need to add module name to the state of model
		$model->getState()->set('module.name', $moduleData['module']);

		/** @var \JForm form */
		$this->form      = $this->get('form');
		$this->positions = $this->get('positions');
		$this->item      = $moduleData;

		if ($this->form)
		{
			$this->form->bind($moduleData);
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function _prepareDocument()
	{
		// There is no menu item for this so we have to use the title from the component
		$this->setDocumentTitle(Text::_('COM_CONFIG_MODULES_SETTINGS_TITLE'));
	}
}
