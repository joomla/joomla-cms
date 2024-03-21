/**
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised!');
}

const ruleTypesElement = document.querySelector('#jform_execution_rules_rule_type');

const elements = [
  '#jform_execution_rules_cron_expression_minutes',
  '#jform_execution_rules_cron_expression_hours',
  '#jform_execution_rules_cron_expression_days_month',
  '#jform_execution_rules_cron_expression_months',
  '#jform_execution_rules_cron_expression_days_week'
];

function toggleRequired(process = 'add') {
  if (!['add', 'remove'].contains(process)) {
    return;
  }

  elements.forEach((elementSelector) => {
    const element = document.querySelector(elementSelector);
    if (!element) {
      return;
    }
    const label = document.querySelector(`${elementSelector}-lbl`);
    if (process === 'add') {
      element.setAttribute('required', '');
      element.classList.add('required');
      if (label) {
        label.classList.add('required');
      }
    } else {
      element.removeAttribute('required');
      element.classList.remove('required');
      if (label) {
        label.classList.remove('required');
      }
    }

  });
}

const handleChange = (event) => event.target.value === 'cron-expression' ? toggleRequired('add') : toggleRequired('remove');

if (ruleTypesElement) {
  handleChange({target: ruleTypesElement});

  ruleTypesElement.addEventListener('change', handleChange);
}
