<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.Isis
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!empty($title)) : ?>
	<h1 class="page-title">
		<?php echo JHtml::_('string.truncate', $title, 0, false, false);?>
	</h1>
<?php endif; ?>
