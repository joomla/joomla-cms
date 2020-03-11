<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.dontTranslate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin to enable adding the translate HTML5 attribute
 * This uses the {dontTranslate}Text{/dontTranslate} syntax
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemDontTranslate extends CMSPlugin
{
	/**
	 * Clean the browser page title
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeRender() : void
	{
		if (!Factory::getApplication()->isClient('site'))
		{
			return;
		}

		$title = Factory::getDocument()->getTitle();
		$title = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $title);
		Factory::getDocument()->setTitle($title);
	}

	/**
	 * Plugin that adds the translate="no" attribute
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRender() : void
	{
		if (Factory::getApplication()->isClient('administrator'))
		{
			$this->cleanAlias();
			$this->cleanCategories();
			$this->cleanList();
		}

		if (!Factory::getApplication()->isClient('site'))
		{
			return;
		}

		$body = Factory::getApplication()->getBody();

		$body = str_replace(['{dontTranslate}', '{/dontTranslate}'], ['<span translate="no">', '</span>'], $body);
		Factory::getApplication()->setBody($body);
	}

	/**
	 * Clean the alias
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function cleanAlias() : void
	{
		$body = Factory::getApplication()->getBody();

		if (!$body)
		{
			return;
		}

		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTML($body);
		libxml_use_internal_errors(false);

		if ($dom->getElementById('jform_alias'))
		{
			$alias = $dom->getElementById('jform_alias');
			$string = $alias->attributes->getNamedItem('value')->nodeValue;
			$string = str_replace(['{donttranslate}', 'donttranslate-'], '', $string);
			$string = str_replace(['{-donttranslate}', '-donttranslate'], '', $string);
			$alias->attributes->getNamedItem('value')->nodeValue = $string;
			$body = $dom->saveHTML();
			Factory::getApplication()->setBody($body);
		}
	}

	/**
	 * Clean the list
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function cleanList() : void
	{
		$body = Factory::getApplication()->getBody();
		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTML($body);
		libxml_use_internal_errors(false);

		foreach ($dom->getElementsByTagName('a') as $link)
		{
			$string = $link->getAttribute('title');
			$found = false;
			$value = $link->nodeValue;

			if (strpos($string, '{dontTranslate}') !== false)
			{
				$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
				$link->setAttribute('title', $string);
				$found = true;
			}

			if (strpos($value, '{dontTranslate}') !== false)
			{
				$value = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $value);
				$link->nodeValue = $value;
				$found = true;
			}

			if ($found)
			{
				$body = $dom->saveHTML();
				Factory::getApplication()->setBody($body);
			}
		}
	}

	/**
	 * On Before Saving content clean alias
	 *
	 * @param   string   $context  The context
	 * @param   object   $table    The item
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentBeforeSave($context, $table, $isNew = false, $data = []) : void
	{
		if (isset($table->alias))
		{
			$table->alias = str_replace(['{donttranslate}', 'donttranslate-'], '', $table->alias);
			$table->alias = str_replace(['{-donttranslate}', '-donttranslate'], '', $table->alias);
		}
	}

	/**
	 * Clean the category
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function cleanCategories() : void
	{
		$body = Factory::getApplication()->getBody();
		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$dom->loadHTML($body);
		libxml_use_internal_errors(false);

		// Article
		if ($dom->getElementById('jform_catid'))
		{
			$select = $dom->getElementById('jform_catid');

			foreach ($select->getElementsByTagName('option') as $link)
			{
				$string = $link->nodeValue;
				$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
				$link->nodeValue = $string;
			}
		}

		// Module
		if ($dom->getElementById('jform_params_catid'))
		{
			$select = $dom->getElementById('jform_params_catid');

			foreach ($select->getElementsByTagName('option') as $link)
			{
				$string = $link->nodeValue;
				$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
				$link->nodeValue = $string;
			}
		}

		// Menu
		if ($dom->getElementById('jform_request_id'))
		{
			$select = $dom->getElementById('jform_request_id');

			foreach ($select->getElementsByTagName('option') as $link)
			{
				$string = $link->nodeValue;
				$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
				$link->nodeValue = $string;
			}
		}

		if ($dom->getElementById('jform_menuordering'))
		{
			$select = $dom->getElementById('jform_menuordering');

			foreach ($select->getElementsByTagName('option') as $link)
			{
				$string = $link->nodeValue;
				$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
				$link->nodeValue = $string;
			}
		}

		if ($dom->getElementById('jform_parent_id'))
		{
			$select = $dom->getElementById('jform_parent_id');

			foreach ($select->getElementsByTagName('option') as $link)
			{
				$string = $link->nodeValue;
				$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
				$link->nodeValue = $string;
			}
		}

		if ($dom->getElementById('jform_request_id_name'))
		{
			$string = $dom->getElementById('jform_request_id_name')->getAttribute('value');
			$string = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $string);
			$dom->getElementById('jform_request_id_name')->setAttribute('value', $string);
		}

		// Menu Select Article
		$divs = $dom->getElementsByTagName('div');

		foreach ($divs as $div)
		{
			if ($div->getAttribute('class') === 'small')
			{
				$div->nodeValue = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $div->nodeValue);
			}
		}

		// Module Menu Assignment
		$labels = $dom->getElementsByTagName('label');

		foreach ($labels as $label)
		{
			if (strrpos($label->getAttribute('for'), 'jform_menuselect') !== false)
			{
				$label->nodeValue = str_replace(['{dontTranslate}', '{/dontTranslate}'], '', $label->nodeValue);
			}
		}

		$body = $dom->saveHTML();
		Factory::getApplication()->setBody($body);
	}
}
