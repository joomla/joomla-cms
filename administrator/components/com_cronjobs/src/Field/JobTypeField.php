<?php
/**
 * Declares the JobTypeField for listing all available job types.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Field;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Cronjobs\Administrator\Cronjobs\CronOption;
use Joomla\Component\Cronjobs\Administrator\Helper\CronjobsHelper;
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
			CronjobsHelper::getCronOptions()->options,
			'title', 1
		);

		// Closure to add a CronOption as a <select> option in $options: array
		$addTypeAsOption = function (CronOption $type) use (&$options) {
			$options[] = HTMLHelper::_('select.option', $type->type, $type->title);
		};

		// Call $addTypeAsOption on each type
		array_map($addTypeAsOption, $types);

		return $options;
	}
}
