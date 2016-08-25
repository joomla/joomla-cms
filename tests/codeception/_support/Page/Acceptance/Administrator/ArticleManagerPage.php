<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to define Content Manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class ArticleManagerPage extends AdminPage
{
	/**
	 * Page object for content body editor field.
	 *
	 * @var array
	 * @since version
	 */
	public static $content = ['id' => 'jform_articletext'];

	/**
	 * Page object for the toggle button.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $toggleEditor = "Toggle editor";

	/**
	 * Link to the article listing page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = "/administrator/index.php?option=com_content&view=articles";

	/**
	 * Locator for article's name field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeName = ['xpath' => "//table[@id='articleList']//tr[1]//td[4]"];

	/**
	 * Locator for article's featured icon
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeFeatured = ['xpath' => "//table[@id='articleList']//*//span[@class='icon-featured']"];

	/**
	 * Locator for article's name field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeAccessLevel = ['xpath' => "//table[@id='articleList']//tr[1]//td[5]"];

	/**
	 * Locator for article's unpublish icon
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeUnpublished = ['xpath' => "//table[@id='articleList']//*//span[@class='icon-unpublish']"];

	/**
	 * Method to create new article
	 *
	 * @param   string  $title    The article title
	 * @param   string  $content  The article content
	 *
	 * @When    I create new content with field title as :title and content as a :content
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function fillContentCreateForm($title, $content)
	{
		$I = $this;

		$I->fillField(self::$title, $title);

		$I->click(self::$toggleEditor);
		$I->fillField(self::$content, $content);
	}
}
