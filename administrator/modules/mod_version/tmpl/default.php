<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="header-item-content">
	<div class="joomlaversion d-flex text-muted">
		<div class="d-flex align-items-end mx-auto">
			<span class="fab fa-joomla" aria-hidden="true"></span>
		</div>
		<div class="tiny mx-auto">
			<span class="sr-only"><?php echo Text::sprintf('MOD_VERSION_CURRENT_VERSION_TEXT', $version); ?></span>
			<span aria-hidden="true"><?php echo $version; ?></span>
		</div>
	</div>
</div>
