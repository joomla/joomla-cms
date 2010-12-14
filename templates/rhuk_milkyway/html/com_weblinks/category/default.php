<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; 
// Code to support edit links for weblinks
// Create a shortcut for params.
$params = &$this->params;
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('behavior.tooltip');
JHtml::core();
// Get the user object.
$user = JFactory::getUser();
// Check if user is allowed to add/edit based on weblinks permissinos.
$canEdit = $user->authorise('core.edit', 'com_weblinks');
$canCreate = $user->authorise('core.create', 'com_weblinks');
$canEditState = $user->authorise('core.edit.state', 'com_weblinks');

$n = count($this->items);
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<?php if ( $this->params->def( 'show_page_heading', 1 ) ) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</div>
<?php endif; ?>
<?php if($this->params->get('show_category_title', 1)) : ?>
<h2>
	<?php echo JHtml::_('content.prepare', $this->category->title); ?>
</h2>
<?php endif; ?>

<?php if ( ($this->params->def('image', -1) != -1) || $this->params->def('show_comp_description', 1) ) : ?>
<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<tr>
	<td valign="top" class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php
		if ( isset($this->image) ) :  echo $this->image; endif;
		echo $this->params->get('comp_description');
	?>
	</td>
</tr>
</table>
<?php endif; ?>
<ul>
	<?php foreach ($this->items as $i => $item) : ?>
	<li>
				<?php if ($this->params->get('link_icons') <> -1) : ?>
					<?php echo JHTML::_('image','system/'.$this->params->get('link_icons', 'weblink.png'), JText::_('COM_WEBLINKS_LINK'), NULL, true);?>
				<?php endif; ?>
				<?php
					// Compute the correct link
					$menuclass = 'category'.$this->params->get('pageclass_sfx');
					$link = $item->link;
					switch ($item->params->get('target', $this->params->get('target')))
					{
						case 1:
							// open in a new window
							echo '<a href="'. $link .'" target="_blank" class="'. $menuclass .'" rel="nofollow">'.
								$this->escape($item->title) .'</a>';
							break;

						case 2:
							// open in a popup window
							echo "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">".
								$this->escape($item->title) ."</a>\n";
							break;
						case 3:
							// TODO: open in a modal window
							JHtml::_('behavior.modal', 'a.modal'); ?>
							<a class="modal" title="<?php  echo $this->escape($item->title) ?> " href="<?php echo $link;?>"  rel="{handler: 'iframe', size: {x: 500, y: 506}}\"></a>
							<?php echo $this->escape($item->title). ' </a>' ;
							break;

						default:
							// open in parent window
							echo '<a href="'.  $link . '" class="'. $menuclass .'" rel="nofollow">'.
								$this->escape($item->title) . ' </a>';
							break;
					}
				?>
				<?php // Code to add the edit link for the weblink. ?>
	
						<?php if ($canEdit) : ?>
							<ul class="actions">
								<li class="edit-icon"><?php //var_dump($this);die; ?>
									<?php echo JHtml::_('icon.edit', $item, $params); ?>
								</li>
							</ul>
						<?php endif; ?>		
			</p>

	</li>
<?php endforeach; ?>
</ul>
