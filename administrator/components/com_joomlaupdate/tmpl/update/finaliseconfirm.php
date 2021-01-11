<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Login\Administrator\Model\LoginModel;
use Joomla\Registry\Registry;

$loginmodule = LoginModel::getLoginModule('mod_login');
$loginmodule->params = new Registry($loginmodule->params);
$loginmodule->params->set('layout', 'update');
$loginmodule->params->set('task', 'update.finaliseconfirm');

?>
<div id="system-message-container" aria-live="polite">
	<joomla-alert type="warning" class="joomla-alert--show">
		<div class="alert-heading"><span class="warning"></span><span class="sr-only"><?php echo Text::_('WARNING'); ?></span></div>
		<div class="alert-wrapper">
			<div class="alert-message">
				<h2>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_HEAD'); ?>
				</h2>
				<p>
					<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_HEAD_DESC', Factory::getApplication()->get('sitename')); ?>
				</p>
			</div>
		</div>
	</joomla-alert>
</div>
<section class="view-login">
	<div class="login mx-auto">
		<?php echo ModuleHelper::renderModule($loginmodule, ['id' => 'section-box']); ?>
	</div>
</section>
