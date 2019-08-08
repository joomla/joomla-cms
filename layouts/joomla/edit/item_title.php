<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @deprecated  3.2
 */

defined('JPATH_BASE') or die;
$title = $displayData->getForm()->getValue('title');
$name = $displayData->getForm()->getValue('name');

?>

<?php if ($title) : ?>
	<h2><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
<?php endif; ?>

<?php if ($name) : ?>
	<h2><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h2>
<?php endif;
