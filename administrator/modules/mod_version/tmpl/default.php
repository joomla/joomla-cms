<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="col-md-12">
	<div class="float-right mb-5 pb-5 mb-sm-0 pb-sm-0">
		<span class="sr-only"><?php echo Text::sprintf('MOD_VERSION_CURRENT_VERSION_TEXT', $version); ?></span>
		<span class="mb-5 pb-5 mb-sm-0 pb-sm-4" aria-hidden="true"><?php echo Text::sprintf('MOD_VERSION_CURRENT_VERSION_SHORTTEXT', $version); ?></span>
	</div>
</div>
