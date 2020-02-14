<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

extract($displayData);

if(isset($attribs->helix_ultimate_video) && $attribs->helix_ultimate_video)
{
	$video = parse_url($attribs->helix_ultimate_video);
	
	switch($video['host'])
	{
		case 'youtu.be':
		$video_id 	= trim($video['path'],'/');
		$video_src 	= '//www.youtube.com/embed/' . $video_id;
		break;
		case 'www.youtube.com':
		case 'youtube.com':
		parse_str($video['query'], $query);
		$video_id 	= $query['v'];
		$video_src 	= '//www.youtube.com/embed/' . $video_id;
		break;
		case 'vimeo.com':
		case 'www.vimeo.com':
		$video_id 	= trim($video['path'],'/');
		$video_src 	= "//player.vimeo.com/video/" . $video_id;
	}

	if($video_src) {
		?>
		<div class="article-featured-video">
			<div class="embed-responsive embed-responsive-16by9">
				<iframe class="embed-responsive-item" src="<?php echo $video_src; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
			</div>
		</div>
		<?php
	}
}
