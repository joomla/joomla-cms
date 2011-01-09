<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; 
// Create a shortcut for params.
$params = &$this->item->params;
$canEdit = $this->user->authorise('core.edit', 'com_content.article.' . $this->item->id);
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('behavior.tooltip');
JHtml::core();
?>
<?php $canEdit	= ($this->user->authorise('core.edit', 'com_content.article.'.$this->item->id)); ?>
<?php if ($this->item->state == 0) : ?>

<div class="system-unpublished">
  <?php endif; ?>
    <?php if ($params->get('show_print_icon') || $params->get('show_email_icon') || $canEdit || $this->item->params->get('show_title')) : ?>
  
  <table class="contentpaneopen<?php echo $this->escape($this->item->params->get( 'pageclass_sfx' )); ?>">
    <tr>
      <?php if ($this->item->params->get('show_title')) : ?>
      <td class="contentheading<?php echo $this->escape($this->item->params->get( 'pageclass_sfx' )); ?>" width="100%"><?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
        <a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>"> <?php echo $this->escape($this->item->title); ?></a>
        <?php else : ?>
        <?php echo $this->escape($this->item->title); ?>
        <?php endif; ?>
      </td>
      <?php endif; ?>
      <?php if ($params->get('show_print_icon')) : ?>
      <td align="right" width="100%" class="buttonheading"><?php echo JHtml::_('icon.print_popup', $this->item, $params); ?> </td>
      <?php endif; ?>
      <?php if ($params->get('show_email_icon')) : ?>
      <td align="right" width="100%" class="buttonheading"><?php echo JHtml::_('icon.email', $this->item, $params); ?> </td>
      <?php endif; ?>
      <?php if ($canEdit) : ?>
      <td align="right" width="100%" class="buttonheading"><?php echo JHtml::_('icon.edit', $this->item, $params); ?> </td>
      <?php endif; ?>
    </tr>
 
  <?php endif; ?>
  <?php  if (!$this->item->params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
  <?php echo $this->item->event->beforeDisplayContent; ?>
  <?php if (($params->get('show_category')) or  ($params->get('show_parent_category'))) : ?>
  <tr>
    <td><?php if ($this->item->params->get('show_parent_category') && ($this->item->parent_id )) : ?>
      <span>
      <?php if ($params->get('show_parent_category')) : ?>
      <?php $title = $this->escape($this->item->parent_title);
						$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_id)) . '">' . $title . '</a>'; ?>
      <?php if ($params->get('link_parent_category')) : ?>
      <?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
      <?php else : ?>
      <?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
      <?php endif; ?>
      <?php endif; ?>
      <?php if ($params->get('show_category')) : ?>
      <?php $title = $this->escape($this->item->category_title);
							$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catid)) . '">' . $title . '</a>'; ?>
      <?php if ($params->get('link_category')) : ?>
      <?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
      <?php else : ?>
      <?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
      <?php endif; ?>
      <?php endif; ?>
      </span>
      <?php endif; ?>
      <?php if ($this->item->params->get('show_category') && $this->item->catid) : ?>
      <span>
      <?php $title = $this->escape($this->item->category_title);
					$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catid)) . '">' . $title . '</a>'; ?>
      <?php if ($params->get('link_category')) : ?>
      <?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
      <?php else : ?>
      <?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
      <?php endif; ?>
      </span>
      <?php endif; ?>
    </td>
  </tr>
  <?php endif; ?>
  <?php if (($this->item->params->get('show_author')) && ($this->item->author != "")) : ?>
  <tr>
    <td width="70%"  valign="top" colspan="2"><span class="small">
      <?php JText::printf( 'COM_CONTENT_WRITTEN_BY', ($this->escape($this->item->created_by_alias) ? $this->escape($this->item->created_by_alias) : $this->escape($this->item->author)) ); ?>
      </span> &#160;&#160; </td>
  </tr>
  <?php endif; ?>
  <?php if ($params->get('show_create_date')) : ?>
  <tr>
    <td valign="top" colspan="2" class="createdate"><?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHTML::_('date',$this->item->created, JText::_('DATE_FORMAT_LC2'))); ?> </td>
  </tr>
  <?php endif; ?>
  <?php if ($params->get('show_modify_date')) : ?>
  <tr>
    <td valign="top" colspan="2" class="createdate"><?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHTML::_('date',$this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?> </td>
  </tr>
  <?php endif; ?>
  <?php if ($params->get('show_publish_date')) : ?>
  <tr>
    <td valign="top" colspan="2" class="createdate"><?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE', JHTML::_('date',$this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?> </td>
  </tr>
  <?php endif; ?>
  <?php if ($this->item->params->get('show_url') && $this->item->urls) : ?>
  <tr>
    <td valign="top" colspan="2"><a href="http://<?php echo $this->escape($this->item->urls) ; ?>" target="_blank"> <?php echo $this->escape($this->item->urls); ?></a> </td>
  </tr>
  <?php endif; ?>
  <tr>
    <td valign="top" colspan="2"><?php if (isset ($this->item->toc)) : ?>
      <?php echo $this->item->toc; ?>
      <?php endif; ?>
      <?php echo $this->item->introtext; ?> </td>
  </tr>
  <?php if ($this->item->params->get('show_readmore') && $this->item->readmore) : ?>
  <tr>
    <td  colspan="2"><?php if ($params->get('show_readmore') && $this->item->readmore) :
		if ($params->get('access-view')) :
			$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
		else :
			$menu = JFactory::getApplication()->getMenu();
			$active = $menu->getActive();
			$itemId = $active->id;
			$link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
			$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug));
			$link = new JURI($link1);
			$link->setVar('return', base64_encode($returnURL));
		endif;
	?>
      <p class="readmore"> <a href="<?php echo $link; ?>">
        <?php if (!$params->get('access-view')) :
							echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
						elseif ($readmore = $this->item->alternative_readmore) :
							echo $readmore;
						else :
							echo JText::sprintf('COM_CONTENT_READ_MORE', $this->escape($this->item->title));
						endif; ?>
        </a> </p></td>
  </tr>
  <?php endif; ?>
  </table>
  <?php endif; ?>
  <?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>
