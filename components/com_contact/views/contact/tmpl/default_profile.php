<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if (JPluginHelper::isEnabled('user', 'profile')) : ?>
<div class="jcontact-profile">
	<ol>
	<?php foreach ($this->contact->profile as $profile) :
		if ($profile->profile_value) {

			$profile->text = htmlspecialchars($profile->profile_value, ENT_COMPAT, 'UTF-8')

			switch ($profile->profile_key)
			{
				case "profile.website":
					$v_http = substr ($profile->profile_value, 0, 4);

					if ($v_http == "http") {
						echo '<li><a href="'.$profile->text.'">'.$profile->text.'</a></li>';
					} else {
						echo '<li><a href="http://'.$profile->text.'">'.$profile->text.'</a></li>';
					}
					break;

				default:
					echo '<li>'.$profile->text.'</li>';
					break;
			}
		}
	endforeach; ?>
	</ol>
</div>
<?php endif; ?>
