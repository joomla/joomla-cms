<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Joomla\CMS\Toolbar;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\Button\ConfirmButton;
use Joomla\CMS\Toolbar\Button\CustomButton;
use Joomla\CMS\Toolbar\Button\HelpButton;
use Joomla\CMS\Toolbar\Button\LinkButton;
use Joomla\CMS\Toolbar\Button\PopupButton;
use Joomla\CMS\Toolbar\Button\SeparatorButton;
use Joomla\CMS\Toolbar\Button\StandardButton;
use Joomla\CMS\Uri\Uri;

/**
 * CoreButtonsTrait
 *
 * @since  {DEPLOY_VERSION}
 */
trait CoreButtonsTrait
{
	public function divider(string $text = ''): SeparatorButton
	{
		return $this->separatorButton('divider', $text);
	}

	public function preview(string $url, string $text = 'JGLOBAL_PREVIEW'): PopupButton
	{
		return $this->popupButton('preview', $text)
			->url($url)
			->iframeWidth(640)
			->iframeHeight(480)
			->icon('icon-eye');
	}

	public function help($ref, $useComponent = false, $url = null, $component = null): HelpButton
	{
		return $this->helpButton('help', 'JTOOLBAR_HELP')
			->ref($ref)
			->useComponent($useComponent)
			->url($url)
			->component($component);
	}

	public function back(string $text = 'JTOOLBAR_BACK'): LinkButton
	{
		return $this->link('back', $text)
			->url('javascript:history.back();');
	}

	public function link(string $text, string $url): LinkButton
	{
		return $this->linkButton('link', $text)
			->url($url);
	}

	public function mediaManager(string $directory, string $text = 'JTOOLBAR_UPLOAD'): PopupButton
	{
		return $this->popupButton('upload', $text)
			->iframeWidth(800)
			->iframeHeight(520)
			->url('index.php?option=com_media&tmpl=component&task=popupUpload&folder=' . $directory);
	}

	public function makeDefault($task, string $text = 'JTOOLBAR_DEFAULT'): StandardButton
	{
		return $this->standardButton('default', $text)
			->task($task);
	}

	public function assign($task, string $text = 'JTOOLBAR_ASSIGN'): StandardButton
	{
		return $this->standardButton('assign', $text)
			->task($task);
	}

	public function addNew($task, string $text = 'JTOOLBAR_NEW'): StandardButton
	{
		return $this->standardButton('new', $text)
			->task($task);
	}

	public function publish($task, string $text = 'JTOOLBAR_PUBLISH'): StandardButton
	{
		return $this->standardButton('publish', $text)
			->task($task);
	}

	public function unpublish($task, string $text = 'JTOOLBAR_UNPUBLISH'): StandardButton
	{
		return $this->standardButton('unpublish', $text)
			->task($task);
	}

	public function archive($task, string $text = 'JTOOLBAR_ARCHIVE'): StandardButton
	{
		return $this->standardButton('archive', $text)
			->task($task);
	}

	public function unarchive($task, string $text = 'JTOOLBAR_UNARCHIVE'): StandardButton
	{
		return $this->standardButton('unarchive', $text)
			->task($task);
	}

	public function edit($task, string $text = 'JTOOLBAR_EDIT'): StandardButton
	{
		return $this->standardButton('edit', $text)
			->task($task);
	}

	public function editHtml($task, string $text = 'JTOOLBAR_EDIT_HTML'): StandardButton
	{
		return $this->standardButton('edithtml', $text)
			->task($task);
	}

	public function editCss($task, string $text = 'JTOOLBAR_EDIT_CSS'): StandardButton
	{
		return $this->standardButton('editcss', $text)
			->task($task);
	}

	public function delete($task, string $text = 'JTOOLBAR_DELETE'): ConfirmButton
	{
		return $this->confirmButton('delete', $text)
			->task($task);
	}

	public function trash($task, string $text = 'JTOOLBAR_TRASH'): StandardButton
	{
		return $this->standardButton('trash', $text)
			->task($task);
	}

	public function apply($task, string $text = 'JTOOLBAR_APPLY'): StandardButton
	{
		return $this->standardButton('apply', $text)
			->task($task);
	}

	public function save($task, string $text = 'JTOOLBAR_SAVE'): StandardButton
	{
		return $this->standardButton('save', $text)
			->task($task);
	}

	public function save2new($task, string $text = 'JTOOLBAR_SAVE_AND_NEW'): StandardButton
	{
		return $this->standardButton('save-new', $text)
			->task($task);
	}

	public function save2copy($task, string $text = 'JTOOLBAR_SAVE_AS_COPY'): StandardButton
	{
		return $this->standardButton('save-copy', $text)
			->task($task);
	}

	public function checkin($task, string $text = 'JTOOLBAR_CHECKIN'): StandardButton
	{
		return $this->standardButton('checkin', $text)
			->task($task);
	}

	public function cancel($task, string $text = 'JTOOLBAR_CLOSE'): StandardButton
	{
		return $this->standardButton('cancel', $text)
			->task($task);
	}

	public function preferences($component, string $text = 'JToolbar_Options', $path = ''): LinkButton
	{
		$component = urlencode($component);
		$path = urlencode($path);

		$uri = (string) Uri::getInstance();
		$return = urlencode(base64_encode($uri));

		return $this->linkButton('options', $text)
			->url('index.php?option=com_config&amp;view=component&amp;component=' . $component . '&amp;path=' . $path . '&amp;return=' . $return);
	}

	public function versions($typeAlias, $itemId, $height = 800, $width = 500, string $text = 'JTOOLBAR_VERSIONS'): CustomButton
	{
		$lang = Factory::getLanguage();
		$lang->load('com_contenthistory', JPATH_ADMINISTRATOR, $lang->getTag(), true);

		/** @var \Joomla\CMS\Table\ContentType $contentTypeTable */
		$contentTypeTable = Table::getInstance('Contenttype');
		$typeId           = $contentTypeTable->getTypeId($typeAlias);

		// Options array for JLayout
		$options              = array();
		$options['title']     = \JText::_($text);
		$options['height']    = $height;
		$options['width']     = $width;
		$options['itemId']    = $itemId;
		$options['typeId']    = $typeId;
		$options['typeAlias'] = $typeAlias;

		$layout = new FileLayout('joomla.toolbar.versions');

		return $this->customHtml($layout->render($options), 'version');
	}

	public function customHtml(string $html, string $name = 'custom'): CustomButton
	{
		$this->customButton($name)
			->html($html);
	}
}
