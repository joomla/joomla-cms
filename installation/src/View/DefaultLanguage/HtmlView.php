<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View\DefaultLanguage;

defined('_JEXEC') or die;

use Joomla\CMS\Installation\View\DefaultView;

/**
 * The Installation Default Language View
 *
 * @since  3.1
 */
class HtmlView extends DefaultView
{
	/**
	 * Container with all installed languages
	 *
	 * @var    array
	 * @since  3.1
	 */
	public $items;

	/**
	 * The default model
	 *
	 * @var	   string
	 * @since  3.0
	 */
	protected $_defaultModel = 'languages';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		$this->items                = new \stdClass;
		$this->items->administrator = $this->get('InstalledlangsAdministrator');
		$this->items->frontend      = $this->get('InstalledlangsFrontend');

		return parent::display($tpl);
	}
}
