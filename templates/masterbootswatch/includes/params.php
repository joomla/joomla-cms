<?php
	/*------------------------------------------------------------------------
# author    Gonzalo Suez
# copyright Copyright © 2013 gsuez.cl. All rights reserved.
# @license  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website   http://www.gsuez.cl
-------------------------------------------------------------------------*/
	defined('_JEXEC') or die;
	// Getting params from template
	$params = JFactory::getApplication()->getTemplate(true)->params;
	$app = JFactory::getApplication();
	$doc = JFactory::getDocument();
	// Column widths
	$leftcolgrid = ($this->countModules('left') == 0) ? 0 :
	$this->params->get('leftColumnWidth', 3);
	$rightcolgrid = ($this->countModules('right') == 0) ? 0 :
	$this->params->get('rightColumnWidth', 3);
	// Add javascript files
	// Include all compiled plugins (below), or include individual files as needed
	$doc->addScript('templates/' . $this->template . '/js/holder.js');
	//$doc->addScript('templates/' . $this->template . '/js/dropdown.js');
	//$doc->addScript('templates/' . $this->template . '/js/bootswatch.js');
	//$doc->addScript('templates/' . $this->template . '/js/tooltip.js');
	//$doc->addScript('templates/' . $this->template . '/js/popover.js');
	//$doc->addScript('templates/' . $this->template . '/js/modal.js');
	// Add Stylesheets
	$doc->addStyleSheet('templates/' . $this->template . '/css/icons.css');
	$doc->addStyleSheet('templates/' . $this->template . '/css/template.css');
	// Disable mootools
	//unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-more.js']);
	//unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-core.js']);
	//unset($doc->_scripts[JURI::root(true) . '/media/system/js/modal.js']);
	//unset($doc->_scripts[JURI::root(true) . '/media/system/js/caption.js']);
	// Add bootswatch styles
	$style = $this->params->get('style');
	if (!empty($style)){
		$doc->addStyleSheet('templates/' . $this->template . '/css/' . $style . '/bootstrap.min.css');
	} else {
		$doc->addStyleSheet('templates/' . $this->template . '/css/bootstrap.min.css');
	}
	// Variables
	$headdata = $doc->getHeadData();
	$menu = $app->getMenu();
	$active = $app->getMenu()->getActive();
	$pageclass = $params->get('pageclass_sfx');
	$tpath = $this->baseurl . '/templates/' . $this->template;
	// Parameter
	$frontpageshow = $this->params->get('frontpageshow', 0);
	$modernizr = $this->params->get('modernizr');
	$fontawesome = $this->params->get('fontawesome');
	$pie = $this->params->get('pie');
	// Generator tag
	$this->setGenerator(null);
	// Force latest IE & chrome frame
	$doc->setMetadata('x-ua-compatible', 'IE=edge,chrome=1');
	// Add javascripts
	if ($modernizr == 1){
		$doc->addScript($tpath . '/js/modernizr-2.8.3.js');
	}
	// Add stylesheets
	if ($fontawesome == 1){
		$doc->addStyleSheet($tpath . '/css/font-awesome.min.css');
	}