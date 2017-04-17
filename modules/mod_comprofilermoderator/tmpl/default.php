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
<?php echo modCBModeratorHelper::getPlugins( $params, 'start' ); ?>
<?php if ( $preText ) { ?>
	<div class="pretext">
		<p><?php echo $preText; ?></p>
	</div>
<?php } ?>
<?php echo modCBModeratorHelper::getPlugins( $params, 'almostStart' ); ?>
<ul class="unstyled cbModeratorLinks">
	<?php echo modCBModeratorHelper::getPlugins( $params, 'beforeLinks' ); ?>
	<?php if ( $showBanned ) { ?>
		<li class="cbModeratorLink cbModeratorLinkBanned">
			<a href="<?php echo $_CB_framework->userProfileUrl(); ?>"><?php echo ( $bannedStatus == 1 ? CBTxt::T( 'Profile Banned' ) : CBTxt::T( 'Unban Request Pending' ) ); ?></a>
		</li>
	<?php } ?>
	<?php if ( $showImageApproval ) { ?>
		<li class="cbModeratorLink cbModeratorLinkImageApproval">
			<a href="<?php echo $_CB_framework->viewUrl( 'moderateimages' ); ?>"><?php echo ( $imageApprovalCount == 1 ? CBTxt::T( 'COUNT_IMAGE_APPROVAL', '[count] Image Approval', array( '[count]' => $imageApprovalCount ) ) : CBTxt::T( 'COUNT_IMAGE_APPROVALS', '[count] Image Approvals', array( '[count]' => $imageApprovalCount ) ) ); ?></a>
		</li>
	<?php } ?>
	<?php if ( $showUserReports ) { ?>
		<li class="cbModeratorLink cbModeratorLinkUserReports">
			<a href="<?php echo $_CB_framework->viewUrl( 'moderatereports' ); ?>"><?php echo ( $userReportsCount == 1 ? CBTxt::T( 'COUNT_PROFILE_REPORT', '[count] Profile Report', array( '[count]' => $userReportsCount ) ) : CBTxt::T( 'COUNT_PROFILE_REPORTS', '[count] Profile Reports', array( '[count]' => $userReportsCount ) ) ); ?></a>
		</li>
	<?php } ?>
	<?php if ( $showUnbanRequests ) { ?>
		<li class="cbModeratorLink cbModeratorLinkUnbanRequests">
			<a href="<?php echo $_CB_framework->viewUrl( 'moderatebans' ); ?>"><?php echo ( $unbanRequestCount == 1 ? CBTxt::T( 'COUNT_UNBAN_REQUEST', '[count] Unban Request', array( '[count]' => $unbanRequestCount ) ) : CBTxt::T( 'COUNT_UNBAN_REQUESTS', '[count] Unban Requests', array( '[count]' => $unbanRequestCount ) ) ); ?></a>
		</li>
	<?php } ?>
	<?php if ( $showUserApproval ) { ?>
		<li class="cbModeratorLink cbModeratorLinkUserApproval">
			<a href="<?php echo $_CB_framework->viewUrl( 'pendingapprovaluser' ); ?>"><?php echo ( $userApprovalCount == 1 ? CBTxt::T( 'COUNT_USER_APPROVAL', '[count] User Approval', array( '[count]' => $userApprovalCount ) ) : CBTxt::T( 'COUNT_USER_APPROVALS', '[count] User Approvals', array( '[count]' => $userApprovalCount ) ) ); ?></a>
		</li>
	<?php } ?>
	<?php if ( $showPrivateMessages ) { ?>
		<li class="cbModeratorLink cbModeratorLinkPrivateMessages">
			<a href="<?php echo $privateMessageURL; ?>"><?php echo ( $newMessageCount == 1 ? CBTxt::T( 'COUNT_PRIVATE_MESSAGE', '[count] Private Message', array( '[count]' => $newMessageCount ) ) : CBTxt::T( 'COUNT_PRIVATE_MESSAGES', '[count] Private Messages', array( '[count]' => $newMessageCount ) ) ); ?></a>
		</li>
	<?php } ?>
	<?php if ( $showConnectionRequests ) { ?>
		<li class="cbModeratorLink cbModeratorLinkConnectionRequests">
			<a href="<?php echo $_CB_framework->viewUrl( 'manageconnections' ); ?>"><?php echo ( $newConnectionRequests == 1 ? CBTxt::T( 'COUNT_CONNECTION_REQUEST', '[count] Connection Request', array( '[count]' => $newConnectionRequests ) ) : CBTxt::T( 'COUNT_CONNECTION_REQUESTS', '[count] Connection Requests', array( '[count]' => $newConnectionRequests ) ) ); ?></a>
		</li>
	<?php } ?>
	<?php echo modCBModeratorHelper::getPlugins( $params, 'afterLinks' ); ?>
</ul>
<?php echo modCBModeratorHelper::getPlugins( $params, 'almostEnd' ); ?>
<?php if ( $postText ) { ?>
	<div class="posttext">
		<p><?php echo $postText; ?></p>
	</div>
<?php } ?>
<?php echo modCBModeratorHelper::getPlugins( $params, 'end' ); ?>