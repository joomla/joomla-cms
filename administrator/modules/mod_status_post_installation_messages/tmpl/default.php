<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status_post_installation_messages
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

		<?php if ($user->authorise('core.manage', 'com_postinstall')) : ?>
			<div class="header-element-content dropdown d-flex">
				<button class="dropdown-toggle d-flex flex-column align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button"
					title="<?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>">
                    <div class="d-flex align-items-end mx-auto">
                        <span class="fa fa-bell" aria-hidden="true"></span>
                    </div>
                    <div class="d-flex align-items-center tiny">
					    <?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>
                    </div>
					<span class="fa fa-angle-down" aria-hidden="true"></span>
					<?php if (count($messages) > 0) : ?>
						<span class="badge badge-pill badge-danger"><?php echo count($messages); ?></span>
					<?php endif; ?>
				</button>
				<?php if (!$hideLinks) : ?>
				<div class="dropdown-menu dropdown-menu-right dropdown-notifications border-0">
					<div class="dropdown-header">
						<?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>
					</div>
					<?php if (empty($messages)) : ?>
						<div class="dropdown-item">
							<a href="<?php echo Route::_('index.php?option=com_postinstall&eid=' . $joomlaFilesExtensionId); ?>">
								<?php echo Text::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?>
							</a>
						</div>
					<?php endif; ?>
					<?php foreach ($messages as $message) : ?>
						<div class="dropdown-item">
							<?php $route = 'index.php?option=com_postinstall&amp;eid=' . $joomlaFilesExtensionId; ?>
							<?php $title = Text::_($message->title_key); ?>
							<a href="<?php echo Route::_($route); ?>" title="<?php echo $title; ?>">
								<?php echo $title; ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
