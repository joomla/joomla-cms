<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$class = empty($displayData['style']) ? 'spacer' : $displayData['style'];
$style = $displayData['style'];

?>
<li class="<?php echo $class; ?>"<?php echo $style; ?>></li>
