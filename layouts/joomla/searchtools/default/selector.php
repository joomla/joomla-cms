<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$data = $displayData;
?>
<div class="js-stools-field-selector">
	<div class="visually-hidden">
		<?php echo $data['view']->filterForm->getField($data['options']['selectorFieldName'])->label; ?>
	</div>
	<?php echo $data['view']->filterForm->getField($data['options']['selectorFieldName'])->input; ?>
</div>
