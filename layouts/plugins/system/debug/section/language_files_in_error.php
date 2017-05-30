<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$errorfiles = $displayData['errors'];

?>
<?php if (empty($errorfiles)) : ?>
	<p><?php echo JText::_('JNONE'); ?></p>
<?php else : ?>
	<ul>
		<?php foreach ($errorfiles as $file => $error) : ?>
			<li><?php echo JDebugHelper::formatLink($file), str_replace($file, '', $error); ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
