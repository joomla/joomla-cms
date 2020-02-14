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

	?>

	<div id="disqus_thread"></div>
	<script>

	<?php
	$devmode = $displayData['params']->get('comment_disqus_devmode');
	if ($devmode)
	{
		echo 'var disqus_developer = 1;';
	}
	?>

	var disqus_shortname = '<?php echo htmlspecialchars($displayData["params"]->get("comment_disqus_subdomain")); ?>';
	var disqus_config = function () {
        this.page.url = "<?php echo $displayData['url']; ?>";
    };

	(function() { 
        var d = document, s = d.createElement('script');
		s.src = 'https://' + disqus_shortname + '.disqus.com/embed.js';
        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);
	})();
	
	</script>
	<noscript>
		Please enable JavaScript to view the 
		<a href="https://disqus.com/?ref_noscript" rel="nofollow">
			comments powered by Disqus.
		</a>
	</noscript>

	<?php
}
