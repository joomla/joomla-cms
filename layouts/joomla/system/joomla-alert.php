<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<joomla-alert type="<?php echo $alertType ?? $type; ?>" dismiss="true">
	<?php if (!empty($msgs)) : ?>
		<div class="alert-heading">
			<span class="<?php echo $type; ?>"></span>
			<span class="sr-only"><?php echo Text::_($type); ?></span>
		</div>
		<div class="alert-wrapper">
			<?php foreach ($msgs as $msg) : ?>
				<div class="alert-message"><?php echo $msg; ?></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</joomla-alert>
