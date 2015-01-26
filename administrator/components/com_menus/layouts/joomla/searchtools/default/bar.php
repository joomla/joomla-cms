<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// We will get the menutype filter & remove it from the form filters
$menuTypeField = $data['view']->filterForm->getField('menutype');
?>
<div class="js-stools-field-filter js-stools-menutype hidden-phone hidden-tablet">
	<?php echo $menuTypeField->input; ?>
</div>
<?php
// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));
