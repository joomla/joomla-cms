<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

if ( $items ) {
	$itemCount	=	0;

?>
	<div class="cb_template cb_template_<?php echo selectTemplate( 'dir' ); ?>">
		<div class="cbFeed">
<?php
		foreach ( $items as $index => $item ) {
			$itemCount++;

			?>
				<div class="cbFeedItem<?php echo ( $modalDisplay ? ' cbTooltip' : null ) . ( $feedEntries && ( $index >= $feedEntries ) ? ' cbFeedItemDisabled' : null ); ?>" <?php echo ( $modalDisplay ? cbTooltip( $_CB_framework->getUi(), modCBAdminHelper::longDescription( $item->description ), $item->title, array( $modalWidth, $modalHeight ), null, null, null, 'data-cbtooltip-modal="true" data-cbtooltip-callback-show="cbFeedShow" data-cbtooltip-callback-hide="cbFeedHide"' ) : null ); ?>>
					<div class="media">
						<a class="pull-left" href="#">
							<div class="cbFeedItemLogo media-object"><?php echo modCBAdminHelper::descriptionIcon( $item->description ); ?></div>
						</a>
						<div class="media-body">
							<h4 class="cbFeedItemTitle media-heading"><a href="<?php echo htmlspecialchars( $item->link ); ?>" target="_blank"><strong><?php echo $item->title; ?></strong></a></h4>
							<div class="cbFeedItemDesc"><?php echo modCBAdminHelper::shortDescription( $item->description, 200 ); ?></div>
							<small class="cbFeedItemDate"><?php echo cbFormatDate( $item->pubDate, true, 'timeago' ); ?></small>
						</div>
					</div>
				</div>
			<?php

			if ( $feedEntries ) {
				if ( ( $itemCount >= $feedEntries ) && ( ( $index + 1 ) != count( $items ) ) ) {
					?><button type="button" class="btn btn-primary btn-lg btn-block cbFeedShowMore"><?php echo CBTxt::T( 'More' ); ?></button><?php

					$itemCount	=	0;
				}
			}
		}
?>
		</div>
	</div>
<?php
} else {
?>
	<div class="cb_template cb_template_<?php echo selectTemplate( 'dir' ); ?>">
		<div class="cbFeed"><?php echo CBTxt::T( 'There currently is no news.' ); ?></div>
	</div>
<?php
}
?>