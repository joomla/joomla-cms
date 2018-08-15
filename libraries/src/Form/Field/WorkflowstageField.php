<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Form\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Workflow Stages field.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowstageField extends GroupedlistField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Workflowstage';

	/**
	 * The extension where we're
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension = 'com_content';

	/**
	 * Show only the stages which has an item attached
	 *
	 * @var     boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $activeonly = false;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$success = parent::setup($element, $value, $group);

		if ($success)
		{
			if (strlen($element['extension']))
			{
				$this->extension =  (string) $element['extension'];
			}

			if ((string) $element['activeonly'] == '1' || (string) $element['activeonly'] == 'true')
			{
				$this->activeonly =  true;
			}
		}

		return $success;
	}

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException
	 */
	protected function getGroups()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Select distinct stages for existing articles
		$query
				->select('DISTINCT ' . $db->quoteName('ws.id', 'workflow_stage_id'))
				->select($db->quoteName(['ws.title', 'w.title', 'w.id', 'w.ordering'], ['workflow_stage_title', 'workflow_title', 'workflow_id', 'ordering']))
				->from($db->quoteName('#__workflow_stages', 'ws'))
				->from($db->quoteName('#__workflows', 'w'))
				->where($db->quoteName('ws.workflow_id') . ' = ' . $db->quoteName('w.id'))
				->where($db->quoteName('w.extension') . ' = ' . $db->quote($this->extension))
				->order($db->quoteName('w.ordering'));

		if ($this->activeonly)
		{
			$query
					->from($db->quoteName('#__workflow_associations', 'wa'))
					->where($db->quoteName('wa.stage_id') . ' = ' . $db->quoteName('ws.id'))
					->where($db->quoteName('wa.extension') . ' = ' . $db->quote($this->extension));

		}

		$stages = $db->setQuery($query)->loadObjectList();

		$workflowStages = array();

		// Grouping the stages by workflow
		foreach ($stages as $stage)
		{
			// Using workflow ID to differentiate workflows having same title
			$workflowStageKey = $stage->workflow_title . ' (' . $stage->workflow_id . ')';

			if (!array_key_exists($workflowStageKey, $workflowStages))
			{
				$workflowStages[$workflowStageKey] = array();
			}

			$workflowStages[$workflowStageKey][] = HTMLHelper::_('select.option', $stage->workflow_stage_id, $stage->workflow_stage_title);
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getGroups(), $workflowStages);
	}
}
