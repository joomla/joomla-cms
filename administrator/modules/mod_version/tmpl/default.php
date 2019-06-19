<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\Multilangstatus\Administrator\Helper\MultilangstatusAdminHelper;

?>
<?php if (!empty($version)) :

	$versionParts = explode("-", $version);
	$versionNumber = $versionParts[0];
	$versionName   = $versionParts[1]; ?>
<diV class="header-item-content">
    <div class="joomlaversion d-flex">
        <div class="d-flex align-items-end mx-auto">
            <span class="fab fa-joomla" aria-hidden="true"></span>
        </div>
        <div class="d-flex align-items-center tiny mx-auto">
	        <?php echo Text::_('MOD_VERSION_JOOMLA'); ?>
        </div>
        <span class="badge badge-pill badge-success"><?php echo $versionNumber; ?></span>
    </div>
</diV>
<?php endif; ?>
