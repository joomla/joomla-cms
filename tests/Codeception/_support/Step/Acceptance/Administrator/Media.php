<?php namespace Step\Acceptance\Administrator;

use Codeception\Util\FileSystem as Util;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Page\Acceptance\Administrator\MediaListPage;

/**
 * Acceptance Step object class contains suits for Media Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Media extends Admin
{
	/**
	 * Helper function to wait for the media manager to load the data
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function waitForMediaLoaded()
	{
		$I = $this;
		try
		{
			$I->waitForElement(MediaListPage::$loader, 3);
			$I->waitForElementNotVisible(MediaListPage::$loader);

			// Add a small timeout to wait for rendering (otherwise it will fail when executed in headless browser)
			$I->wait(0.2);
		}
		catch (NoSuchElementException $e)
		{
			/*
			 * Continue if we cant find the loader within 3 seconds.
			 * In most cases this means that the loader disappeared so quickly, that selenium was not able to see it.
			 * Unfortunately we currently dont have any better technique to detect when vue components are loaded/updated
			 */
		}
	}

	/**
	 * Helper function that tests that you see contents of a directory
	 *
	 * @param array $contents
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function seeContents(array $contents = [])
	{
		$I = $this;
		$I->seeElement(MediaListPage::$items);
		foreach ($contents as $content)
		{
			$I->seeElement(MediaListPage::item($content));
		}
	}

	/**
	 * Helper function to upload a file in the current directory
	 *
	 * @param  string $fileName
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function uploadFile($fileName)
	{
		$I = $this;
		$I->seeElementInDOM(MediaListPage::$fileInputField);
		$I->attachFile(MediaListPage::$fileInputField, $fileName);
	}

	/**
	 * Delete a file from filesystem
	 *
	 * @param  string $path
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @todo extract to JoomlaFilesystem
	 */
	public function deleteFile($path)
	{
		$I            = $this;
		$absolutePath = $this->absolutizePath($path);
		if (!file_exists($absolutePath))
		{
			\PHPUnit\Framework\Assert::fail('file not found.');
		}
		unlink($absolutePath);
		$I->comment('Deleted ' . $absolutePath);
	}

	/**
	 * Create a new directory on filesystem
	 *
	 * @param   string  $dirname
	 * @param   integer $mode
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @todo extract to JoomlaFilesystem
	 */
	public function createDirectory($dirname, $mode = 0755)
	{
		$I            = $this;
		$absolutePath = $this->absolutizePath($dirname);
		$oldUmask     = @umask(0);
		@mkdir($absolutePath, $mode, true);

		// This was adjusted to make drone work: codeception is executed as root, joomla runs as www-data
		// so we have to run chown after creating new directpries
		if (!empty($user = $this->getLocalUser()))
		{
			@chown($absolutePath, $user);
		}
		@umask($oldUmask);
		$I->comment('Created ' . $absolutePath);
	}

	/**
	 * Deletes directory with all subdirectories
	 *
	 * @param   string $dirname
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @todo extract to JoomlaFilesystem
	 */
	public function deleteDirectory($dirname)
	{
		$I            = $this;
		$absolutePath = $this->absolutizePath($dirname);
		Util::deleteDir($absolutePath);
		$I->comment('Deleted ' . $absolutePath);
	}

	/**
	 * Click on a link in the media tree
	 *
	 * @param   string $link
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clickOnLinkInTree($link)
	{
		$I = $this;
		$I->click($link, MediaListPage::$mediaTree);
	}

	/**
	 * Click on a link in the media breadcrumb
	 *
	 * @param   string $link
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clickOnLinkInBreadcrumb($link)
	{
		$I = $this;
		$I->click($link, MediaListPage::$mediaBreadcrumb);
	}

	/**
	 * Open the item actions menu of an item
	 *
	 * @param   string $itemName
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * Open the item actions menu and click on one action
	 *
	 * @param   string $itemName
	 * @param   string $actionName
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * Helper function to open the media manager info bar
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function openInfobar()
	{
		$I = $this;
		try
		{
			$I->seeElement(MediaListPage::$infoBar);
		}
		catch (\Exception $e)
		{
			$I->click(MediaListPage::$toggleInfoBarButton);
			$I->waitForElementVisible(MediaListPage::$infoBar);
		}
	}

	/**
	 * Helper function to close the media manager info bar
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function closeInfobar()
	{
		$I = $this;
		try
		{
			$I->seeElement(MediaListPage::$infoBar);
			$I->click(MediaListPage::$toggleInfoBarButton);
			$I->waitForElementNotVisible(MediaListPage::$infoBar);
		}
		catch (\Exception $e)
		{
			// Do nothing
		}
	}

	/**
	 * Click on an element holding shift key
	 *
	 * @param   string $xpath Xpath selector
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clickHoldingShiftkey($xpath)
	{
		$I = $this;
		$I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use ($xpath) {
			$element  = $webdriver->findElement(\Facebook\WebDriver\WebDriverBy::xpath($xpath));
			$action   = new \Facebook\WebDriver\Interactions\WebDriverActions($webdriver);
			$shiftKey = \Facebook\WebDriver\WebDriverKeys::SHIFT;
			$action->keyDown(null, $shiftKey)
				->click($element)
				->keyUp(null, $shiftKey)
				->perform();
		});
	}

	/**
	 * Get the absoluute path
	 *
	 * @param   string $path
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @todo extract to JoomlaFilesystem
	 */
	protected function absolutizePath($path)
	{
		return rtrim($this->getCmsPath(), '/') . '/' . ltrim($path, '/');
	}

	/**
	 * Get the local user from the configuration from suite configuration
	 *
	 * @return string
	 */
	protected function getLocalUser()
	{
		try
		{
			return $this->getSuiteConfiguration()['modules']['config']['Helper\Acceptance']['localUser'];
		}
		catch (\Exception $e)
		{
			return '';
		}
	}

	/**
	 * Get thee cms path from suite configuration
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function getCmsPath()
	{
		try
		{
			return $this->getSuiteConfiguration()['modules']['config']['Helper\Acceptance']['cmsPath'];
		}
		catch (\Exception $e)
		{
			throw new \Exception('cmsPath is not defined in acceptance.suite.yml.');
		}
	}
}
