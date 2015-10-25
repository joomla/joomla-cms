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
<?php echo modCBModeratorHelper::getPlugins( $params, 'start', 'span', 1 ); ?>
<?php if ( $preText ) { ?>
	<span class="pretext"><?php echo $preText; ?></span>
<?php } ?>
<?php echo modCBModeratorHelper::getPlugins( $params, 'almostStart', 'span', 1 ); ?>
<span class="cbModeratorLinks">
	<?php echo modCBModeratorHelper::getPlugins( $params, 'beforeLinks', 'span', 1, null, '&nbsp;' ); ?>
	<?php if ( $showBanned ) { ?>
		<span class="cbModeratorLink cbModeratorLinkBanned">
			<a href="<?php echo $_CB_framework->userProfileUrl(); ?>"><?php echo ( $bannedStatus == 1 ? CBTxt::T( 'Profile Banned' ) : CBTxt::T( 'Unban Request Pending' ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php if ( $showImageApproval ) { ?>
		<span class="cbModeratorLink cbModeratorLinkImageApproval">
			<a href="<?php echo $_CB_framework->viewUrl( 'moderateimages' ); ?>"><?php echo ( $imageApprovalCount == 1 ? CBTxt::T( 'COUNT_IMAGE_APPROVAL', '[count] Image Approval', array( '[count]' => $imageApprovalCount ) ) : CBTxt::T( 'COUNT_IMAGE_APPROVALS', '[count] Image Approvals', array( '[count]' => $imageApprovalCount ) ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php if ( $showUserReports ) { ?>
		<span class="cbModeratorLink cbModeratorLinkUserReports">
			<a href="<?php echo $_CB_framework->viewUrl( 'moderatereports' ); ?>"><?php echo ( $userReportsCount == 1 ? CBTxt::T( 'COUNT_PROFILE_REPORT', '[count] Profile Report', array( '[count]' => $userReportsCount ) ) : CBTxt::T( 'COUNT_PROFILE_REPORTS', '[count] Profile Reports', array( '[count]' => $userReportsCount ) ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php if ( $showUnbanRequests ) { ?>
		<span class="cbModeratorLink cbModeratorLinkUnbanRequests">
			<a href="<?php echo $_CB_framework->viewUrl( 'moderatebans' ); ?>"><?php echo ( $unbanRequestCount == 1 ? CBTxt::T( 'COUNT_UNBAN_REQUEST', '[count] Unban Request', array( '[count]' => $unbanRequestCount ) ) : CBTxt::T( 'COUNT_UNBAN_REQUESTS', '[count] Unban Requests', array( '[count]' => $unbanRequestCount ) ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php if ( $showUserApproval ) { ?>
		<span class="cbModeratorLink cbModeratorLinkUserApproval">
			<a href="<?php echo $_CB_framework->viewUrl( 'pendingapprovaluser' ); ?>"><?php echo ( $userApprovalCount == 1 ? CBTxt::T( 'COUNT_USER_APPROVAL', '[count] User Approval', array( '[count]' => $userApprovalCount ) ) : CBTxt::T( 'COUNT_USER_APPROVALS', '[count] User Approvals', array( '[count]' => $userApprovalCount ) ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php if ( $showPrivateMessages ) { ?>
		<span class="cbModeratorLink cbModeratorLinkPrivateMessages">
			<a href="<?php echo $privateMessageURL; ?>"><?php echo ( $newMessageCount == 1 ? CBTxt::T( 'COUNT_PRIVATE_MESSAGE', '[count] Private Message', array( '[count]' => $newMessageCount ) ) : CBTxt::T( 'COUNT_PRIVATE_MESSAGES', '[count] Private Messages', array( '[count]' => $newMessageCount ) ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php if ( $showConnectionRequests ) { ?>
		<span class="cbModeratorLink cbModeratorLinkConnectionRequests">
			<a href="<?php echo $_CB_framework->viewUrl( 'manageconnections' ); ?>"><?php echo ( $newConnectionRequests == 1 ? CBTxt::T( 'COUNT_CONNECTION_REQUEST', '[count] Connection Request', array( '[count]' => $newConnectionRequests ) ) : CBTxt::T( 'COUNT_CONNECTION_REQUESTS', '[count] Connection Requests', array( '[count]' => $newConnectionRequests ) ) ); ?></a>
		</span>
		&nbsp;
	<?php } ?>
	<?php echo modCBModeratorHelper::getPlugins( $params, 'afterLinks', 'span', 1, null, '&nbsp;' ); ?>
</span>
<?php echo modCBModeratorHelper::getPlugins( $params, 'almostEnd', 'span', 1 ); ?>
<?php if ( $postText ) { ?>
	<span class="posttext"><?php echo $postText; ?></span>
<?php } ?>
<?php echo modCBModeratorHelper::getPlugins( $params, 'end', 'span', 1 ); ?>