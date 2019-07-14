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
use Joomla\CMS\Uri\Uri;

?>
<diV class="header-item-content">
	<div class="joomlaversion d-flex">
		<div class="d-flex align-items-end mx-auto">
			<span class="fab fa-joomla" aria-hidden="true"></span>
		</div>
		<div class="d-flex align-items-center tiny mx-auto">
			<?php echo $version; ?>
		</div>
	</div>
</diV>
