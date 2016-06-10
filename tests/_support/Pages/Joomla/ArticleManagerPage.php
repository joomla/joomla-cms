<?php
/**
 * Created by PhpStorm.
 * User: nibra
 * Date: 10.06.16
 * Time: 05:10
 */

namespace Pages\Joomla;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;

class ArticleManagerPage extends AdminPage
{
	protected $url = 'administrator/index.php?option=com_content';

	/** @var Toolbar */
	protected $toolbar = null;

	/**
	 * @return Toolbar
	 */
	public function toolbar()
	{
		if (is_null($this->toolbar))
		{
			$buttons = [
				'new'    => ['id' => 'toolbar-new', 'page' => ArticleEditPage::class],
				'edit'   => ['id' => 'toolbar-edit', 'page' => ArticleEditPage::class],
				'delete' => ['id' => 'toolbar-delete', 'page' => ArticleManagerPage::class],
			];

			$this->toolbar = new Toolbar($this->driver, $buttons);
		}

		return $this->toolbar;
	}

	/**
	 * @param $arg1
	 *
	 * @return $this
	 * @throws NoSuchElementException
	 */
	public function selectItem($arg1)
	{
		$list = $this->driver->findElement(WebDriverBy::id('articleList'));
		foreach ($list->findElements(WebDriverBy::cssSelector('tr')) as $row)
		{
			try
			{
				$row->findElement(WebDriverBy::linkText($arg1));
				$row->findElement(WebDriverBy::cssSelector('input[type="checkbox"]'))->click();

				return $this;
			}
			catch (NoSuchElementException $e)
			{
				continue;
			}
		}

		throw new NoSuchElementException("Element '$arg1' not found, or it has no checkbox");
	}
}
