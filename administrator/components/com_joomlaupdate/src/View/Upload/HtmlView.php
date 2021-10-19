<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\View\Upload;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Joomla! Update's Update View
 *
 * @since  3.6.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array with the Joomla! update information.
	 *
	 * @var    array
	 *
	 * @since  4.0.0
	 */
	protected $updateInfo = null;

	/**
	 * Flag if the update component itself has to be updated
	 *
	 * @var boolean  True when update is available otherwise false
	 *
	 * @since 4.0.0
	 */
	protected $selfUpdateAvailable = false;

	/**
	 * Warnings for the upload update
	 *
	 * @var array  An array of warnings which could prevent the upload update
	 *
	 * @since 4.0.0
	 */
	protected $warnings = [];

	/**
	 * Renders the view.
	 *
	 * @param   string  $tpl  Template name.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function display($tpl = null)
	{
		// Load com_installer's language
		$language = Factory::getLanguage();
		$language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
		$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

		$this->updateInfo = $this->get('UpdateInformation');
		$this->selfUpdateAvailable = $this->get('CheckForSelfUpdate');

		if ($this->getLayout() !== 'captive')
		{
			$this->warnings = $this->get('Items', 'warnings');
		}

		$this->addToolbar();

		// Render the view.
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function addToolbar()
	{
		// Set the toolbar information.
		ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_OVERVIEW'), 'sync install');

		$arrow = Factory::getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';
		ToolbarHelper::link('index.php?option=com_joomlaupdate&' . ($this->getLayout() == 'captive' ? 'view=upload' : ''), 'JTOOLBAR_BACK', $arrow);
		ToolbarHelper::divider();
		ToolbarHelper::help('Joomla_Update');
	}
}
