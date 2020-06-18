<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   © 2009 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$id = empty($displayData['id']) ? '' : $displayData['id'];
$active = empty($displayData['active']) ? '' : $displayData['active'];

?>

<div id="<?php echo $id; ?>" class="tab-pane<?php echo $active; ?>">
