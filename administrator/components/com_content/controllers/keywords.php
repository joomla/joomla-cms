<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.theartofjoomla.com
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentControllerKeywords extends JController
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->registerTask('rebuild',	'rebuild');
	}

	/**
	 * Display the view
	 */
	function display()
	{
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Keywords', $prefix = 'ContentModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuilds the jos_content_keyword_article_map table
	 */
	function rebuild()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		$model = $this->getModel('keywords');
		$returnArray = $model->rebuild();
		$msg = JText::sprintf('KEYWORDS_REBUILD_SUCCESS', $returnArray[1], $returnArray[2]);
		$msgType = 'message';

		if ($returnArray[0] === false) {
			$msg = JText::_('Error re-building article keyword map table.');
			$msgType = 'error';
		}

		$this->setRedirect('index.php?option=com_content&view=keywords', $msg, $msgType);
	}
	/**
	 * Repairs the jos_content_keyword_article_map table
	 */
	function repair()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		$model = $this->getModel('keywords');
		$returnArray = $model->repair();
		$result = $returnArray['success'];
		$repaired = $returnArray['repaired'];
		$good = $returnArray['good'];
		$unmatched = $returnArray['unmatched'];
		$totalArticles = $repaired + $good;
		if (!$unmatched && !$repaired && $result) 
		{
			$msg = JText::sprintf('KEYWORDS_REPAIR_NOCHANGES', $totalArticles);
			$msgType = 'message';
		}
		else if ($result)
		{
			$msg = 	$msg = JText::sprintf('KEYWORDS_REPAIR_WITHCHANGES', $good, 
				$repaired, $unmatched);
			$msgType = 'message';
		}
		else 
		{
			$msg = JText::_('Error repairing article keyword map table.');
			$msgType = 'error';
		}

		$this->setRedirect('index.php?option=com_content&view=keywords', $msg, $msgType);
		
	}
}