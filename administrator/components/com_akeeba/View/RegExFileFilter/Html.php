<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\RegExFileFilter;

// Protect from unauthorized access
use Akeeba\Backup\Admin\Model\RegExFileFilters;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;

defined('_JEXEC') || die();

class Html extends \FOF30\View\DataView\Html
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
		$this->container->template->addJS('media://com_akeeba/js/RegExFileFilter.min.js', true, false, $this->container->mediaVersion);

		/** @var RegExFileFilters $model */
		$model = $this->getModel();

		// Get a JSON representation of the available roots
		$filters   = Factory::getFilters();
		$root_info = $filters->getInclusions('dir');
		$roots     = array();
		$options   = array();

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
		$site_root         = $roots[0];
		$this->root_select = JHtml::_('select.genericlist', $options, 'root', [
			'list.select' => $site_root,
			'id'          => 'active_root',
		]);
		$this->roots       = $roots;

		// Pass script options
		$platform = $this->container->platform;
		$platform->addScriptOptions('akeeba.System.params.AjaxURL', JUri::base() . 'index.php?option=com_akeeba&view=RegExFileFilters&task=ajax');
		$platform->addScriptOptions('akeeba.RegExFileFilter.guiData', $model->get_regex_filters($site_root));

		$this->getProfileIdAndName();

		// Push translations
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_FILES');

	}
}
