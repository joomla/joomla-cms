<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Rule;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Form\Rule\OptionsRule;
use Joomla\Registry\Registry;

/**
 * The ExecutionRulesRule Class.
 * Validates execution rules, with input for other fields as context.
 *
 * @since  4.1.0
 */
class ExecutionRulesRule extends FormRule
{
	/**
	 * @var string  RULE_TYPE_FIELD   The field containing the rule type to test against
	 * @since  4.1.0
	 */
	private const RULE_TYPE_FIELD = "execution_rules.rule-type";

	/**
	 * @var string CUSTOM_RULE_GROUP  The field group containing custom execution rules
	 * @since  4.1.0
	 */
	private const CUSTOM_RULE_GROUP = "execution_rules.custom";

	/**
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
	 *                                       field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   ?string            $group    The field name group control value. This acts as an array container for the
	 *                                       field. For example if the field has `name="foo"` and the group value is set
	 *                                       to "bar" then the full field name would end up being "bar[foo]".
	 * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against
	 *                                       the entire form.
	 * @param   ?Form              $form     The form object for which the field is being tested.
	 *
	 * @return boolean
	 *
	 * @since  4.1.0
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null): bool
	{
		$fieldName = (string) $element['name'];
		$ruleType  = $input->get(self::RULE_TYPE_FIELD);

		if ($ruleType === $fieldName || ($ruleType === 'custom' && $group === self::CUSTOM_RULE_GROUP))
		{
			return $this->validateField($element, $value, $group, $form);
		}

		return true;
	}

	/**
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement for the field.
	 * @param   mixed              $value    The field value.
	 * @param   ?string            $group    The form field group the element belongs to.
	 * @param   Form|null          $form     The Form object against which the field is tested/
	 *
	 * @return boolean  True if field is valid
	 *
	 * @since  4.1.0
	 */
	private function validateField(\SimpleXMLElement $element, $value, ?string $group = null, ?Form $form = null): bool
	{
		$elementType = (string) $element['type'];

		// If element is of cron type, we test against options and return
		if ($elementType === 'cron')
		{
			return (new OptionsRule)->test($element, $value, $group, null, $form);
		}

		// Test for a positive integer value and return
		return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
	}
}
