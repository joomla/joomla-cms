<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

?>
<tr>
  <th scope="row"><strong><a href="<?php echo $event->url; ?>" target="_blank"><?php echo $event->title; ?></a></strong><br><span class="small"><?php echo $event->location; ?></span></th>
  <td class="text-right"><span class="small"><?php echo HTMLHelper::_('date', $event->start, 'D, M j, Y'); ?></span><br><span class="small"><?php echo HTMLHelper::_('date', $event->start, 'H:i T'); ?></span></td>
</tr>
