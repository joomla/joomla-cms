<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Page\Acceptance\Administrator\MediaFilePage;
use Page\Acceptance\Administrator\MediaListPage;
use Step\Acceptance\Administrator\Media;

/**
 * TODO test d&d upload of files
 * TODO test download of files
 * TODO enable skipped tests
 */

/**
 * Media manager list tests
 *
 * @since  4.0.0
 */
class MediaListCest
{
    /**
     * The default contents.
     *
     * @var array
     *
     * @since   4.0.0
     */
    private $contents = [
        'root' => [
            'banners',
            'headers',
            'sampledata',
            'joomla_black.png',
            'powered_by.png',
        ],
        '/banners' => [
            'banner.jpg',
            'osmbanner1.png',
            'osmbanner2.png',
            'shop-ad.jpg',
            'shop-ad-books.jpg',
            'white.png',
        ],
    ];

    /**
     * The name of the test directory, which gets deleted after each test.
     *
     * @var string
     *
     * @since   4.0.0
     */
    private $testDirectory = 'test-dir';

    /**
     * Runs before every test.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function _before(Media $I)
    {
        $I->doAdministratorLogin();

        // Create the test directory
        $I->createDirectory('images/' . $this->testDirectory);
    }

    /**
     * Runs after every test.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function _after(Media $I)
    {
        // Delete the test directory
        $I->deleteDirectory('images/' . $this->testDirectory);

        // Clear localstorage before every test
        $I->executeJS('window.sessionStorage.removeItem("' . MediaListPage::$storageKey . '");');
    }

    /**
     * Test that it loads without php notices and warnings.
     *
     * @param   AcceptanceTester  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function loadsWithoutPhpNoticesAndWarnings(AcceptanceTester $I)
    {
        $I->wantToTest('that it loads without php notices and warnings.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForText(MediaListPage::$pageTitleText);
        $I->checkForPhpNoticesOrWarnings();
    }

    /**
     * Test that it shows then joomla default media files and folders.
     *
     * @param   Media  $I
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function showsDefaultFilesAndFolders(Media $I)
    {
        $I->wantToTest('that it shows the joomla default media files and folders.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->seeElement(MediaListPage::$items);
        $I->seeContents($this->contents['root']);
    }

    /**
     * Test that it shows then joomla default media files and folders.
     *
     * @param   Media  $I
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function showsFilesAndFoldersOfASubdirectoryWhenOpenedUsingDeepLink(Media $I)
    {
        $I->wantToTest('that it shows the  media files and folders of a subdirectory when opened using deep link.');
        $I->amOnPage(MediaListPage::$url . 'banners');
        $I->waitForMediaLoaded();
        $I->seeElement(MediaListPage::$items);
        $I->seeContents($this->contents['/banners']);
    }

    /**
     * Test that it is possible to select a single file.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function selectSingleFile(Media $I)
    {
        $I->wantToTest('that it is possible to select a single file');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->click(MediaListPage::item('powered_by.png'));
        $I->seeNumberOfElements(MediaListPage::$itemSelected, 1);
    }

    /**
     * Test that it is possible to select a single file.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function selectSingleFolder(Media $I)
    {
        $I->wantToTest('that it is possible to select a single folder');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->click(MediaListPage::item('banners'));
        $I->seeNumberOfElements(MediaListPage::$itemSelected, 1);
    }

    /**
     * Test that it is possible to select an image and see the information in the infobar.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function selectMultipleItems(Media $I)
    {
        $I->wantToTest('that it is possible to select multiple');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->click(MediaListPage::item('banners'));
        $I->clickHoldingShiftkey(MediaListPage::item('powered_by.png'));
        $I->seeNumberOfElements(MediaListPage::$itemSelected, 2);
    }

    /**
     * Test that it's possible to navigate to a subfolder using double click.
     *
     * @param   Media  $I
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function navigateUsingDoubleClickOnFolder(Media $I)
    {
        $I->wantToTest('that it is possible to navigate to a subfolder using double click.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->doubleClick(MediaListPage::item('banners'));
        $I->waitForMediaLoaded();
        $I->seeInCurrentUrl(MediaListPage::$url . 'banners');
        $I->seeContents($this->contents['/banners']);
    }

    /**
     * Test that it's possible to navigate to a subfolder using tree.
     *
     * @param   Media  $I
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function navigateUsingTree(Media $I)
    {
        $I->wantToTest('that it is possible to navigate to a subfolder using tree.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->clickOnLinkInTree('banners');
        $I->waitForMediaLoaded();
        $I->seeInCurrentUrl(MediaListPage::$url . 'banners');
        $I->seeContents($this->contents['/banners']);
    }

    /**
     * Test that it's possible to navigate to a subfolder using breadcrumb.
     *
     * @param   Media  $I
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function navigateUsingBreadcrumb(Media $I)
    {
        $I->wantToTest('that it is possible to navigate to a subfolder using breadcrumb.');
        $I->amOnPage(MediaListPage::$url . 'banners');
        $I->waitForMediaLoaded();
        $I->clickOnLinkInBreadcrumb('images');
        $I->waitForMediaLoaded();
        $I->seeInCurrentUrl(MediaListPage::$url);
        $I->seeContents($this->contents['root']);
    }

    /**
     * Test that search is applied to the current list.
     *
     * @param   Media  $I
     *
     * @throws  Exception
     *
     * @since   4.0.6
     */
    public function searchInFilesAndFolders(Media $I)
    {
        $I->wantToTest('that search is applied to the current list.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->fillField(MediaListPage::$searchInputField, 'joomla');
        $I->seeElement(MediaListPage::$items);
        $I->seeElement(MediaListPage::item('joomla_black.png'));
        $I->dontSeeElement(MediaListPage::item('banners'));
    }

    /**
     * Test that search is cleared when navigating in the tree.
     *
     * @param   Media  $I
     *
     * @throws  Exception
     *
     * @since   4.0.6
     */
    public function searchIsClearedOnNavigate(Media $I)
    {
        $I->wantToTest('that search is cleared when navigating in the tree.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->fillField(MediaListPage::$searchInputField, 'banner');
        $I->doubleClick(MediaListPage::item('banners'));
        $I->waitForMediaLoaded();
        $I->seeInField(MediaListPage::$searchInputField, '');
    }

    /**
     * Test the upload of a single file using toolbar button.
     *
     * @param   Media $I Acceptance Helper Object
     *
     * @since   4.0.0
     */
    public function uploadSingleFileUsingToolbarButton(Media $I)
    {
        $testFileName = 'test-image-1.png';
        $testFileItem = MediaListPage::item($testFileName);

        $I->wantToTest('the upload of a single file using toolbar button.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->waitForJsOnPageLoad();
        $I->uploadFile('com_media/' . $testFileName);
        $I->seeAndCloseSystemMessage('Item uploaded.');
        $I->seeContents([$testFileName]);
        $I->click($testFileItem);
        $I->click(MediaListPage::$toolbarDeleteButton);
        $I->waitForElement(MediaListPage::$toolbarModalDeleteButton);
        $I->waitForJsOnPageLoad();

        // Sometimes the modal is still fading in
        $I->wait(1);
        $I->click(MediaListPage::$toolbarModalDeleteButton);

        // Ensure the modal has closed
        $I->wait(1);
        $I->seeAndCloseSystemMessage('Item deleted.');
        $I->waitForElementNotVisible($testFileItem);
    }

    /**
     * Test the upload of an existing file using toolbar button.
     *
     * @skip    We need to skip this test, because of a bug in acceptPopup in chrome.
     *          It throws a Facebook\WebDriver\Exception\UnexpectedAlertOpenException and does not accept the popup.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function uploadExistingFileUsingToolbarButton(Media $I)
    {
        $testFileName = 'test-image-1.jpg';

        $I->wantToTest('that it shows a confirmation dialog when uploading existing file.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->waitForJsOnPageLoad();
        $I->uploadFile('com_media/' . $testFileName);
        $I->seeAndCloseSystemMessage('Item uploaded.');
        $I->uploadFile('com_media/' . $testFileName);
        $I->seeContents([$testFileName]);
        $I->waitForMediaLoaded();
        $I->seeInPopup($testFileName . ' already exists. Do you want to replace it?');
        $I->acceptPopup();
        $I->seeAndCloseSystemMessage('Item uploaded.');
        $I->seeContents([$testFileName]);
    }

    /**
     * Test the create folder using toolbar button.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function createFolderUsingToolbarButton(Media $I)
    {
        $testFolderName = 'test-folder';

        $I->wantToTest('that it is possible to create a new folder using the toolbar button.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->waitForJsOnPageLoad();
        $I->click(MediaListPage::$toolbarCreateFolderButton);
        $I->waitForJsOnPageLoad();

        // Sometimes the modal is still fading in
        $I->wait(1);
        $I->seeElement(MediaListPage::$newFolderInputField);
        $I->seeElement(MediaListPage::$modalConfirmButtonDisabled);
        $I->fillField(MediaListPage::$newFolderInputField, $testFolderName);
        $I->waitForElementChange(MediaListPage::$modalConfirmButton, function (Facebook\WebDriver\Remote\RemoteWebElement $el) {
            return $el->isEnabled();
        });
        $I->click(MediaListPage::$modalConfirmButton);

        // Ensure the modal has closed
        $I->wait(1);
        $I->seeSystemMessage('Folder created.');
        $I->waitForElement(MediaListPage::item($testFolderName));
        $I->seeElement(MediaListPage::item($testFolderName));
    }

    /**
     * Test create an existing folder.
     *
     * @skip    Skipping until bug is resolved in media manager
     *
     * @param   Media $I Acceptance Helper Object
     *
     * @since   4.0.0
     */
    public function createExistingFolderUsingToolbar(Media $I)
    {
        $I->wantToTest('that it is not possible to create an existing folder.');
        $I->amOnPage(MediaListPage::$url);
        $I->click(MediaListPage::$toolbarCreateFolderButton);
        $I->seeElement(MediaListPage::$newFolderInputField);
        $I->seeElement(MediaListPage::$modalConfirmButtonDisabled);
        $I->fillField(MediaListPage::$newFolderInputField, $this->testDirectory);
        $I->waitForElementChange(MediaListPage::$modalConfirmButton, function (Facebook\WebDriver\Remote\RemoteWebElement $el) {
            return $el->isEnabled();
        });
        $I->click(MediaListPage::$modalConfirmButton);
        $I->seeSystemMessage('Error creating folder.');
    }

    /**
     * Test delete single file using toolbar.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function deleteSingleFileUsingToolbar(Media $I)
    {
        $testFileName = 'test-image-1.png';
        $testFileItem = MediaListPage::item($testFileName);

        $I->wantToTest('that it is possible to delete a single file.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->uploadFile('com_media/' . $testFileName);
        $I->seeAndCloseSystemMessage('Item uploaded.');
        $I->waitForElement($testFileItem);
        $I->waitForJsOnPageLoad();
        $I->click($testFileItem);
        $I->click(MediaListPage::$toolbarDeleteButton);

        $I->waitForElement(MediaListPage::$toolbarModalDeleteButton);

        // Sometimes the modal is still fading in
        $I->wait(1);
        $I->waitForJsOnPageLoad();
        $I->click(MediaListPage::$toolbarModalDeleteButton);

        // Ensure the modal has closed
        $I->wait(1);
        $I->seeSystemMessage('Item deleted.');
        $I->waitForElementNotVisible($testFileItem);
        $I->dontSeeElement($testFileName);
    }

    /**
     * Test toggle info bar.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function deleteSingleFolder(Media $I)
    {
        $testfolderName = 'test-folder';
        $testFolderItem = MediaListPage::item($testfolderName);

        $I->wantToTest('that it is possible to delete a single folder.');
        $I->createDirectory('images/' . $this->testDirectory . '/' . $testfolderName);
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->waitForElement($testFolderItem);
        $I->click($testFolderItem);
        $I->click(MediaListPage::$toolbarDeleteButton);
        $I->waitForElement(MediaListPage::$toolbarModalDeleteButton);

        // Sometimes the modal is still fading in
        $I->wait(1);
        $I->waitForJsOnPageLoad();
        $I->click(MediaListPage::$toolbarModalDeleteButton);

        // Ensure the modal has closed
        $I->wait(1);
        $I->seeSystemMessage('Item deleted.');
        $I->waitForElementNotVisible($testFolderItem);
        $I->dontSeeElement($testFolderItem);
    }

    /**
     * Test check all items.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function deleteMultipleFiles(Media $I)
    {
        $testFileName1 = 'test-image-1.png';
        $testFileName2 = 'test-image-1.jpg';
        $testFileItem1 = MediaListPage::item($testFileName1);
        $testFileItem2 = MediaListPage::item($testFileName2);

        $I->wantToTest('that it is possible to delete multiple files.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->uploadFile('com_media/' . $testFileName1);
        $I->seeAndCloseSystemMessage('Item uploaded.');
        $I->wait(10);
        $I->waitForElement($testFileItem1);

        // We have to clear the file input, otherwise our method of uploading the file via Codeception will upload it twice
        $I->executeJS('document.getElementsByName(\'file\')[0].value = \'\'');
        $I->waitForMediaLoaded();
        $I->uploadFile('com_media/' . $testFileName2);
        $I->seeAndCloseSystemMessage('Item uploaded.');
        $I->wait(10);
        $I->waitForMediaLoaded();
        $I->waitForElement($testFileItem2);
        $I->click($testFileItem1);
        $I->clickHoldingShiftkey($testFileItem2);
        $I->click(MediaListPage::$toolbarDeleteButton);
        $I->waitForElement(MediaListPage::$toolbarModalDeleteButton);
        $I->click(MediaListPage::$toolbarModalDeleteButton);
        $I->seeSystemMessage('Item deleted.');
        $I->waitForElementNotVisible($testFileItem1);
        $I->waitForElementNotVisible($testFileItem2);
        $I->dontSeeElement($testFileItem1);
        $I->dontSeeElement($testFileItem2);
    }

    /**
     * Test rename a file.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function renameFile(Media $I)
    {
        $testFileName = 'test-image-1.png';
        $testFileItem = MediaListPage::item($testFileName);

        $I->wantToTest('that it is possible to rename a file.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->uploadFile('com_media/' . $testFileName);
        $I->waitForElement($testFileItem);
        $I->wait(1);
        $I->clickOnActionInMenuOf($testFileName, MediaListPage::$renameAction);
        $I->waitForElement(MediaListPage::$renameInputField);

        // Sometimes the modal is still fading in
        $I->wait(1);
        $I->seeElement(MediaListPage::$renameInputField);
        $I->seeElement(MediaListPage::$modalConfirmButton);
        $I->fillField(MediaListPage::$renameInputField, 'test-image-1-renamed');
        $I->click(MediaListPage::$modalConfirmButton);

        // Ensure the modal has closed
        $I->wait(1);
        $I->seeSystemMessage('Item renamed.');
        $I->dontSeeElement($testFileItem);
        $I->seeElement(MediaListPage::item('test-image-1-renamed.png'));
    }

    /**
     * Test rename a file to the same name as an existing file.
     *
     * @skip    Skipping until bug is resolved in media manager
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function renameFileToExistingFile(Media $I)
    {
        $testFileName1 = 'test-image-1.png';
        $testFileName2 = 'test-image-2.png';
        $testFileItem1 = MediaListPage::item($testFileName1);
        $testFileItem2 = MediaListPage::item($testFileName2);

        $I->wantToTest('that it is not possible to rename a file to a filename of an existing file.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->uploadFile('com_media/' . $testFileName1);
        $I->waitForElement($testFileItem1);
        $I->uploadFile('com_media/' . $testFileName2);
        $I->waitForElement($testFileItem2);
        $I->clickOnActionInMenuOf($testFileName2, MediaListPage::$renameAction);
        $I->seeElement(MediaListPage::$renameInputField);
        $I->seeElement(MediaListPage::$modalConfirmButton);
        $I->fillField(MediaListPage::$renameInputField, 'test-image-1');
        $I->click(MediaListPage::$modalConfirmButton);
        $I->seeSystemMessage('Error renaming file.');
        $I->seeElement($testFileItem1);
        $I->seeElement($testFileItem2);
    }

    /**
     * Test rename a file.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function renameFolder(Media $I)
    {
        $testFolderName = 'test-folder';
        $testFolderItem = MediaListPage::item($testFolderName);

        $I->wantToTest('that it is possible to rename a folder.');
        $I->createDirectory('images/' . $this->testDirectory . '/' . $testFolderName);
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->waitForElement($testFolderItem);
        $I->wait(1);
        $I->clickOnActionInMenuOf($testFolderName, MediaListPage::$renameAction);
        $I->waitForElement(MediaListPage::$renameInputField);

        // Sometimes the modal is still fading in
        $I->wait(1);
        $I->seeElement(MediaListPage::$renameInputField);
        $I->seeElement(MediaListPage::$modalConfirmButton);
        $I->fillField(MediaListPage::$renameInputField, $testFolderName . '-renamed');
        $I->click(MediaListPage::$modalConfirmButton);

        // Ensure the modal has closed
        $I->wait(1);
        $I->seeSystemMessage('Item renamed.');
        $I->dontSeeElement($testFolderItem);
        $I->seeElement(MediaListPage::item($testFolderName . '-renamed'));
    }

    /**
     * Test rename a folder to the same name as an existing folder.
     *
     * @skip    Skipping until bug is resolved in media manager
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function renameFolderToExistingFolder(Media $I)
    {
        $testFolderName1 = 'test-folder-1';
        $testFolderName2 = 'test-folder-2';
        $testFolderItem1 = MediaListPage::item($testFolderName1);
        $testFolderItem2 = MediaListPage::item($testFolderName2);

        $I->wantToTest('that it is not possible to rename a folder to a foldername of an existing folder.');
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->createDirectory('images/' . $this->testDirectory . '/' . $testFolderName1);
        $I->createDirectory('images/' . $this->testDirectory . '/' . $testFolderName2);
        $I->amOnPage(MediaListPage::$url . $this->testDirectory);
        $I->waitForElement($testFolderItem1);
        $I->waitForElement($testFolderItem2);
        $I->clickOnActionInMenuOf($testFolderName2, MediaListPage::$renameAction);
        $I->seeElement(MediaListPage::$renameInputField);
        $I->seeElement(MediaListPage::$modalConfirmButton);
        $I->fillField(MediaListPage::$renameInputField, $testFolderName1);
        $I->click(MediaListPage::$modalConfirmButton);
        $I->seeSystemMessage('Error renaming folder.');
        $I->seeElement($testFolderItem1);
        $I->seeElement($testFolderItem2);
    }

    /**
     * Test preview using double click on image.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function showPreviewUsingDoubleClickOnImage(Media $I)
    {
        $I->wantToTest('that it shows a preview for image when user doubleclicks it.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->doubleClick(MediaListPage::item('powered_by.png'));
        $I->waitForElement(MediaListPage::$previewModal);
        $I->seeElement(MediaListPage::$previewModal);
        $I->see('powered_by.png', MediaListPage::$previewModal);
        $I->seeElement(MediaListPage::$previewModalImg);
        $I->seeElement(MediaListPage::$previewModalCloseButton);
    }

    /**
     * Test preview using action menu.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function showPreviewUsingClickOnActionMenu(Media $I)
    {
        $I->wantToTest('that it is possible to show a preview of an image using button in action menu.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->clickOnActionInMenuOf('powered_by.png', MediaListPage::$previewAction);
        $I->waitForElement(MediaListPage::$previewModal);
        $I->seeElement(MediaListPage::$previewModal);
        $I->see('powered_by.png', MediaListPage::$previewModal);
        $I->seeElement(MediaListPage::$previewModalImg);
        $I->seeElement(MediaListPage::$previewModalCloseButton);
    }

    /**
     * Test close the preview modal.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function closePreviewModalUsingCloseButton(Media $I)
    {
        $I->wantToTest('that it is possible to close the preview modal using the close button.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->doubleClick(MediaListPage::item('powered_by.png'));
        $I->waitForElement(MediaListPage::$previewModal);
        $I->waitForJsOnPageLoad();
        $I->seeElement(MediaListPage::$previewModalCloseButton);
        $I->click(MediaListPage::$previewModalCloseButton);
        $I->dontSeeElement(MediaListPage::$previewModal);
    }

    /**
     * Test close the preview modal.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function closePreviewModalUsingEscapeKey(Media $I)
    {
        $I->wantToTest('that it is possible to close the preview modal using escape key.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->doubleClick(MediaListPage::item('powered_by.png'));
        $I->waitForElement(MediaListPage::$previewModal);
        $I->waitForJsOnPageLoad();
        $I->pressKey('body', \Facebook\WebDriver\WebDriverKeys::ESCAPE);
        $I->dontSeeElement(MediaListPage::$previewModal);
    }

    /**
     * Test rename a file.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function openImageEditorUsingActionMenu(Media $I)
    {
        $I->wantToTest('that it is possible to open the image editor using action menu.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->clickOnActionInMenuOf('powered_by.png', MediaListPage::$editAction);
        $I->seeInCurrentUrl(MediaFilePage::$url . '&mediatypes=0,1,2,3&path=local-images:/powered_by.png');
    }

    /**
     * Test toggle info bar.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function toggleInfoBar(Media $I)
    {
        $I->wantToTest('that it is possible to toggle the infobar.');
        $I->amOnPage(MediaListPage::$url);
        $I->openInfobar();
        $I->seeElement(MediaListPage::$infoBar);
        $I->closeInfobar();
        $I->waitForElementNotVisible(MediaListPage::$infoBar);
        $I->dontSeeElement(MediaListPage::$infoBar);
    }

    /**
     * Test show file information in infobar.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function showFileInformationInInfobar(Media $I)
    {
        $I->wantToTest('that it shows basic file information in the infobar.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->click(MediaListPage::item('powered_by.png'));
        $I->openInfobar();
        $I->see('powered_by.png', MediaListPage::$infoBar);
        $I->see('image/png', MediaListPage::$infoBar);
    }

    /**
     * Test show folder information in infobar.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function showFolderInformationInInfobar(Media $I)
    {
        $I->wantToTest('that it shows basic folder information in the infobar.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->click(MediaListPage::item('banners'));
        $I->openInfobar();
        $I->see('banners', MediaListPage::$infoBar);
        $I->see('directory', MediaListPage::$infoBar);
    }

    /**
     * Test resize the thumbnails.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function resizeThumbnails(Media $I)
    {
        $I->wantToTest('that it is possible to resize the thumbnails.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();

        // Resize to max
        $I->seeElement(MediaListPage::$itemsContainerMedium);
        $I->click(MediaListPage::$increaseThumbnailSizeButton);
        $I->seeElement(MediaListPage::$itemsContainerLarge);
        $I->click(MediaListPage::$increaseThumbnailSizeButton);
        $I->seeElement(MediaListPage::$itemsContainerExtraLarge);
        $I->seeElement(MediaListPage::$increaseThumbnailSizeButtonDisabled);

        // Resize to min
        $I->click(MediaListPage::$decreaseThumbnailSizeButton);
        $I->seeElement(MediaListPage::$itemsContainerLarge);
        $I->click(MediaListPage::$decreaseThumbnailSizeButton);
        $I->seeElement(MediaListPage::$itemsContainerMedium);
        $I->click(MediaListPage::$decreaseThumbnailSizeButton);
        $I->seeElement(MediaListPage::$itemsContainerSmall);
        $I->seeElement(MediaListPage::$decreaseThumbnailSizeButtonDisabled);
    }

    /**
     * Test table view.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function toggleListViewUsingToolbarButton(Media $I)
    {
        $I->wantToTest('that it is possible to toggle the list view (grid/table) using the toolbar button.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->seeElement(MediaListPage::$mediaBrowserGrid);
        $I->seeElement(MediaListPage::$toggleListViewButton);
        $I->click(MediaListPage::$toggleListViewButton);
        $I->dontSeeElement(MediaListPage::$increaseThumbnailSizeButton);
        $I->dontSeeElement(MediaListPage::$decreaseThumbnailSizeButton);
        $I->seeElement(MediaListPage::$mediaBrowserTable);
        $I->click(MediaListPage::$toggleListViewButton);
        $I->seeElement(MediaListPage::$mediaBrowserGrid);
    }

    /**
     * Test check all items.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function selectAllItemsUsingToolbarButton(Media $I)
    {
        $I->wantToTest('that it is possible to select all items using toolbar button.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $I->click(MediaListPage::$selectAllButton);
        $I->seeNumberOfElements(MediaListPage::$itemSelected, count($this->contents['root']) + 1);
    }

    /**
     * Test that the app state is synced with session storage.
     *
     * @param   Media  $I  Acceptance Helper Object
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function synchronizeAppStateWithSessionStorage(Media $I)
    {
        $I->wantToTest('that the application state is synchronized with session storage.');
        $I->amOnPage(MediaListPage::$url);
        $I->waitForMediaLoaded();
        $json = $I->executeJS('return sessionStorage.getItem("' . MediaListPage::$storageKey . '")');
        $I->assertContains('"selectedDirectory":"local-images:/"', $json);
        $I->assertContains('"showInfoBar":false', $json);
        $I->assertContains('"listView":"grid"', $json);
        $I->assertContains('"gridSize":"md"', $json);
        $I->assertContains('"search":""', $json);
        $I->clickOnLinkInTree('banners');
        $I->waitForMediaLoaded();
        $I->openInfobar();
        $I->click(MediaListPage::$increaseThumbnailSizeButton);
        $I->click(MediaListPage::$toggleListViewButton);
        $I->fillField(MediaListPage::$searchInputField, 'joomla');
        $json = $I->executeJS('return sessionStorage.getItem("' . MediaListPage::$storageKey . '")');
        $I->assertContains('"selectedDirectory":"local-images:/banners"', $json);
        $I->assertContains('"showInfoBar":true', $json);
        $I->assertContains('"listView":"table"', $json);
        $I->assertContains('"gridSize":"lg"', $json);
        $I->assertContains('"search":"joomla"', $json);
    }
}
