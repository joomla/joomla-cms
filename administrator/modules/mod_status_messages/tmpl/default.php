<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status_messages
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
			<a class="d-flex align-items-stretch <?php echo ($hideLinks ? 'disabled' : 'dropdown-toggle'); ?>" <?php echo ($hideLinks ? '' : 'href="' . Route::_('index.php?option=com_messages') . '"'); ?> title="<?php echo Text::_('MOD_STATUS_PRIVATE_MESSAGES'); ?>">
                <div class="d-flex align-items-end mx-auto">
                    <span class="fa fa-envelope" aria-hidden="true"></span>
                </div>
                <div class="d-flex align-items-center tiny">
                   <?php echo Text::_('MOD_STATUS_MESSAGES_PRIVATE_MESSAGES'); ?>
                </div>
				<?php $countUnread = $app->getSession()->get('messages.unread'); ?>
				<?php if ($countUnread > 0) : ?>
					<span class="badge badge-pill badge-danger"><?php echo $countUnread; ?></span>
				<?php endif; ?>
			</a>
		</div>
