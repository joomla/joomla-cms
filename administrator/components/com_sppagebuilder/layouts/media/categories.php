<?php
defined('_JEXEC') or die();
$categories = $displayData['categories'];
$media_categories = '';

$app = JFactory::getApplication();
$support = $app->input->post->get('support', 'image', 'STRING');
$type = $app->input->post->get('type', '*', 'STRING');

$media_categories .= '<li'. (($type == '*') ? ' class="active"' : '') .'><a href="#" class="sp-pagebuilder-browse-media sp-pagebuilder-browse-all" data-type="*"><i class="fa fa-files-o fa-fw"></i> All Items <span>'. ((isset($categories['all']) && $categories['all']) ? $categories['all'] : 0) .'</span></a></li>';
$media_categories .= '<li'. (($type == 'image') ? ' class="active"' : '') .'><a href="#" class="sp-pagebuilder-browse-media sp-pagebuilder-browse-image" data-type="image"><i class="fa fa-picture-o fa-fw"></i> Images <span>'. ((isset($categories['image']) && $categories['image']) ? $categories['image'] : 0) .'</span></a></li>';
$media_categories .= '<li'. (($type == 'video') ? ' class="active"' : '') .'><a href="#" class="sp-pagebuilder-browse-media sp-pagebuilder-browse-video" data-type="video"><i class="fa fa-film fa-fw"></i> Videos <span>'. ((isset($categories['video']) && $categories['video']) ? $categories['video'] : 0) .'</span></a></li>';
$media_categories .= '<li'. (($type == 'audio') ? ' class="active"' : '') .'><a href="#" class="sp-pagebuilder-browse-media sp-pagebuilder-browse-audio" data-type="audio"><i class="fa fa-music fa-fw"></i> Audios <span>'. ((isset($categories['audio']) && $categories['audio']) ? $categories['audio'] : 0) .'</span></a></li>';
$media_categories .= '<li'. (($type == 'attachment') ? ' class="active"' : '') .'><a href="#" class="sp-pagebuilder-browse-media sp-pagebuilder-browse-attachment" data-type="attachment"><i class="fa fa-paperclip fa-fw"></i> Attachments <span>'. ((isset($categories['attachment']) && $categories['attachment']) ? $categories['attachment'] : 0) .'</span></a></li>';
if($support == 'image') {
	$media_categories .= '<li><a href="#" class="sp-pagebuilder-browse-media sp-pagebuilder-browse-folders" data-type="folders"><i class="fa fa-folder-open-o fa-fw"></i> Browse Folders <span>...</span></a></li>';
}

echo $media_categories;
