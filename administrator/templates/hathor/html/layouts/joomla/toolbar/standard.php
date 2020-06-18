<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   © 2009 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doTask   = $displayData['doTask'];
$class    = $displayData['class'];
$text     = $displayData['text'];
$btnClass = $displayData['btnClass'];

?>

<a href="javascript:void(0)" onclick="<?php echo $doTask; ?>" class="toolbar">
	<span class="<?php echo $class; ?>"></span>
	<?php echo $text; ?>
</a>
