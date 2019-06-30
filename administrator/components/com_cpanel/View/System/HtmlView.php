<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\View\System;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The list of links
	 *
	 * @var \Joomla\Component\Cpanel\Administrator\Entities\SystemHeader[]
	 */
	public $links = [];

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_CPANEL_TITLE_SYSTEM_PANEL'), 'cog help_header');
		ToolbarHelper::help('screen.cpanel');

		/** @var  \Joomla\Component\Cpanel\Administrator\Model\SystemModel $model */
		$model = $this->getModel();
		$this->links = $model->getItems();
		$hasItems = false;

		foreach ($this->links as $section)
		{
			if ($section->hasItems())
			{
				$hasItems = true;
				break;
			}
		}

		if (!$hasItems)
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		Factory::getLanguage()->load(
			'mod_menu',
			JPATH_ADMINISTRATOR,
			Factory::getLanguage()->getTag(),
			true
		);

		return parent::display($tpl);
	}
}
