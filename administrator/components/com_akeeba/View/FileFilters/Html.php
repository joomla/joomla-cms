<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\FileFilters;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\FileFilters;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;

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
	 * @return  void
	 */
	public function onBeforeMain()
	{
		$this->container->template->addJS('media://com_akeeba/js/FileFilters.min.js', true, false, $this->container->mediaVersion);

		/** @var FileFilters $model */
		$model = $this->getModel();

		// Add custom submenus
		$task    = $model->getState('browse_task', 'normal', 'cmd');
		$toolbar = $this->container->toolbar;

		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_NORMALVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=FileFilters&task=normal',
			($task == 'normal')
		);
		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TABULARVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=FileFilters&task=tabular',
			($task == 'tabular')
		);

		// Get a JSON representation of the available roots
		$filters   = Factory::getFilters();
		$root_info = $filters->getInclusions('dir');
		$roots     = [];
		$options   = [];

		if (!empty($root_info))
		{
			// Loop all dir definitions
			foreach ($root_info as $dir_definition)
			{
				if (is_null($dir_definition[1]))
				{
					// Site root definition has a null element 1. It is always pushed on top of the stack.
					array_unshift($roots, $dir_definition[0]);
				}
				else
				{
					$roots[] = $dir_definition[0];
				}

				$options[] = JHtml::_('select.option', $dir_definition[0], $dir_definition[0]);
			}
		}

		$siteRoot      = $roots[0];
		$selectOptions = [
			'list.select' => $siteRoot,
			'id'          => 'active_root',
		];

		$this->root_select = JHtml::_('select.genericlist', $options, 'root', $selectOptions);
		$this->roots       = $roots;
		$platform          = $this->container->platform;

		// Add script options
		$platform->addScriptOptions('akeeba.System.params.AjaxURL', 'index.php?option=com_akeeba&view=FileFilters&task=ajax');
		$platform->addScriptOptions('akeeba.Fsfilters.loadingGif', $this->container->template->parsePath('media://com_akeeba/icons/loading.gif'));

		switch ($task)
		{
			case 'normal':
			default:
				$this->setLayout('default');

				// Get a JSON representation of the directory data
				$platform->addScriptOptions('akeeba.FileFilters.guiData', $model->make_listing($siteRoot, [], ''));
				$platform->addScriptOptions('akeeba.FileFilters.viewType', "list");

				break;

			case 'tabular':
				$this->setLayout('tabular');

				// Get a JSON representation of the tabular filter data
				$platform->addScriptOptions('akeeba.FileFilters.guiData', $model->get_filters($siteRoot));
				$platform->addScriptOptions('akeeba.FileFilters.viewType', "tabular");

				break;
		}

		// Push translations
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_FILES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_FILES_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_APPLYTOALLDIRS');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_APPLYTOALLFILES');

		$this->getProfileIdAndName();
	}

}
