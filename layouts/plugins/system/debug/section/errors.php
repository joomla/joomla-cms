<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$errors = $displayData['errors'];

?>

<ol>
	<?php foreach ($errors as $error) : ?>
		<?php $col = (E_WARNING == $error->get('level')) ? 'red' : 'orange'; ?>
		<li>
			<b style="color: <?php echo $col; ?>"><?php echo $error->getMessage(); ?></b><br>
			<?php if ($info = $error->get('info')) : ?>
				<pre><?php print_r($info); ?></pre><br>
			<?php endif; ?>
			<?php echo JLayoutHelper::render('plugins.system.debug.backtrace', array('backtrace' => $error->getTrace())); ?>
		</li>
	<?php endforeach; ?>
</ol>
