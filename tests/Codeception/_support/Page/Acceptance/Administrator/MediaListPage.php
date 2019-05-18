<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class for media manager list page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class MediaListPage extends AdminListPage
{
	/**
	 * Url to media manager listing page.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $url = "administrator/index.php?option=com_media&path=local-0:/";

	/**
	 * Page title of the media manager listing page.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $pageTitleText = 'Media';

	/**
	 * Page title of the media manager listing page.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $container = ['class' => 'media-container'];

	/**
	 * Page title of the media manager listing page.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $infoBar = ['class' => 'media-infobar'];

	/**
	 * The media browser items
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemsContainer = ['class' => 'media-browser-items'];

	/**
	 * The media browser items extra small
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemsContainerExtraSmall = ['class' => 'media-browser-items-xs'];

	/**
	 * The media browser items small
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemsContainerSmall = ['class' => 'media-browser-items-sm'];

	/**
	 * The media browser items medium
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemsContainerMedium = ['class' => 'media-browser-items-md'];

	/**
	 * The media browser items large
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemsContainerLarge = ['class' => 'media-browser-items-lg'];

	/**
	 * The media browser items extra large
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemsContainerExtraLarge = ['class' => 'media-browser-items-xl'];

	/**
	 * The media browser items
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $items = ['class' => 'media-browser-item'];

	/**
	 * The media browser items selected locator
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $itemSelected = ['css' => '.media-browser-item.selected'];

	/**
	 * The media tree
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $mediaTree = ['class' => 'media-tree'];

	/**
	 * The media tree
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $mediaBreadcrumb = ['class' => 'media-breadcrumb'];

	/**
	 * Button that toggles the info bar
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $toggleInfoBarButton = ['class' => 'media-toolbar-info'];

	/**
	 * The hidden file upload field
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $fileInputField = 'input[name=\'file\']';

	/**
	 * The create folder button in the toolbar
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $toolbarCreateFolderButton = '//button[contains(@onclick, \'onClickCreateFolder\')]';

	/**
	 * The delete button in the toolbar
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $toolbarDeleteButton = '//button[contains(@onclick, \'onClickDelete\')]';

	/**
	 * The delete button in the toolbar
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $toolbarModalDeleteButton = ['id' => 'media-delete-item'];

	/**
	 * The select all button
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $selectAllButton = ['class' => 'media-toolbar-select-all'];

	/**
	 * The increase thumbnail size button
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $increaseThumbnailSizeButton = ['class' => 'media-toolbar-increase-grid-size'];

	/**
	 * The disabled increase thumbnail size button disabled
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $increaseThumbnailSizeButtonDisabled = ['css' => '.media-toolbar-increase-grid-size.disabled'];

	/**
	 * The decrease thumbnail size button
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $decreaseThumbnailSizeButton = ['class' => 'media-toolbar-decrease-grid-size'];

	/**
	 * The decrease thumbnail size button disabled
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $decreaseThumbnailSizeButtonDisabled = ['css' => '.media-toolbar-decrease-grid-size.disabled'];

	/**
	 * The disabled increase thumbnail size button disabled
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $toggleListViewButton = ['class' => 'media-toolbar-list-view'];

	/**
	 * The item actions
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $itemActions = ['class' => 'media-browser-actions'];

	/**
	 * The rename action
	 *
	 * @var string
	 * @since  4.0.0
	 */
	public static $renameAction = 'action-rename';

	/**
	 * The rename action
	 *
	 * @var string
	 * @since  4.0.0
	 */
	public static $previewAction = 'action-preview';

	/**
	 * The rename action
	 *
	 * @var string
	 * @since  4.0.0
	 */
	public static $editAction = 'action-edit';

	/**
	 * The name field of modal forms
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $renameInputField = ['id' => 'name'];

	/**
	 * The name field of modal forms
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $newFolderInputField = ['id' => 'folder'];

	/**
	 * The confirm button of modals
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $modalConfirmButton = ['css' => '.modal button.btn-success'];

	/**
	 * The confirm button of modals
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $modalConfirmButtonDisabled = ['css' => '.modal button:disabled.btn-success'];

	/**
	 * The preview modal
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $previewModal = ['class' => 'media-preview-modal'];

	/**
	 * The preview modal image locator
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $previewModalImg = ['css' => '.media-preview-modal img'];

	/**
	 * The preview modal image locator
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $previewModalCloseButton = ['class' => 'media-preview-close'];

	/**
	 * The media browser grid
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $mediaBrowserGrid = ['class' => 'media-browser-grid'];

	/**
	 * The media browser table
	 *
	 * @var array
	 * @since  4.0.0
	 */
	public static $mediaBrowserTable = ['class' => 'media-browser-table'];

	/**
	 * The key for the app storage
	 *
	 * @var string
	 * @since  4.0.0
	 */
	public static $loader = ['class' => 'media-loader'];

	/**
	 * The key for the app storage
	 *
	 * @var string
	 * @since  4.0.0
	 */
	public static $storageKey = 'joomla.mediamanager';

	/**
	 * Dynamic locator for media item files
	 *
	 * @var    string $name
	 *
	 * @since  4.0.0
	 *
	 * @return string
	 */
	public static function item($name)
	{
		return self::itemXpath($name);
	}

	/**
	 * Dynamic locator for media item action
	 *
	 * @var    string $itemName
	 * @var    string $actionName
	 *
	 * @since  4.0.0
	 *
	 * @return string
	 */
	public static function itemActionMenuToggler($itemName)
	{
		return self::itemXpath($itemName) . '//button[@class= \'action-toggle\']';
	}

	/**
	 * Dynamic locator for media item action
	 *
	 * @var    string $itemName
	 * @var    string $actionName
	 *
	 * @since  4.0.0
	 *
	 * @return string
	 */
	public static function itemAction($itemName, $actionName)
	{
		return self::itemXpath($itemName) . '//button[@class= \'' . $actionName . '\']';
	}

	/**
	 * Get the xpath of a media item
	 *
	 * @var    string $name
	 * @since  4.0.0
	 *
	 * @return string
	 */
	protected static function itemXpath($name)
	{
		return '//div[contains(@class, \'media-browser-item-info\') and normalize-space(text()) = \'' . $name . '\']/parent::div';
	}
}
