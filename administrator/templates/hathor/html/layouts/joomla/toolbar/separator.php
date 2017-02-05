<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$class = (empty($displayData['style'])) ? 'spacer' : $displayData['style'];
$style = $displayData['style'];

?>
<li class="<?php echo $class; ?>"<?php echo $style; ?>></li>
