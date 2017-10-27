<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Contenthistory\Administrator\Model\History;

JLoader::register('ContenthistoryHelper', JPATH_ADMINISTRATOR . '/components/com_contenthistory/helpers/contenthistory.php');

JHtml::_('behavior.core');
JHtml::_('script', 'com_content/admin-article-showdiff.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'vendor/diff/diff.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'com_contenthistory/jquery.pretty-text-diff.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_content/admin-article-showdiff.js', array('version' => 'auto', 'relative' => true));

$document    = JFactory::getDocument();
$this->eName = JFactory::getApplication()->input->getCmd('e_name', '');
$this->eName = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $this->eName);

$document->setTitle(JText::_('COM_CONTENT_SHOWDIFF_DOC_TITLE'));

$input = JFactory::getApplication()->input;

/** @var Joomla\Component\Contenthistory\Administrator\Model\History $contentHistory */
$contentHistory = new History;

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
<!-- These Buttons toggle the shown text between one including HTML-Tags and one that doesn´t -->
<div>
	<button class="diff-header btn hasTooltip" title="<?php JText::_('COM_CONTENT_SHOWDIFF_BUTTON_COMPARE_HTML_DESC'); ?>">
		<span class="icon-wrench" aria-hidden="true"></span>
		<?php echo JText::_('COM_CONTENT_SHOWDIFF_BUTTON_COMPARE_HTML'); ?>
	</button>
	<button class="diffhtml-header btn hasTooltip" title="<?php echo JText::_('COM_CONTENT_SHOWDIFF_BUTTON_COMPARE_TEXT_DESC'); ?>"
			style="display:none">
		<span class="icon-pencil" aria-hidden="true"></span>
		<?php echo JText::_('COM_CONTENT_SHOWDIFF_BUTTON_COMPARE_TEXT'); ?>
	</button>
</div>

<?php
echo '<div id="diff_area" class="container-popup" style="height: auto">';

if (count($dbObject) > 1)
{
	$object = ContenthistoryHelper::decodeFields($dbObject[ $previousVersion ]->version_data);

	if ($object->fulltext != null)
	{
		echo $object->introtext . '<hr id="system-readmore" />' . $object->fulltext;
	}
	else
	{
		echo $object->introtext;
	}
}

echo '</div>';
