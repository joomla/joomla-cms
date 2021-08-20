<?php
/**
 * Declares the JobTypeField for listing all available job types.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Scheduler\Administrator\Cronjobs\TaskOption;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Utilities\ArrayHelper;
use function array_map;

/**
 * A list field with all available job types
 *
 * @since  __DEPLOY_VERSION__
 */
class JobTypeField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'jobType';

	/**
	 * Method to get field options
	 *
	 * @return array
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getOptions(): array
	{
		$options = parent::getOptions();

		// Get all available job types and sort by title
		$types = ArrayHelper::sortObjects(
			SchedulerHelper::getCronOptions()->options,
			'title', 1
		);

		// Closure to add a TaskOption as a <select> option in $options: array
		$addTypeAsOption = function (TaskOption $type) use (&$options) {
			$options[] = HTMLHelper::_('select.option', $type->type, $type->title);
		};

		// Call $addTypeAsOption on each type
		array_map($addTypeAsOption, $types);

		return $options;
	}
}
