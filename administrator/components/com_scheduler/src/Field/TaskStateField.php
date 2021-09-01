<?php
/**
 * @package         Joomla.Administrator
 * @subpackage      com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Declares a list field with all possible states for a task entry. */

namespace Joomla\Component\Scheduler\Administrator\Field;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\PredefinedlistField;

/**
 * A predefined list field with all possible states for a com_scheduler entry.
 *
 * @since  __DEPLOY_VERSION__
 */
class TaskStateField extends PredefinedlistField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type = 'taskState';

	/**
	 * Available states
	 *
	 * @var  string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $predefinedOptions = [
		-2 => 'JTRASHED',
		0 => 'JDISABLED',
		1 => 'JENABLED',
		'*' => 'JALL'
	];
}
