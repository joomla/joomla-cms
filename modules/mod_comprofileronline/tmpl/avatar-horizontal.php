<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

?>
<?php echo modCBOnlineHelper::getPlugins( $params, 'start', 'span', 1 ); ?>
<?php if ( $preText ) { ?>
	<div class="pretext"><?php echo $preText; ?></div>
<?php } ?>
<?php echo modCBOnlineHelper::getPlugins( $params, 'almostStart', 'span', 1 ); ?>
<span class="cbOnlineUsers">
	<?php echo modCBOnlineHelper::getPlugins( $params, 'beforeUsers', 'span', 1, null, '&nbsp;' ); ?>
	<?php if ( $cbUsers ) foreach ( $cbUsers as $cbUser ) { ?>
		<span class="cbOnlineUser">
			<?php echo $cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true ); ?>
		</span>
		&nbsp;
	<?php } ?>
	<?php echo modCBOnlineHelper::getPlugins( $params, 'afterUsers', 'span', 1, null, '&nbsp;' ); ?>
</span>
<?php echo modCBOnlineHelper::getPlugins( $params, 'almostEnd', 'span', 1 ); ?>
<?php if ( $postText ) { ?>
	<div class="posttext"><?php echo $postText; ?></div>
<?php } ?>
<?php echo modCBOnlineHelper::getPlugins( $params, 'end', 'span', 1 ); ?>