<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?>">
<?php echo modFeedHelper::render($params); ?>
</div>
