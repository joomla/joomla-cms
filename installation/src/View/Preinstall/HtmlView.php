<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View\Preinstall;

defined('_JEXEC') or die;

use Joomla\CMS\Installation\View\DefaultView;

/**
 * The HTML Joomla Core Install Preinstall View
 *
 * @since  3.1
 */
class HtmlView extends DefaultView
{
	/**
	 * Array of PHP config options.
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $options;

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
		$this->options  = $this->get('PhpOptions', 'Checks');

		return parent::display($tpl);
	}
}
