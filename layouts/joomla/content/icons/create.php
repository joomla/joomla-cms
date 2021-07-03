<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @deprecated  5.0 without replacement
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$params = $displayData['params'];

?>
<?php if ($params->get('show_icons')) : ?>
	<span class="icon-plus icon-fw" aria-hidden="true"></span>
	<?php echo Text::_('JNEW'); ?>
<?php else : ?>
	<?php echo Text::_('JNEW') . '&#160;'; ?>
<?php endif; ?>
