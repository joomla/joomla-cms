<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');

// Create shortcut to parameters.
$params = $this->state->get('params');
?>

<div class="item-page<?php echo $params->get('pageclass_sfx')?>">
	<?php if ($params->get('show_page_title', 1) && $params->get('page_title') != $this->item->title) : ?>
		<h1>
			<?php if ($this->escape($params->get('page_heading'))) :?>
				<?php echo $this->escape($params->get('page_heading')); ?>
			<?php else : ?>
				<?php echo $this->escape($params->get('page_title')); ?>
			<?php endif; ?>
		</h1>
	<?php endif; ?>

<?php if ($params->get('show_title')|| $params->get('access-edit')) : ?>
        <h2>
               	<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
                <a href="<?php echo $this->item->readmore_link; ?>">
                        <?php echo $this->escape($this->item->title); ?></a>
                <?php else : ?>
                        <?php echo $this->escape($this->item->title); ?>
                <?php endif; ?>
        </h2>
<?php endif; ?>


<?php if ($params->get('access-edit') || $params->get('show_title') ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
        <ul class="actions">
        <?php if (!$this->print) : ?>
                <?php if ($params->get('show_print_icon')) : ?>
                <li class="print-icon">
                        <?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?>
                </li>
                <?php endif; ?>

                <?php if ($params->get('show_email_icon')) : ?>
                <li class="email-icon">
                        <?php echo JHtml::_('icon.email',  $this->item, $params); ?>
                </li>
                <?php endif; ?>
               	<?php if ($this->user->authorise('core.edit', 'com_content.article.'.$this->item->id)) : ?>
						<li class="edit-icon">
							<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
						</li>
					<?php endif; ?>
        <?php else : ?>
                <li>
                        <?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
                </li>
        <?php endif; ?>
        </ul>
<?php endif; ?>

	<?php  if (!$params->get('show_intro')) :
		echo $this->item->event->afterDisplayTitle;
	endif; ?>

	<?php echo $this->item->event->beforeDisplayContent; ?>

<?php // to do not that elegant would be nice to group the params ?>

<?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))) : ?>
 <dl class="article-info">
 <dt class="article-info-term"><?php  echo JText::_('JContent_Article_Infos'); ?></dt>
<?php endif; ?>
<?php if ($params->get('show_category')) : ?>
<dd class="category-name"><?php  echo JText::_('JContent_Category'); ?>
                <?php if ($params->get('link_category')) : ?>
                	<?php echo '<a href="'.JRoute::_(ContentRoute::category($this->item->catslug)).'">'; ?>
                	<?php echo $this->escape($this->item->category_title); ?></a>
                	<?php else : ?>
                	<?php echo $this->escape($this->item->category_title); ?>
				<?php endif; ?>
     </dd>
<?php endif; ?>
<?php if ($params->get('show_create_date')) : ?>
        <dd class="create">
         <?php   echo JText::sprintf('CONTENT_CREATED_DATE', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'))); ?>
		</dd>
<?php endif; ?>
<?php if ($params->get('show_modify_date')) : ?>
       <dd class="modified">
       <?php   echo JText::sprintf('LAST_UPDATED2', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
        </dd>
<?php endif; ?>
<?php if ($params->get('show_publish_date')) : ?>
       <dd class="published">
       <?php   echo JText::sprintf('PUBLISHED_DATE', JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
        </dd>
<?php endif; ?>
<?php if ($params->get('show_author') && !empty($this->item->author)) : ?>
	  <dd class="createdby">
       <?php $author=($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author);?>
    <?php echo JText::sprintf('Written_by', $author); ?>
		</dd>
	<?php endif; ?>
<?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))) : ?>
 </dl>
<?php endif; ?>

	<?php if (isset ($this->item->toc)) : ?>
		<?php echo $this->item->toc; ?>
	<?php endif; ?>

	<?php echo $this->item->text; ?>

	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
