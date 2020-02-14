<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$button = $displayData;

if ($button->get('name')) :
	$class   = 'btn btn-secondary';
	$class  .= ($button->get('class')) ? ' ' . $button->get('class') : null;
	$class  .= ($button->get('modal')) ? ' modal-button' : null;
	$href    = '#' . str_replace(' ', '', $button->get('text')) . 'Modal';
	$link    = ($button->get('link')) ? JUri::base() . $button->get('link') : null;
	$onclick = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
	$title   = ($button->get('title')) ? $button->get('title') : $button->get('text');
?>
<a href="<?php echo $href; ?>" role="button" class="<?php echo $class; ?>" <?php echo $button->get('modal') ? 'data-toggle="modal"' : '' ?> title="<?php echo $title; ?>" <?php echo $onclick; ?>>
	<span class="icon-<?php echo $button->get('name'); ?>" aria-hidden="true"></span> <?php echo $button->get('text'); ?>
</a>
<?php endif; ?>
