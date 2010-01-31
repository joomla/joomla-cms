<?php
/**
 * @version
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_profile')) : ?>
<div class="jcontact-profile">
	<h4>
		<?php echo JText::_('Com_Contact_Profile_Heading'); ?>
	</h4>
	<ol>
		<?php foreach ($this->contact->profile as $profile) :	?>
			<li>

				<?php echo $profile->text = htmlspecialchars($profile->profile, ENT_COMPAT, 'UTF-8'); ?>
				
			</li>
		<?php endforeach; ?>
	</ol>
</div>
<?php endif; ?>
