<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

if( $displayData['params']->get('comment_disqus_subdomain') != '' )
{
	$doc = JFactory::getDocument();

	if(!defined('HELIX_ULTIMATE_COMMENTS_DISQUS_COUNT'))
	{
		ob_start();

		$devmode = $displayData['params']->get('comment_disqus_devmode');
		
		if ($devmode)
		{
			echo 'var disqus_developer = 1;';
		}

		?>
		var disqus_shortname = '<?php echo $displayData['params']->get("comment_disqus_subdomain"); ?>';

		(function() { 
			var d = document, s = d.createElement('script');
			s.src = 'https://' + disqus_shortname + '.disqus.com/count.js';
			s.setAttribute('data-timestamp', +new Date());
			(d.head || d.body).appendChild(s);
		})();

		<?php

		$output = ob_get_clean();

		$doc->addScriptdeclaration( $output );

		define('HELIX_ULTIMATE_COMMENTS_DISQUS_COUNT', 1);
	}
	?>
	<a href="<?php echo $displayData['url']; ?>#article-comments">
		<span class="disqus-comment-count" data-disqus-url="<?php echo $displayData['url']; ?>"></span>
	</a>
	<?php
}
