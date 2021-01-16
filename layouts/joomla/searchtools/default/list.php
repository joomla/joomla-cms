<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$data = $displayData;

// Load the form list fields
$list = $data['view']->filterForm->getGroup('list');
?>
<?php if ($list) : ?>
	<div class="ordering-select">
		<?php foreach ($list as $fieldName => $field) : ?>
			<div class="js-stools-field-list">
				<span class="visually-hidden"><?php echo $field->label; ?></span>
				<?php echo $field->input; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
