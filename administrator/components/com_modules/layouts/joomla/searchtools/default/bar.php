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

$clientIdField = $data['view']->filterForm->getField('client_id');
?>
<script type="text/javascript">
jQuery.fn.clearPositionType = function(){
	jQuery("#filter_position, #filter_module, #filter_language").val("");
};
</script>
<div class="js-stools-field-filter js-stools-client_id hidden-phone hidden-tablet">
	<?php echo $clientIdField->input; ?>
</div>
<?php
// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));
