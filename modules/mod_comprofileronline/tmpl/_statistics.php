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
<?php echo modCBOnlineHelper::getPlugins( $params, 'start' ); ?>
<?php if ( $preText ) { ?>
	<div class="pretext">
		<p><?php echo $preText; ?></p>
	</div>
<?php } ?>
<?php echo modCBOnlineHelper::getPlugins( $params, 'beforeStatistics' ); ?>
<ul class="unstyled cbOnlineStatistics">
	<?php echo modCBOnlineHelper::getPlugins( $params, 'beforeList' ); ?>
	<li class="cbStatisticsOnline">
		<?php
			$icon	=	'<span class="' . htmlspecialchars( $templateClass ) . '">'
					.		'<span class="cbModuleStatisticsOnlineIcon fa fa-circle" title="' . htmlspecialchars( CBTxt::T( 'ONLINE_USERS', 'Online Users' ) ) . '"></span>'
					.	'</span>';

			if ( $label == 3 ) {
				echo CBTxt::T( 'ICON_ONLINE_USERS_COUNT_FORMAT', '[icon] Online Users: [count_format]', array( '[icon]' => $icon, '[count]' => $onlineUsers, '[count_format]' => number_format( (float) $onlineUsers, 0, '.', $separator ) ) );
			} elseif ( $label == 2 ) {
				echo CBTxt::T( 'ICON_COUNT_FORMAT', '[icon] [count_format]', array( '[icon]' => $icon, '[count]' => $onlineUsers, '[count_format]' => number_format( (float) $onlineUsers, 0, '.', $separator ) ) );
			} else {
				echo CBTxt::T( 'ONLINE_USERS_COUNT_FORMAT', 'Online Users: [count_format]', array( '[count]' => $onlineUsers, '[count_format]' => number_format( (float) $onlineUsers, 0, '.', $separator ) ) );
			}
		?>
	</li>
	<li class="cbStatisticsOffline">
		<?php
			$icon	=	'<span class="' . htmlspecialchars( $templateClass ) . '">'
					.		'<span class="cbModuleStatisticsOfflineIcon fa fa-circle-o" title="' . htmlspecialchars( CBTxt::T( 'OFFLINE_USERS', 'Offline Users' ) ) . '"></span>'
					.	'</span>';

			if ( $label == 3 ) {
				echo CBTxt::T( 'ICON_OFFLINE_USERS_COUNT_FORMAT', '[icon] Offline Users: [count_format]', array( '[icon]' => $icon, '[count]' => $offlineUsers, '[count_format]' => number_format( (float) $offlineUsers, 0, '.', $separator ) ) );
			} elseif ( $label == 2 ) {
				echo CBTxt::T( 'ICON_COUNT_FORMAT', '[icon] [count_format]', array( '[icon]' => $icon, '[count]' => $offlineUsers, '[count_format]' => number_format( (float) $offlineUsers, 0, '.', $separator ) ) );
			} else {
				echo CBTxt::T( 'OFFLINE_USERS_COUNT_FORMAT', 'Offline Users: [count_format]', array( '[count]' => $offlineUsers, '[count_format]' => number_format( (float) $offlineUsers, 0, '.', $separator ) ) );
			}
		?>
	</li>
	<li class="cbStatisticsGuest">
		<?php
			$icon	=	'<span class="' . htmlspecialchars( $templateClass ) . '">'
					.		'<span class="cbModuleStatisticsGuestIcon fa fa-eye" title="' . htmlspecialchars( CBTxt::T( 'GUESTS', 'Guests' ) ) . '"></span>'
					.	'</span>';

			if ( $label == 3 ) {
				echo CBTxt::T( 'ICON_GUESTS_COUNT_FORMAT', '[icon] Guests: [count_format]', array( '[icon]' => $icon, '[count]' => $guestUsers, '[count_format]' => number_format( (float) $guestUsers, 0, '.', $separator ) ) );
			} elseif ( $label == 2 ) {
				echo CBTxt::T( 'ICON_COUNT_FORMAT', '[icon] [count_format]', array( '[icon]' => $icon, '[count]' => $guestUsers, '[count_format]' => number_format( (float) $guestUsers, 0, '.', $separator ) ) );
			} else {
				echo CBTxt::T( 'GUESTS_COUNT_FORMAT', 'Guests: [count_format]', array( '[count]' => $guestUsers, '[count_format]' => number_format( (float) $guestUsers, 0, '.', $separator ) ) );
			}
		?>
	</li>
	<?php echo modCBOnlineHelper::getPlugins( $params, 'afterStatistics' ); ?>
</ul>
<?php echo modCBOnlineHelper::getPlugins( $params, 'almostEnd' ); ?>
<?php if ( $postText ) { ?>
	<div class="posttext">
		<p><?php echo $postText; ?></p>
	</div>
<?php } ?>
<?php echo modCBOnlineHelper::getPlugins( $params, 'end' ); ?>