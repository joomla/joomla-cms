<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

?>
<div class="com-users-users registration">
	<?php //if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1><?php //echo $this->escape($this->params->get('page_heading')); ?></h1>
        </div>
	<?php //endif; ?>

	<?php if ($this->items): ?>

		<?php foreach ($this->items as $item): ?>

			<?php echo $item->name; ?><br>

		<?php endforeach; ?>

	<?php endif; ?>

</div>
