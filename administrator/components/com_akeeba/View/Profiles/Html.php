<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Profiles;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;

/**
 * View controller for the profiles management page
 */
class Html extends BaseView
{
	use ProfileIdAndName;

	/**
	 * Sorting order fields
	 *
	 * @var  array
	 */
	public $sortFields;

	/**
	 * The default layout, shows a list of profiles
	 */
	function onBeforeBrowse()
	{
		$this->getProfileIdAndName();

		// Get Sort By fields
		$this->sortFields = [
			'id'          => JText::_('JGRID_HEADING_ID'),
			'description' => JText::_('COM_AKEEBA_PROFILES_COLLABEL_DESCRIPTION'),
		];

		parent::onBeforeBrowse();

		JHtml::_('behavior.multiselect');
		JHtml::_('dropdown.init');
	}

	/**
	 * The edit layout, editing a profile's name
	 */
	protected function onBeforeEdit()
	{
		parent::onBeforeEdit();

		// Include tooltip support
		JHtml::_('behavior.tooltip');
	}
}
