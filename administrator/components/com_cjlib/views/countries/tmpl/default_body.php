<?php
/**
 * @version		$Id: default_body.php 2013-07-29 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;
$user = JFactory::getUser();
?>
<?php foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td><?php echo $item->id; ?></td>
		<td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
		<td><?php echo $this->escape($item->country_code); ?></td>
		<td><input name="country_name" type="text" style="margin-bottom: 0;" value="<?php echo $this->escape($item->country_name);?>"></td>
		<td><?php echo $this->escape($item->language);?></td>
	</tr>
<?php endforeach; ?>

