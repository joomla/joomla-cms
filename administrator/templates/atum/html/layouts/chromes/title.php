<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Module chrome for rendering the module in a submenu
 */

defined('_JEXEC') or die;

$module = $displayData['module'];

if ((string) $module->content === '')
{
	return;
}

?>
<div class="card-header">
	<h6><?php echo $module->title; ?></h6>
</div>
<?php echo $module->content; ?>
