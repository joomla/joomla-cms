<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status_frontend
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

HTMLHelper::_('bootstrap.framework');

$hideLinks = $app->input->getBool('hidemainmenu');
?>

<div class="header-element-content">
    <a class="footer-mobil-icon d-flex" href="<?php echo Uri::root(); ?>"
        title="<?php echo Text::sprintf('MOD_STATUS_PREVIEW', $sitename); ?>"
        target="_blank">
        <div class="d-flex align-items-end mx-auto">
            <span class="fa fa-external-link-alt" aria-hidden="true"></span>
        </div>
        <div class="d-flex align-items-center tiny">
            <?php echo HTMLHelper::_('string.truncate', $sitename, 28, false, false); ?>
        </div>
    </a>
</div>