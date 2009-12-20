<?php
/**
 * @version		$Id: featured_item.php 12416 2009-07-03 08:49:14Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = &$this->item->params;
?>

<?php if ($params->get('show_title')) : ?>
	<h2>
		<?php if ($params->get('link_titles')) : ?>
		<a href="<?php echo $this->item->readmore_link; ?>">
			<?php echo $this->escape($this->item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->title); ?>
		<?php endif; ?>
	</h2>
<?php endif; ?>

<?php if ($params->get('show_print_icon') || $params->get('show_email_icon') || $params->get('access-edit')) : ?>
	<ul class="jactions">
		<?php if ($params->get('show_print_icon')) : ?>
		<li class="jprint">
			<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
		</li>
		<?php endif; ?>
		<?php if ($params->get('show_email_icon')) : ?>
		<li class="jemail">
			<?php echo JHtml::_('icon.email', $this->item, $params); ?>
		</li>
		<?php endif; ?>
		<?php if ($params->get('access-edit')) : ?>
		<li class="jedit">
			<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
		</li>
		<?php endif; ?>
	</ul>
<?php endif; ?>

<?php if (!$params->get('show_intro')) : ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>
<?php endif; ?>

<?php echo $this->item->event->beforeDisplayContent; ?>
<?php // to do not that elegant ?>
<?php if (($params->get('show_author')) or ($params->get('link_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date'))) : ?>
 <dl class="article_info">
<?php endif; ?>
<?php if ($params->get('show_category')) : ?>
<dt class="category_term"><?php  echo JText::_('CATEGORY'); ?></dt>
<dd class="category">
		<?php if ($params->get('link_category')) : ?>
			<a href="<?php echo JRoute::_(ContentRoute::category($this->item->catslug)); ?>">
            <?php echo $this->escape($this->item->category_title); ?>
            </a>
        <?php else : ?>
			<?php echo $this->escape($this->item->category_title); ?>
        <?php endif; ?>
</dd>
<?php endif; ?>
<?php if ($params->get('show_create_date')) : ?>
        <dt class="create_term"> <?php echo JText::_('CREATION_DATE'); ?> </dt>
        <dd class="create_date">
                <?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2')); ?>
        </dd>
<?php endif; ?>
<?php if ($params->get('show_author') && !empty($this->item->author_name)) : ?>
		<dt class="createdby_term"> <?php echo JText::_('WRITTEN_BY'); ?></dt>
		<dd class="createdby">
			<?php echo ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author_name); ?>
		</dd>
	<?php endif; ?>
<?php if (($params->get('show_author')) or ($params->get('link_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date'))) : ?>
 </dl>
<?php endif; ?>

<?php echo $this->item->introtext; ?>

<?php if ($params->get('show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
		$link = JRoute::_(ContentRoute::article($this->item->slug, $this->item->catslug));
	else :
		$link = JRoute::_("index.php?option=com_users&view=login");
	endif;
?>
	<p class="jreadmore">
                <a href="<?php echo $link; ?>" class="readon">
                        <?php if (!$params->get('access-view')) :
                                echo JText::_('Register to read more...');
                        elseif ($readmore = $params->get('readmore')) :
                                echo $readmore;
                        else :
                             echo JText::sprintf('Read more', $this->escape($this->item->title));
                        endif; ?></a>
        </p>
<?php endif; ?>


<div class="jseparator"></div>
<?php echo $this->item->event->afterDisplayContent; ?>
