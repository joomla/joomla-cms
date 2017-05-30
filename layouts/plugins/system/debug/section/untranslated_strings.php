<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$guesses = $displayData['guesses'];

?>
<?php if (empty($guesses)) : ?>
	<p><?php echo JText::_('JNONE'); ?></p>
<?php else : ?>
<pre>
<?php foreach ($guesses as $file => $keys): ?>

<?php echo '#', ($file ? JDebugHelper::formatLink($file) : JText::_('PLG_DEBUG_UNKNOWN_FILE')); ?>


<?php echo implode("\n", $keys); ?>

<?php endforeach; ?>
</pre>
<?php endif; ?>
