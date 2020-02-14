<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();
?>

<?php if( $displayData['params']->get('comment_intensedebate_acc') != '' ) : ?>
	
	<script>
		var idcomments_acct = '<?php echo $displayData["params"]->get("comment_intensedebate_acc"); ?>';
		var idcomments_post_id = '<?php echo md5( $displayData["url"] ); ?>';
		var idcomments_post_url = '<?php echo $displayData["url"]; ?>';
	</script>
	<span id="IDCommentsPostTitle" style="display:none"></span>
	<script type='text/javascript' src='https://www.intensedebate.com/js/genericCommentWrapperV2.js'></script>

<?php endif; ?>