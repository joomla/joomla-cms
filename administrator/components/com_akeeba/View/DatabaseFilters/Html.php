<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\DatabaseFilters;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\DatabaseFilters;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;

/**
 * View for database table exclusion
 */
class Html extends BaseView
{
	use ProfileIdAndName;

	/**
	 * SELECT element for choosing a database root
	 *
	 * @var  string
	 */
	public $root_select = '';

	/**
	 * List of database roots
	 *
	 * @var  array
	 */
	public $roots = [];

	/**
	 * Main page
	 */
	public function onBeforeMain()
	{
		// Load Javascript files
		$this->container->template->addJS('media://com_akeeba/js/FileFilters.min.js', true, false, $this->container->mediaVersion);
		$this->container->template->addJS('media://com_akeeba/js/DatabaseFilters.min.js', true, false, $this->container->mediaVersion);

		/** @var DatabaseFilters $model */
		$model = $this->getModel();

		// Add custom submenus
		$task    = $model->getState('browse_task', 'normal', 'cmd');
		$toolbar = $this->container->toolbar;

		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_NORMALVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=DatabaseFilters&task=normal',
			($task == 'normal')
		);
		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TABULARVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=DatabaseFilters&task=tabular',
			($task == 'tabular')
		);

		// Get a JSON representation of the available roots
		$root_info = $model->get_roots();
		$roots     = [];
		$options   = [];

		if (!empty($root_info))
		{
			// Loop all dir definitions
			foreach ($root_info as $def)
			{
				$roots[]   = $def->value;
				$options[] = JHtml::_('select.option', $def->value, $def->text);
			}
		}

		$siteRoot          = '[SITEDB]';
		$selectOptions     = [
			'list.select' => $siteRoot,
			'id'          => 'active_root',
		];
		$this->root_select = JHtml::_('select.genericlist', $options, 'root', $selectOptions);
		$this->roots       = $roots;
		$platform          = $this->container->platform;

		// Add script options
		$platform->addScriptOptions('akeeba.System.params.AjaxURL', 'index.php?option=com_akeeba&view=DatabaseFilters&task=ajax');

		switch ($task)
		{
			case 'normal':
			default:
				$this->setLayout('default');

				// Get the database entities GUI data
				$platform->addScriptOptions('akeeba.DatabaseFilters.guiData', $model->make_listing($siteRoot));
				$platform->addScriptOptions('akeeba.DatabaseFilters.viewType', 'list');

				break;

			case 'tabular':
				$this->setLayout('tabular');

				// Get the filter data for tabular display
				$platform->addScriptOptions('akeeba.DatabaseFilters.guiData', $model->get_filters($siteRoot));
				$platform->addScriptOptions('akeeba.DatabaseFilters.viewType', 'tabular');


				break;
		}

		// Translations
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		JText::script('COM_AKEEBA_DBFILTER_TYPE_TABLES');
		JText::script('COM_AKEEBA_DBFILTER_TYPE_TABLEDATA');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_MISC');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_TABLE');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_VIEW');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_PROCEDURE');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_FUNCTION');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_TRIGGER');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_META_ROWCOUNT');

		$this->getProfileIdAndName();
	}
}
