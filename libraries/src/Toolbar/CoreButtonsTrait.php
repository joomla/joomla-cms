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
	public function divider(string $name = 'divider', string $text = ''): SeparatorButton
	{
		return $this->separatorButton($name, $text);
	}

	public function preview(string $name = 'preview', string $text = 'JGLOBAL_PREVIEW'): PopupButton
	{
		return $this->popupButton($name, $text)
			->iframeWidth(640)
			->iframeHeight(480);
	}

	public function help(string $name = 'help', string $text = 'JTOOLBAR_HELP'): HelpButton
	{
		return $this->helpButton($name, $text);
	}

	public function back(string $name = 'back', string $text = 'JTOOLBAR_BACK'): LinkButton
	{
		return $this->link($name, $text)
			->url('javascript:history.back();');
	}

	public function link(string $name = 'link', string $text): LinkButton
	{
		return $this->linkButton($name, $text);
	}

	public function mediaManager($directory, string $name = 'upload', string $text = 'JTOOLBAR_UPLOAD'): PopupButton
	{
		return $this->popupButton($name, $text)
			->iframeWidth(800)
			->iframeHeight(520)
			->url('index.php?option=com_media&tmpl=component&task=popupUpload&folder=' . $directory);
	}

	public function makeDefault(string $name = 'default', string $text = 'JTOOLBAR_DEFAULT'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function assign(string $name = 'assign', string $text = 'JTOOLBAR_ASSIGN'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function addNew(string $name = 'new', string $text = 'JTOOLBAR_NEW'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function publish(string $name = 'publish', string $text = 'JTOOLBAR_PUBLISH'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function unpublish(string $name = 'unpublish', string $text = 'JTOOLBAR_UNPUBLISH'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function archive(string $name = 'archive', string $text = 'JTOOLBAR_ARCHIVE'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function unarchive(string $name = 'unarchive', string $text = 'JTOOLBAR_UNARCHIVE'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function edit(string $name = 'edit', string $text = 'JTOOLBAR_EDIT'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function editHtml(string $name = 'edithtml', string $text = 'JTOOLBAR_EDIT_HTML'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function editCss(string $name = 'editcss', string $text = 'JTOOLBAR_EDIT_CSS'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function delete(string $name = 'delete', string $text = 'JTOOLBAR_DELETE'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function trash(string $name = 'trash', string $text = 'JTOOLBAR_TRASH'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function apply(string $name = 'apply', string $text = 'JTOOLBAR_APPLY'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function save(string $name = 'save', string $text = 'JTOOLBAR_SAVE'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function save2new(string $name = 'save2new', string $text = 'JTOOLBAR_SAVE_AND_NEW'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function save2copy(string $name = 'save2copy', string $text = 'JTOOLBAR_SAVE_AS_COPY'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function checkin(string $name = 'checkin', string $text = 'JTOOLBAR_CHECKIN'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function cancel(string $name = 'cancel', string $text = 'JTOOLBAR_CANCEL'): StandardButton
	{
		return $this->standardButton($name, $text);
	}

	public function preferences($component, string $name = 'options', string $text = 'JToolbar_Options', $path = ''): LinkButton
	{
		$component = urlencode($component);
		$path = urlencode($path);

		$uri = (string) Uri::getInstance();
		$return = urlencode(base64_encode($uri));

		return $this->linkButton($name, $text)
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

		return $this->customButton('version', $text)
			->html($layout->render($options));
	}
}
