/**
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised!');
}

const setRequired = () => {
  document.querySelector('#jform_execution_rules_cron_expression_minutes-lbl').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_minutes').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_minutes').setAttribute('required', 'true');

  document.querySelector('#jform_execution_rules_cron_expression_hours-lbl').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_hours').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_hours').setAttribute('required', 'true');

  document.querySelector('#jform_execution_rules_cron_expression_days_month-lbl').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_month').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_month').setAttribute('required', 'true');

  document.querySelector('#jform_execution_rules_cron_expression_months-lbl').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_months').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_months').setAttribute('required', 'true');

  document.querySelector('#jform_execution_rules_cron_expression_days_week-lbl').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_week').classList.add('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_week').setAttribute('required', 'true');
};

const removeRequired = () => {
  document.querySelector('#jform_execution_rules_cron_expression_minutes-lbl').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_minutes').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_minutes').removeAttribute('required');

  document.querySelector('#jform_execution_rules_cron_expression_hours-lbl').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_hours').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_hours').removeAttribute('required');

  document.querySelector('#jform_execution_rules_cron_expression_days_month-lbl').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_month').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_month').removeAttribute('required');

  document.querySelector('#jform_execution_rules_cron_expression_months-lbl').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_months').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_months').removeAttribute('required');

  document.querySelector('#jform_execution_rules_cron_expression_days_week-lbl').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_week').classList.remove('required');
  document.querySelector('#jform_execution_rules_cron_expression_days_week').removeAttribute('required');
};

const onBoot = () => {
  if (document.querySelector('#jform_execution_rules_rule_type').value === 'cron-expression') {
    setRequired();
  } else {
    removeRequired();
  }

  document.querySelector('#jform_execution_rules_rule_type').addEventListener('change', (e) => {
    const selectValue = e.target.value;
    // When we select custom cron rules, we set the respective fields as required, otherwise we remove the attributes / classes
    // in order to be able when selecting other execution rules
    if (selectValue === 'cron-expression') {
      setRequired();
    } else {
      removeRequired();
    }
  });

  document.removeEventListener('DOMContentLoaded', onBoot);
};

document.addEventListener('DOMContentLoaded', onBoot);
