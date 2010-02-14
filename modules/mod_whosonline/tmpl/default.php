<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_whosonline
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;


if ($count['guest'] <= 1) {
$guest = JText::_('MOD_WHOSONLINE_GUEST');
} else if ($count['guest'] > 1) {
$guest = JText::_('MOD_WHOSONLINE_GUESTS');}

if ($count['user'] <= 1) {
$member = JText::_('MOD_WHOSONLINE_MEMBER');
} else if ($count['user'] > 1) {
$member = JText::_('MOD_WHOSONLINE_MEMBERS');}


if ($showmode == 0 || $showmode == 2) :
	echo JText::sprintf('MOD_WHOSONLINE_WE_HAVE', $guest, $count['guest'], $member, $count['user']);
endif;

if (($showmode > 0) && count($names)) : ?>
	<ul  class="whosonline" >
<?php foreach($names as $name) : ?>

		<li>
		<?php if ($linknames==1) { ?>
		<a href="index.php?option=com_users&view=profile&member_id=<?php echo (int) $name->userid; ?>">
		<?php } ?>
		<?php echo $name->username; ?>
			<?php if ($linknames==1) : ?>
				</a>
			<?php endif; ?>
		</li>
<?php endforeach;  ?>
	</ul>
<?php endif;