<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   © 2007 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$data = $displayData;
?>
<div class="js-stools-field-selector">
	<?php echo $data['view']->filterForm->getField($data['options']['selectorFieldName'])->input; ?>
</div>
