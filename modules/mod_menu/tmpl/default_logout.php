<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$user = JFactory::getUser();
if (!$user->guest):
	$returnUrl = $item->params->get('logout');

	if (empty($returnUrl))
	{
		$returnUrl = '';
	}
	else
	{
		$returnUrl = 'index.php?Itemid=' . $item->params->get('logout');
		$returnUrl = base64_encode($returnUrl);
	}

	// Note. It is important to remove spaces between elements.
	$class = $item->anchor_css ? 'class="'.$item->anchor_css.'" ' : '';
	$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';
	$linktype = $item->title;
	if ($item->menu_image)
	{
		$item->params->get('menu_text', 1) ?
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title">'.$item->title.'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
	}
	else
	{
		$linktype = $item->title;
	}
	?>
	<form>
		<div class=$class>
			<input type="submit" name="Logout" class="btn btn-nav" value="<?php echo JText::_('JLOGOUT'); ?>" />
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="user.logout" />
			<input type="hidden" name="return" value="<?php echo $returnUrl; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
<?php endif; ?>
