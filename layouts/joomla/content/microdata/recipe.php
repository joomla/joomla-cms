<?php
/**
 * @package     Joomla.Cms
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$microdata     = $displayData['item']->metadata;
$cookTime      = $microdata->get('microdata_recipe_cookTime');
$prepTime      = $microdata->get('microdata_recipe_prepTime');
$calories      = $microdata->get('microdata_recipe_calories');
$ingredients   = $microdata->get('microdata_recipe_ingredients');

?>
<dd class="cookTime">
	<meta data-sd="cookTime" content="<?php echo "PT" . $cookTime . "M";?>" />
	<?php echo JText::sprintf('JFIELD_MICRODATA_RECIPE_COOKTIME_LABEL') . ': ' . $cookTime . ' ' . JText::sprintf('MINUTES_SHORT'); ?>
</dd>
<dd class="prepTime">
	<meta data-sd="prepTime" content="<?php echo "PT" . $prepTime . "M";?>" />
	<?php echo JText::sprintf('JFIELD_MICRODATA_RECIPE_PREPTIME_LABEL') . ': ' . $prepTime . ' ' . JText::sprintf('MINUTES_SHORT'); ?>
	<meta data-sd="totalTime" content="<?php echo "PT" . ($cookTime + $prepTime) . "M";?>" />
</dd>
<dd class="calories" data-sd="nutrition.NutritionInformation">
	<?php echo JText::sprintf('JFIELD_MICRODATA_RECIPE_CALORIES_LABEL') . ':';?>
	<span data-sd="calories">
		<?php echo $calories;?>
	</span>
</dd>
<dd class="ingredients">
	<?php echo JText::sprintf('JFIELD_MICRODATA_RECIPE_INGREDIENTS_LABEL') . ':';?>
	<span data-sd="Recipe ingredients">
		<?php echo $ingredients;?>
	</span>
</dd>