<?php
/**
 * @package     Joomla.Site
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
use Joomla\Component\Users\Site\View\Methods\HtmlView;

// phpcs:ignoreFile
/** @var HtmlView $this */

?>
<div id="com-users-methods-list">
	<?php if (!$this->isAdmin): ?>
	<h3 id="com-users-methods-list-head">
		<?php echo Text::_('COM_USERS_TFA_FIRSTTIME_PAGE_HEAD'); ?>
	</h3>
	<?php endif; ?>
	<div id="com-users-methods-list-instructions" class="alert alert-info">
		<h2 class="alert-heading">
			<span class="fa fa-shield-alt" aria-hidden="true"></span>
			<?php echo Text::_('COM_USERS_TFA_FIRSTTIME_INSTRUCTIONS_HEAD'); ?>
		</h2>
		<p>
			<?php echo Text::_('COM_USERS_TFA_FIRSTTIME_INSTRUCTIONS_WHATITDOES'); ?>
		</p>
		<a href="<?php echo Route::_(
				'index.php?option=com_users&task=methods.doNotShowThisAgain' .
				($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') .
				'&user_id=' . $this->user->id .
				'&' . Factory::getApplication()->getFormToken() . '=1'
		)?>"
		   class="btn btn-danger w-100">
			<?php echo Text::_('COM_USERS_TFA_FIRSTTIME_NOTINTERESTED'); ?>
		</a>
	</div>

	<?php $this->setLayout('list'); echo $this->loadTemplate(); ?>
</div>
