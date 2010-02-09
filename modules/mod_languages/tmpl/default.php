<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_languages
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$uri = JFactory::getURI();
?>
<form action="<?php echo JRoute::_('index.php?option=com_languages'); ?>" method="post" id="languages-form">
	<div class="mod_languages<?php echo $params->get('moduleclass_sfx') ?>">
	<?php if ($headerText) : ?>
		<div class="mod_languages_header"><?php echo $headerText; ?></div>
	<?php endif; ?>
	<?php echo JHtml::_('select.genericlist', $list, 'tag', ' onchange="this.form.submit();"','value','text',$tag);?>
	<?php if ($footerText) : ?>
		<div class="mod_languages_footer"><?php echo $footerText; ?></div>
	<?php endif; ?>
	</div>
	<input type="hidden" name="redirect" value="<?php echo base64_encode($uri);?>" />
	<input type="hidden" name="task" value="select" />
	<?php echo JHtml::_('form.token'); ?>	
</form>

