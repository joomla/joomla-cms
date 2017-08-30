<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//use Joomla\CMS\Model\Model;
use Joomla\Component\Contenthistory\Administrator\Model\History;

JLoader::register('ContenthistoryHelper', JPATH_ADMINISTRATOR . '/components/com_contenthistory/helpers/contenthistory.php');

JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('script', 'com_content/admin-article-showdiff.min.js', array('version' => 'auto', 'relative' => true));


//JHtml::_('script', 'com_contenthistory/diff_match_patch.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'com_contenthistory/jquery.pretty-text-diff.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_content/admin-article-showdiff.js', array('version' => 'auto', 'relative' => true));

//$path  = JURI::root(true) . "/media/com_content/js/admin-article-showdiff.js";
//$path2 = JURI::root(true) . "/media/com_contenthistory/js/diff_match_patch.js";

$document    = JFactory::getDocument();
$this->eName = JFactory::getApplication()->input->getCmd('e_name', '');
$this->eName = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $this->eName);

$document->setTitle(JText::_('COM_CONTENT_PAGEBREAK_DOC_TITLE'));

$input = JFactory::getApplication()->input;
//JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_contenthistory/Model/');

/** @var Joomla\Component\Contenthistory\Administrator\Model\History $contentHistory */
//$contentHistory  = JModelLegacy::getInstance('History', 'Joomla/Component/Contenthistory/Administrator/Model/');
$contentHistory = new History;
//$contentHistory  = JModelLegacy::getInstance('History', '');

$itemId          = $contentHistory->getState('item_id', $input->get('id'));
$typeId          = $contentHistory->getState('type_id', 5);
$previousVersion = 1;

if ($itemId === 0)
{
	$itemId = $input->get('id');
}

if ($typeId === 0)
{
	$typeId = 1;
}

$contentHistory->setState('item_id', $itemId);
$contentHistory->setState('type_id', $typeId);
$dbObject = $contentHistory->getItems();

?>

<div id="diff_area" class="container-popup" style="height: auto"><?php
    if (count($dbObject) > 1)
	{
		$object = ContenthistoryHelper::decodeFields($dbObject[$previousVersion]->version_data);
		if ($object->fulltext != null)
		{
			echo $object->introtext . '<hr id="system-readmore" />' . $object->fulltext;

		}
		else
		{
			echo $object->introtext;
		}
	}
	?>
</div>



