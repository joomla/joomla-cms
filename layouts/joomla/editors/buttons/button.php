<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Uri\Uri;

$button = $displayData;

if ($button->get('name')) :
	$class   = 'btn btn-secondary';
	$class  .= ($button->get('class')) ? ' ' . $button->get('class') : null;
	$class  .= ($button->get('modal')) ? ' modal-button' : null;
	$href    = '#' . str_replace(' ', '', $button->get('text')) . 'Modal';
	$link    = ($button->get('link')) ? Uri::base() . $button->get('link') : null;
	$onclick = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
	$title   = ($button->get('title')) ? $button->get('title') : $button->get('text');
?>
<a href="<?php echo $href; ?>" role="button" class="<?php echo $class; ?>" <?php echo $button->get('modal') ? 'data-toggle="modal"' : '' ?> title="<?php echo $title; ?>" <?php echo $onclick; ?>>
	<span class="icon-<?php echo $button->get('name'); ?>" aria-hidden="true"></span>
	<?php echo $button->get('text'); ?>
</a>
<?php endif; ?>
