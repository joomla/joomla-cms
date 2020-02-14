<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.helper');

$cParams = JComponentHelper::getParams('com_sppagebuilder');

$app = JFactory::getApplication();
$input = $app->input;

// Load Page Template List
if ( $action === 'pre-page-list' ) {

	jimport( 'joomla.filesystem.folder' );
	$cache_path = JPATH_CACHE . '/sppagebuilder';
	$cache_file = $cache_path . '/templates.json';

	$output = array('status' => false, 'data' => 'Templates not found.');
	$templates = array(); // All pre-defined templates list
	$templatesData = '';

	if(!file_exists($cache_path)) {
		JFolder::create($cache_path, 0755);
	}

	if (file_exists($cache_file) && (filemtime($cache_file) > (time()  - (24 * 60 * 60) ))) {
		$templatesData = file_get_contents($cache_file);
	} else {
		// $templateApi = 'https://sppagebuilder.com/api/templates/templates.php';
		$templateApi = 'https://www.joomshaper.com/index.php?option=com_layouts&view=templates&layout=json';

		if(extension_loaded('curl')){
			$headers = array();
			$headers[] = "Content-Type: text/html";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_URL, $templateApi);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$templatesData = curl_exec($ch);
			curl_close($ch);
		} else if(ini_get('allow_url_fopen')){
			$opts = array(
				'http' => array(
					'method'  => 'GET',
					'header'  => "Content-Type: text/html",
					'timeout' => 60
				)
			);
			$context  = stream_context_create($opts);
			$templatesData = file_get_contents($templateApi, false, $context);
		} else {
			$output = array('status' => false, 'data' => 'Please enable \'cURL\' or url_fopen in PHP or contact with your Server or Hosting administrator.');
		}

		if (!empty($templatesData)) {
			file_put_contents($cache_file, $templatesData, LOCK_EX);
		}
	}

	if (!empty($templatesData)) {
		$templates = json_decode($templatesData);

		if (count((array) $templates)) {
			$output['status'] = true;
			$output['data'] = $templates;
			echo json_encode($output); die();
		}
	}
	echo json_encode($output); die();
}

// Load Page Template List
if ( $action === 'get-pre-page-data' ) {

	$template = $input->post->get('template', '', 'STRING');
	$category = $input->post->get('category', '', 'STRING');

	$output = array('status' => false, 'data' => 'Page not found.');

	$args = '&email=' . $cParams->get('joomshaper_email') . '&api_key='.$cParams->get('joomshaper_license_key');
	$pageApi = 'https://sppagebuilder.com/api/templates/data.php?template=' . $template . '&category=' . $category.$args;

	$pageData = '';

	if(extension_loaded('curl')){
		$headers = array();
		$headers[] = "Content-Type: text/html";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $pageApi);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$pageData = curl_exec($ch);
		curl_close($ch);
	} else if(ini_get('allow_url_fopen')){
		$opts = array(
			'http' => array(
				'method'  => 'GET',
				'header'  => "Content-Type: text/html",
				'timeout' => 60
			)
		);
		$context  = stream_context_create($opts);
		$pageData = file_get_contents($pageApi, false, $context);
	} else {
		$output = array('status' => false, 'data' => 'Please enable \'cURL\' or url_fopen in PHP or contact with your Server or Hosting administrator.');
	}

	if (!empty($pageData)) {
		$pageData = json_decode($pageData);
		
		if(isset($pageData->status) && $pageData->status){
			$output['status'] = true;
			$output['data'] = $pageData->content;
			echo json_encode($output); die();
		} elseif(isset($pageData->authorised)) {
			$output['status'] = false;
			$output['data'] = $pageData->authorised;
			echo json_encode($output); die();
		}

	}
	echo json_encode($output); die();
}

if ( $action === 'pre-section-list' ) {

	jimport( 'joomla.filesystem.folder' );
	$cache_path = JPATH_CACHE . '/sppagebuilder';
	$cache_file = $cache_path . '/sections.json';

	$output = array('status' => false, 'data' => 'Sections not found.');
	$sections = array(); // All pre-defined templates list
	$sectionsData = '';

	if(!file_exists($cache_path)) {
		JFolder::create($cache_path, 0755);
	}

	if ( file_exists($cache_file ) && ( filemtime($cache_file) > (time()  - (24 * 60 * 60) ))) {
		$sectionsData = file_get_contents($cache_file);
	} else {
		
		
		$args = '?email=' . $cParams->get('joomshaper_email') . '&api_key='.$cParams->get('joomshaper_license_key');
		$sectionApi = 'https://sppagebuilder.com/api/sections/sections.php'. $args;

		if(extension_loaded('curl')){
			$headers = array();
	    $headers[] = "Content-Type: text/html";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_URL, $sectionApi);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$sectionsData = curl_exec($ch);
			curl_close($ch);
		} else if(ini_get('allow_url_fopen')){
			$opts = array(
				'http' => array(
			    'method'  => 'GET',
			    'header'  => "Content-Type: text/html",
			    'timeout' => 60
			  )
			);
			$context  = stream_context_create($opts);
			$sectionsData = file_get_contents($sectionApi, false, $context);
		} else {
			$output = array('status' => false, 'data' => 'Please enable \'cURL\' or url_fopen in PHP or contact with your Server or Hosting administrator.');
		}

		if(!empty($sectionsData)){
			file_put_contents($cache_file, $sectionsData, LOCK_EX);
		}
	}


	if (!empty($sectionsData)) {
		$sections = json_decode($sectionsData);

		if (count((array) $sections)) {
			$output['status'] = true;
			$output['data'] = $sections;
			echo json_encode($output); die();
		}
	}

	echo json_encode($output); die();
}

// Load page from uploaded page
if ( $action === 'upload-page' ) {
	if ( isset($_FILES['page']) && $_FILES['page']['error'] === 0 ) {

		$file_name = $_FILES['page']['name'];
		$file_extension = substr( $file_name, -5 );
		$file_extension_lower = strtolower($file_extension);

		if ($file_extension_lower === '.json')
		{
			$content = file_get_contents($_FILES['page']['tmp_name']);
			if (is_array(json_decode($content))) {

				require_once JPATH_COMPONENT_ADMINISTRATOR . '/builder/classes/addon.php';
				$content = SpPageBuilderAddonHelper::__($content);

				// Check frontend editing
				if ($input->get('editarea', '', 'STRING') == 'frontend') {
					$content = SpPageBuilderAddonHelper::getFontendEditingPage($content);
				}
				echo json_encode( array('status' => true, 'data' => $content) ); die;
			}
		}

	}

	echo json_encode(array('status'=> false, 'data'=>'Something worng there.')); die;
}
