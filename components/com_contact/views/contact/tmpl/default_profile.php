<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php if (JPluginHelper::isEnabled('user', 'profile')) : ?>
	<?php $fields = $this->item->profile->getFieldset('profile'); ?>
	<div class="contact-profile" id="users-profile-custom">
		<dl class="dl-horizontal">
			<?php foreach ($fields as $profile) : ?>
				<?php if ($profile->value) : ?>
					<?php echo '<dt>' . $profile->label . '</dt>'; ?>
					<?php $profile->text = htmlspecialchars($profile->value, ENT_COMPAT, 'UTF-8'); ?>
					<?php switch ($profile->id) : 
						 case 'profile_website': ?>
							<?php $v_http = substr($profile->value, 0, 4); ?>
							<?php if ($v_http === 'http') : ?>
								<?php echo '<dd><a href="' . $profile->text . '">' . JStringPunycode::urlToUTF8($profile->text) . '</a></dd>'; ?>
							<?php else : ?>
								<?php echo '<dd><a href="http://' . $profile->text . '">' . JStringPunycode::urlToUTF8($profile->text) . '</a></dd>'; ?>
							<?php endif; ?>
							<?php break; ?>
						<?php case 'profile_dob': ?>
							<?php echo '<dd>' . JHtml::_('date', $profile->text, JText::_('DATE_FORMAT_LC4'), false) . '</dd>'; ?>
							<?php break; ?>
						<?php default: ?>
							<?php echo '<dd>' . $profile->text . '</dd>'; ?>
							<?php break; ?>
					<?php endswitch; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</dl>
	</div>
<?php endif; ?>
