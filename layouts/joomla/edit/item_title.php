<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$title = $displayData->get('form')->getValue('title');
$name = $displayData->get('form')->getValue('name');

?>

<?php if ($title) : ?>
	<h4><?php echo $title; ?></h4>
<?php endif; ?>

<?php if ($name) : ?>
	<h4><?php echo $name; ?></h4>
<?php endif;