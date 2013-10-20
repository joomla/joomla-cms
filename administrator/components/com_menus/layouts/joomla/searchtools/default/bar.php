<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;
?>
<div class="js-stools-field-filter js-stools-menutype hidden-phone hidden-tablet">
	<?php echo $data['menutype']->input; ?>
</div>
<?php
// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));