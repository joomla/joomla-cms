<?php

/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Codeception\Util\FileSystem as Util;
use Exception;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Page\Acceptance\Administrator\MediaListPage;
use PHPUnit\Framework\Assert;

/**
 * Acceptance Step object class contains suits for Media Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Media extends Admin
{
    /**
     * Method to to wait for the media manager to load the data.
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function waitForMediaLoaded()
    {
        $I = $this;

        try {
            $I->waitForElement(MediaListPage::$loader, 3);
            $I->waitForElementNotVisible(MediaListPage::$loader);

            // Add a small timeout to wait for rendering (otherwise it will fail when executed in headless browser)
            $I->wait(0.5);
        } catch (TimeoutException $e) {
            /*
             * Continue if we cant find the loader within 3 seconds.
             * In most cases this means that the loader disappeared so quickly, that selenium was not able to see it.
             * Unfortunately we currently dont have any better technique to detect when vue components are loaded/updated
             */
        }
    }

    /**
     * Method to that tests that you see contents of a directory.
     *
     * @param   array  $contents  Contents
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function seeContents(array $contents = [])
    {
        $I = $this;
        $I->seeElement(MediaListPage::$items);

        foreach ($contents as $content) {
            $I->seeElement(MediaListPage::item($content));
        }
    }

    /**
     * Method to upload a file in the current directory.
     *
     * @param   string  $fileName  Filename
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function uploadFile($fileName)
    {
        $I = $this;
        $I->seeElementInDOM(MediaListPage::$fileInputField);
        $I->attachFile(MediaListPage::$fileInputField, $fileName);
    }

    /**
     * Method to delete a file from filesystem.
     *
     * @param   string  $path  Path
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     *
     * @todo    extract to JoomlaFilesystem
     */
    public function deleteFile($path)
    {
        $I            = $this;
        $absolutePath = $this->absolutizePath($path);

        if (!file_exists($absolutePath)) {
            Assert::fail('file not found.');
        }

        unlink($absolutePath);
        $I->comment('Deleted ' . $absolutePath);
    }

    /**
     * Method to create a new directory on filesystem.
     *
     * @param   string   $dirname  Dirname
     * @param   integer  $mode     Mode
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     *
     * @todo    extract to JoomlaFilesystem
     */
    public function createDirectory($dirname, $mode = 0755)
    {
        $I            = $this;
        $absolutePath = $this->absolutizePath($dirname);
        $oldUmask     = @umask(0);
        @mkdir($absolutePath, $mode, true);

        // This was adjusted to make drone work: codeception is executed as root, joomla runs as www-data
        // so we have to run chown after creating new user.
        if (!empty($user = $this->getLocalUser())) {
            @chown($absolutePath, $user);
        }

        @umask($oldUmask);
        $I->comment('Created ' . $absolutePath);
    }

    /**
     * Method to deletes directory with all subdirectories.
     *
     * @param   string  $dirname  Dirname
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     *
     * @todo    extract to JoomlaFilesystem
     */
    public function deleteDirectory($dirname)
    {
        $I            = $this;
        $absolutePath = $this->absolutizePath($dirname);
        Util::deleteDir($absolutePath);
        $I->comment('Deleted ' . $absolutePath);
    }

    /**
     * Method to click on a link in the media tree.
     *
     * @param   string  $link  Link
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function clickOnLinkInTree($link)
    {
        $I = $this;
        $I->click($link, MediaListPage::$mediaTree);
    }

    /**
     * Method to click on a link in the media breadcrumb.
     *
     * @param   string  $link  Link
     *
     * @return void
     * @since   4.0.0
     */
    public function clickOnLinkInBreadcrumb($link)
    {
        $I = $this;
        $I->click($link, MediaListPage::$mediaBreadcrumb);
    }

    /**
     * Method to open the item actions menu of an item.
     *
     * @param   string  $itemName  Item name
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function openActionsMenuOf($itemName)
    {
        $I       = $this;
        $toggler = MediaListPage::itemActionMenuToggler($itemName);
        $I->moveMouseOver(MediaListPage::item($itemName));
        $I->seeElement($toggler);
        $I->click($toggler);
    }

    /**
     * Method to open the item actions menu and click on one action.
     *
     * @param   string  $itemName    Item name
     * @param   string  $actionName  Action name
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function clickOnActionInMenuOf($itemName, $actionName)
    {
        $I      = $this;
        $action = MediaListPage::itemAction($itemName, $actionName);
        $I->openActionsMenuOf($itemName);
        $I->waitForElementVisible($action);
        $I->click($action);
    }

    /**
     * Method to open the media manager info bar.
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function openInfobar()
    {
        $I = $this;

        try {
            $I->seeElement(MediaListPage::$infoBar);
        } catch (Exception $e) {
            $I->click(MediaListPage::$toggleInfoBarButton);
            $I->waitForElementVisible(MediaListPage::$infoBar);
        }
    }

    /**
     * Method to close the media manager info bar.
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function closeInfobar()
    {
        $I = $this;

        try {
            $I->seeElement(MediaListPage::$infoBar);
            $I->click(MediaListPage::$toggleInfoBarButton);
            $I->waitForElementNotVisible(MediaListPage::$infoBar);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Method to click on an element holding shift key.
     *
     * @param   string  $xpath  Xpath selector
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function clickHoldingShiftkey($xpath)
    {
        $I = $this;
        $I->executeInSelenium(
            function (RemoteWebDriver $webdriver) use ($xpath) {
                $element  = $webdriver->findElement(WebDriverBy::xpath($xpath));
                $action   = new WebDriverActions($webdriver);
                $shiftKey = WebDriverKeys::SHIFT;
                $action->keyDown(null, $shiftKey)
                    ->click($element)
                    ->keyUp(null, $shiftKey)
                    ->perform();
            }
        );
    }

    /**
     * Method to get the absolute path.
     *
     * @param   string  $path  Path
     *
     * @return string
     *
     * @since   4.0.0
     *
     * @throws Exception
     *
     * @todo    extract to JoomlaFilesystem
     */
    protected function absolutizePath($path)
    {
        return rtrim($this->getCmsPath(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Method to get the local user from the configuration from suite configuration.
     *
     * @return string
     *
     * @since   4.0.0
     */
    protected function getLocalUser()
    {
        $I = $this;

        return $I->getConfig('localUser');
    }

    /**
     * Method to get the cms path from suite configuration.
     *
     * @return string
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    protected function getCmsPath()
    {
        $I = $this;

        return $I->getConfig('cmsPath');
    }
}
