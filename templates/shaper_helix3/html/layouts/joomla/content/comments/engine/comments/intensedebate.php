<?php
/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2015 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/
//no direct access
defined('_JEXEC') or die('Restricted Access');

if( $displayData['params']->get('intensedebate_acc') != '' ) {
	?>
	
	<script>
	var idcomments_acct = '<?php echo $displayData["params"]->get("intensedebate_acc"); ?>';
	var idcomments_post_id = '<?php echo md5( $displayData["url"] ); ?>';
	var idcomments_post_url = '<?php echo $displayData["url"]; ?>';
	</script>
	<span id="IDCommentsPostTitle" style="display:none"></span>
	<script type='text/javascript' src='http://www.intensedebate.com/js/genericCommentWrapperV2.js'></script>

	<?php
}