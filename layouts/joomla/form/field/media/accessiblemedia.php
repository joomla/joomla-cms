<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

$form = $forms[0];

$formfields = $form->getGroup('');
$field->disabled = $disabled;
?>

<div class="subform-wrapper">
<?php foreach ($formfields as $field) : ?>
    <?php $field->disabled = $disabled; ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
