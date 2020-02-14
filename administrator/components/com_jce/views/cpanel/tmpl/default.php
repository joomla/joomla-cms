<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

$user = JFactory::getUser();
$canEditPref = $user->authorise('core.admin', 'com_jce');

?>
<div class="ui-jce row row-fluid">
	<div class="span12 col-md-12">
        <nav id="wf-cpanel" class="quick-icons">
			<ul class="unstyled">
				<?php echo implode("\n", $this->icons); ?>
			</ul>
		</nav>    

        <dl class="dl-horizontal card card-body well">
            <dt class="wf-tooltip" title="<?php echo JText::_('WF_CPANEL_SUPPORT') . '::' . JText::_('WF_CPANEL_SUPPORT_DESC'); ?>">
                <?php echo JText::_('WF_CPANEL_SUPPORT'); ?>
            </dt>
            <dd><a href="https://www.joomlacontenteditor.net/support" target="_new">https://www.joomlacontenteditor.com/support</a></dd>
            <dt class="wf-tooltip" title="<?php echo JText::_('WF_CPANEL_LICENCE') . '::' . JText::_('WF_CPANEL_LICENCE_DESC'); ?>">
                <?php echo JText::_('WF_CPANEL_LICENCE'); ?>
            </dt>
            <dd><?php echo $this->state->get('licence'); ?></dd>
            <dt class="wf-tooltip" title="<?php echo JText::_('WF_CPANEL_VERSION') . '::' . JText::_('WF_CPANEL_VERSION_DESC'); ?>">
                <?php echo JText::_('WF_CPANEL_VERSION'); ?>
            </dt>
            <dd><?php echo $this->state->get('version'); ?></dd>
            <?php if ($this->params->get('feed', 0) || $canEditPref): ?>
                <dt class="wf-tooltip" title="<?php echo JText::_('WF_CPANEL_FEED') . '::' . JText::_('WF_CPANEL_FEED_DESC'); ?>">
                    <?php echo JText::_('WF_CPANEL_FEED'); ?>
                </dt>
                <dd>
                <?php if ($this->params->get('feed', 0)): ?>
                    <ul class="unstyled wf-cpanel-newsfeed">
                        <li><?php echo JText::_('WF_CPANEL_FEED_NONE'); ?></li>
                    </ul>
                <?php else: ?>
                    <?php echo JText::_('WF_CPANEL_FEED_DISABLED'); ?> :: <a id="newsfeed_enable" title="<?php echo JText::_('WF_PREFERENCES'); ?>" href="#">[<?php echo JText::_('WF_CPANEL_FEED_ENABLE'); ?>]</a>
                <?php endif;?>
                </dd>
            <?php endif;?>
        </dl>
        <?php if (!WF_EDITOR_PRO):
            echo $this->loadTemplate('pro');
        endif;?>
    </div>
</div>