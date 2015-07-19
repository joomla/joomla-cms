<?php
/**
 * @package     Joomla.Site
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
		$class	 .= ($button->get('modal')) ? ' modal-button' : null;
		$href     = ($button->get('link')) ? ' href="' . JUri::base() . $button->get('link') . '"' : null;
		$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : '';
		$title    = ($button->get('title')) ? $button->get('title') : $button->get('text');

	// Load modal popup behavior
	if ($button->get('modal'))
	{
		JHtml::_('behavior.modal', 'a.modal-button');
	}
	?>
	<a class="<?php echo $class; ?>" title="<?php echo $title; ?>" <?php echo $href, $onclick; ?> rel="<?php echo $button->get('options'); ?>">
		<span class="icon-<?php echo $button->get('name'); ?>"></span> <?php echo $button->get('text'); ?>
	</a>
<?php endif;
