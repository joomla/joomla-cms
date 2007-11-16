<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php if ($showmode == 0 || $showmode == 2) : ?>
    <?php if ($count['guest'] != 0 || $count['user'] != 0) : ?>
        <?php echo JText::_('We have') . '&nbsp;'; ?>
		<?php if ($count['guest'] == 1) : ?>
		     <?php echo JText::sprintf('guest', '1'); ?>
		<?php else : ?>
		    <?php if ($count['guest'] > 1) : ?>
			    <?php echo JText::sprintf('guest', $count['guest']); ?>
			<?php endif; ?>
		<?php endif; ?>
		
		<?php if ($count['guest'] != 0 && $count['user'] != 0) : ?>
		    <?php echo '&nbsp;' . JText::_('and') . '&nbsp;'; ?>
	    <?php endif; ?>		
		
		<?php if ($count['user'] == 1) : ?>
		     <?php echo JText::sprintf('member', '1'); ?>
		<?php else : ?>
		    <?php if ($count['user'] > 1) : ?>
			    <?php echo JText::sprintf('member', $count['user']); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php echo '&nbsp;' . JText::_('online'); ?>
    <?php endif; ?>
<?php endif; ?>	

<?php if(($showmode > 0) && count($names)) : ?>
    <ul>
    <?php foreach($names as $name) : ?>
	    <li><strong><?php echo $name->username; ?></strong></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>