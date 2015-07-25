<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm  $form - Form for render section
 * @var string $basegroup - base group name
 * @var string $group - current group name
 * @var array  $buttons Buttons that will be enabled
 */
extract($displayData);

?>

<div class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<div class="btn-toolbar text-right">
		<div class="btn-group">
			<?php if(!empty($buttons['add'])):?><a class="group-add btn btn-mini button btn-success"><span class="icon-plus"></span> </a><?php endif;?>
			<?php if(!empty($buttons['remove'])):?><a class="group-remove btn btn-mini button btn-danger"><span class="icon-minus"></span> </a><?php endif;?>
			<?php if(!empty($buttons['move'])):?><a class="group-move btn btn-mini button btn-primary"><span class="icon-move"></span> </a><?php endif;?>
		</div>
	</div>
<?php foreach($form->getGroup(false) as $field): ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
