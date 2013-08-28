<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$form = $displayData->get('form');

$fields = $displayData->get('fields');
$fields = !empty($fields) ? $fields : array(
	array('category', 'catid'),
	array('parent', 'parent_id'),
	'tags',
	array('published', 'state'),
	'featured',
	'access',
	'language'
);

$hidden_fields = $displayData->get('hidden_fields');
$hidden_fields = !empty($hidden_fields) ? $hidden_fields : array();

if (!isset($app->languages_enabled))
{
	$hidden[] = 'language';
}

?>
<fieldset class="form-vertical">
	<?php foreach ($fields as $field) : ?>
		<?php $field = is_array($field) ? $field : array($field); ?>
		<?php foreach ($field as $f) : ?>
			<?php if ($form->getField($f)) : ?>
				<?php if (in_array($f, $hidden_fields)) : ?>
					<input type="hidden" name="language" value="<?php echo $form->getValue($f); ?>" />
				<?php else : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $form->getLabel($f); ?>
						</div>
						<div class="controls">
							<?php echo $form->getInput($f); ?>
						</div>
					</div>
				<?php endif; ?>
				<?php break; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endforeach; ?>
</fieldset>
