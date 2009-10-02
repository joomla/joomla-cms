<?php
/**
 * @version		$Id$
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
	<h3>
		<?php if ($params->get('link_titles')) : ?>
		<a href="<?php echo $this->item->readmore_link; ?>">
			<?php echo $this->escape($this->item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->title); ?>
		<?php endif; ?>
	</h3>
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

<?php if ($params->get('show_category')) : ?>
	<span>
		<?php if ($params->get('link_category')) : ?>
			<a href="<?php echo JRoute::_(ContentRoute::category($this->item->catslug)); ?>">
				<?php echo $this->escape($this->item->category); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->category); ?>
		<?php endif; ?>
	</span>
<?php endif; ?>
<div class="jiteminfo">
	<?php if ($params->get('show_category')) : ?>
		<span class="jcategory">
			<?php if ($params->get('link_category')) : ?>
				<a href="<?php echo JRoute::_(ContentRoute::category($this->item->catslug)); ?>">
					<?php echo $this->escape($this->item->category); ?> </a>
			<?php else : ?>
				<?php echo $this->escape($this->item->category); ?>
			<?php endif; ?>
		</span>
	<?php endif; ?>

	<?php if ($params->get('show_create_date')) : ?>
		<span class="jcreated-date">
			<?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2')); ?>
		</span>
	<?php endif; ?>

	<?php if (intval($this->item->modified) && $params->get('show_modify_date')) : ?>
		<span class="jmodified-date">
			<?php echo JText::sprintf('LAST_UPDATED2', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
		</span>
	<?php endif; ?>

	<?php if ($params->get('show_author') && !empty($this->item->author_name)) : ?>
		<span class="jcreated-by">
			<?php echo JText::sprintf('Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author_name)); ?>
		</span>
	<?php endif; ?>
	<?php $debugthis = $this->item; ?>

</div><!-- end iteminfo -->

<?php echo $this->item->introtext; ?>

<?php if ($params->get('show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
		$link = JRoute::_(ContentRoute::article($this->item->slug, $this->item->catslug));
	else :
		$link = JRoute::_("index.php?option=com_users&view=login");
	endif;
?>
	<div class="jreadmore">
		<a href="<?php echo $link; ?>" >
			<?php if (!$params->get('access-view')) :
				echo JText::_('Register to read more...');
			elseif ($readmore = $params->get('readmore')) :
				echo $readmore;
			else :
				echo JText::sprintf('Read more...');
			endif; ?></a>
	</div>
<?php endif; ?>

<div class="jseparator"></div>
<?php echo $this->item->event->afterDisplayContent; ?>
