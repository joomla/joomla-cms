<?php
/**
 * Main Plugin File
 * Does all the magic!
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

$app = JFactory::getApplication();
if ($app->isAdmin()) {
	// load the NoNumber Framework language file
	$lang = JFactory::getLanguage();
	if ($lang->getTag() != 'en-GB') {
		// Loads English language file as fallback (for undefined stuff in other language file)
		$lang->load('plg_system_nnframework', JPATH_ADMINISTRATOR, 'en-GB');
	}
	$lang->load('plg_system_nnframework', JPATH_ADMINISTRATOR, null, 1);
}

if ($app->isSite() && JRequest::getCmd('option') == 'com_search') {
	$classes = get_declared_classes();
	if (!in_array('SearchModelSearch', $classes) && !in_array('SearchModelSearch', $classes)) {
		require_once JPATH_PLUGINS.'/system/nnframework/helpers/search.php';
	}
}

/**
 * Plugin that loads Framework
 */
class plgSystemNNFramework extends JPlugin
{
	var $_version = '12.6.4';

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$app = JFactory::getApplication();
		if ($app->isSite()) {
			return;
		}

		$template = $app->getTemplate();
		if ($template == 'adminpraise3') {
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root(true).'/plugins/system/nnframework/css/ap3.css?v='.$this->_version);
		}
		if (in_array(JRequest::getCmd('option'), array(
			'com_advancedmodules',
			'com_contenttemplater',
			'com_nonumbermanager',
			'com_rereplacer',
			'com_snippets',
		))
		) {
			$document = JFactory::getDocument();
			$document->addScriptDeclaration('var is_nn = 1;');
		}
	}

	function onAfterRoute()
	{
		if (!JRequest::getInt('nn_qp')) {
			return;
		}

		// Include the Helper
		require_once JPATH_PLUGINS.'/system/nnframework/helper.php';
		$this->helper = new plgSystemNNFrameworkHelper;
	}
}