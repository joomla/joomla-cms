<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\View\Methods\HtmlView;

// phpcs:ignoreFile
/** @var HtmlView $this */
?>
<div id="com-users-methods-list">
	<div id="com-users-methods-reset-container">
		<div id="com-users-methods-reset-message">
			<?php echo Text::sprintf('COM_USERS_TFA_LIST_STATUS', Text::_('COM_USERS_TFA_LIST_STATUS_' . ($this->tfaActive ? 'ON' : 'OFF'))) ?>
		</div>
		<?php if ($this->tfaActive): ?>
		<div>
			<a href="<?php echo Route::_('index.php?option=com_users&task=methods.disable&' . Factory::getApplication()->getFormToken() . '=1' . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id) ?>"
			   class="btn btn-danger btn-sm">
				<?php echo Text::_('COM_USERS_TFA_LIST_REMOVEALL'); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>

	<?php if (!$this->isAdmin): ?>
	<h3 id="com-users-methods-list-head">
		<?php echo Text::_('COM_USERS_TFA_LIST_PAGE_HEAD'); ?>
	</h3>
	<?php endif; ?>
	<div id="com-users-methods-list-instructions" class="alert alert-info">
		<span class="icon icon-info-circle"></span>
		<?php echo Text::_('COM_USERS_TFA_LIST_INSTRUCTIONS'); ?>
	</div>

	<?php $this->setLayout('list'); echo $this->loadTemplate(); ?>
</div>
