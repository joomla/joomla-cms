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
  <th scope="row"><a href="<?php echo $article->link; ?>" target="_blank"><?php echo $article->title; ?></a></th>
  <td class="text-right"><span class="small"><?php echo HTMLHelper::_('date', $article->pubDate, 'M j, Y'); ?></span></td>
</tr>
