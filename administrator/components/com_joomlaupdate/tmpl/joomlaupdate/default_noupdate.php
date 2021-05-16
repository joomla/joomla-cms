<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */
?>
<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES'); ?>
	</legend>
	<p>
		<?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?>
	</p>
	<div class="alert alert-success">
		<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', '&#x200E;' . JVERSION); ?>
	</div>
</fieldset>
