<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$button = $displayData;

?>
<?php if ($button->get('name')) : ?>
	<?php
		$class    = ($button->get('class')) ? $button->get('class') : null;
		$class	 .= ($button->get('modal')) ? ' modal-button' : null;
		$href     = ($button->get('link')) ? ' href="' . JUri::base() . $button->get('link') . '"' : null;
		$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : ' onclick="IeCursorFix(); return false;"';
		$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');
	?>
	<a class="<?php echo $class; ?>" title="<?php echo $title; ?>" <?php echo $href . $onclick; ?> rel="<?php echo $button->get('options'); ?>">
		<i class="icon-<?php echo $button->get('name'); ?>"></i> <?php echo $button->get('text'); ?>
	</a>
<?php endif;