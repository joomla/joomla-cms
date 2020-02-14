<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();
$width = ($displayData['params']->get('comment_facebook_width') == 100 ) ? '100%' : (int) $displayData['params']->get('comment_facebook_width');
?>

<?php if( $displayData['params']->get('comment_facebook_app_id') != '' ) : ?>
	
	<div id="fb-root"></div>
	
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.11&appId=<?php echo $displayData['params']->get('comment_facebook_app_id'); ?>&autoLogAppEvents=1';
		fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>

	<div class="fb-comments" data-href="<?php echo $displayData['url']; ?>" data-numposts="<?php echo (int) $displayData['params']->get('comment_facebook_number'); ?>" data-width="<?php echo $width; ?>" data-colorscheme="light"></div>
	
<?php endif; ?>