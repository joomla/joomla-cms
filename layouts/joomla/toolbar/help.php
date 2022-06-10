<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');

$doTask = $displayData['doTask'];
$text   = $displayData['text'];

?>
<button onclick="<?php echo $doTask; ?>" rel="help" class="btn btn-small">
	<span class="icon-question-sign" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
