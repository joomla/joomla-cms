<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_messages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$hideLinks = $app->input->getBool('hidemainmenu');
$uri   = Uri::getInstance();
$route = 'index.php?option=com_messages&view=messages&id=' . $app->getIdentity()->id . '&return=' . base64_encode($uri);
?>

<div class="header-item-content">
	<a class="d-flex align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>" <?php echo ($hideLinks ? '' : 'href="' . Route::_($route) . '"'); ?> title="<?php echo Text::_('MOD_MESSAGES_PRIVATE_MESSAGES'); ?>">
		<div class="d-flex align-items-end mx-auto">
			<span class="icon-envelope" aria-hidden="true"></span>
		</div>
		<div class="tiny">
			<?php echo Text::_('MOD_MESSAGES_PRIVATE_MESSAGES'); ?>
		</div>
		<?php if ($countUnread > 0) : ?>
			<span class="badge bg-danger"><?php echo $countUnread; ?></span>
		<?php endif; ?>
	</a>
</div>


