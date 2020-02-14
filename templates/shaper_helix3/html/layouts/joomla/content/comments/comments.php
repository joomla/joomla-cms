<?php

//no direct access
defined('_JEXEC') or die('Restricted Access');

$params 	= JFactory::getApplication()->getTemplate(true)->params;

if( $params->get('commenting_engine') != 'disabled' ) {
	
	$url        =  JRoute::_(ContentHelperRoute::getArticleRoute($displayData->id . ':' . $displayData->alias, $displayData->catid, $displayData->language));
	$root       = JURI::base();
	$root       = new JURI($root);
	$url        = $root->getScheme() . '://' . $root->getHost() . $url;

	echo '<div id="sp-comments">';
	echo JLayoutHelper::render( 'joomla.content.comments.engine.comments.' . $params->get('commenting_engine'), array( 'item'=>$displayData, 'params'=>$params, 'url'=>$url ) );
	echo '</div>';
}