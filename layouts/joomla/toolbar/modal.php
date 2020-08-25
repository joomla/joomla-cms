<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');

/**
 * Generic toolbar button layout to open a modal
 * -----------------------------------------------
 * @param   array   $displayData    Button parameters. Default supported parameters:
 *                                  - selector  string  Unique DOM identifier for the modal. CSS id without #
 *                                  - class     string  Button class
 *                                  - icon      string  Button icon
 *                                  - text      string  Button text
 */

$selector = $displayData['selector'];
$class    = isset($displayData['class']) ? $displayData['class'] : 'btn btn-small';
$icon     = isset($displayData['icon']) ? $displayData['icon'] : 'out-3';
$text     = isset($displayData['text']) ? $displayData['text'] : '';
?>
<button class="<?php echo $class; ?>" data-toggle="modal" data-target="#<?php echo $selector; ?>">
	<span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
