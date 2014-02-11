<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if (JPluginHelper::isEnabled('user', 'profile')) :
	$fields = $this->item->profile->getFieldset('profile'); ?>
	<div class="contact-profile" id="users-profile-custom">
		<dl class="dl-horizontal">
			<?php foreach ($fields as $profile) :
				if ($profile->value) :
					echo '<dt>' . $profile->label . '</dt>';
					$profile->text = htmlspecialchars($profile->value, ENT_COMPAT, 'UTF-8');

					switch ($profile->id) :
						case "profile_website":
							$v_http = substr($profile->value, 0, 4);

							if ($v_http == "http") :
								echo '<dd><a href="' . $profile->text . '">' . $profile->text . '</a></dd>';
							else :
								echo '<dd><a href="http://' . $profile->text . '">' . $profile->text . '</a></dd>';
							endif;
							break;

						default:
							echo '<dd>' . $profile->text . '</dd>';
							break;
					endswitch;
				endif;
			endforeach; ?>
		</dl>
	</div>
<?php endif; ?>
