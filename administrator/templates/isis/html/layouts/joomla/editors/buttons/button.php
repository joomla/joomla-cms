<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$button = $displayData;

?>
<?php if ($button->get('name')) : ?>
	<?php
		$class    = ($button->get('class')) ? $button->get('class') : null;
		$class   .= ($button->get('modal')) ? ' modal-button' : null;
		$href     = '#' . str_replace(' ', '', $button->get('text')) . 'Modal';
		$link     = ($button->get('link')) ? JUri::base() . $button->get('link') : null;
		$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
		$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');

	?>
	<a href="<?php echo $href; ?>" role="button" class="<?php echo $class; ?>" data-toggle="modal" title="<?php echo $title; ?>" <?php echo $onclick; ?>>
		<span class="icon-<?php echo $button->get('name'); ?>"></span> <?php echo $button->get('text'); ?>
	</a>
<?php endif;
