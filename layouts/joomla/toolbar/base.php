<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * @var  string  $action
 * @var  bool    $hasChildren
 * @var  string  $children
 * @var  array   $options
 */
extract($displayData, EXTR_OVERWRITE);
?>

<?php if ($hasChildren): ?>
	<div class="btn-group group-<?php echo $options['name'] ?? ''; ?>" role="group" aria-label="Button Dropdown">
		<?php echo $action; ?>

		<button type="button" class="<?php echo $options['caretClass'] ?? ''; ?> dropdown-toggle dropdown-toggle-split"
			data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="sr-only">Toggle Dropdown</span>
		</button>

		<div class="dropdown-menu dropdown-menu-right">
			<?php echo $children; ?>
		</div>
	</div>
<?php else: ?>
	<?php echo $action; ?>
<?php endif; ?>
