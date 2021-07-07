<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.responsiveimages
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Table\Table;

/**
 * Plugin for implementing responsive images with art direction functionality
 *
 * @since  4.1.0
 */
class PlgContentResponsiveImages extends CMSPlugin
{
	/**
	 * Initial version of form images
	 *
	 * @var    array
	 * @since  4.1.0
	 */
	protected $initFormImages;

	/**
	 * Initial version of content
	 *
	 * @var    string
	 * @since  4.1.0
	 */
	protected $initContent;

	/**
	 * Event that stores initial versions of images and inserts srcset and sizes
	 * attributes into content img tags
	 *
	 * @param   string   $context  The context
	 * @param   object   $table    The item
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  boolean
	 *
	 * @since   4.1.0
	 */
	public function onContentBeforeSave($context, $table, $isNew, $data)
	{
		// Check type of table object
		if ($table instanceof Table)
		{
			// Add srcset attribute to content images
			$contentKey = $this->_getContentKey($context);
			$table->{$contentKey} = MediaHelper::addContentSrcsetAndSizes($table->{$contentKey});

			$item = clone $table;
			$item->load($table->id);

			// Get initial versions of content and form images
			if ($formImages = $this->_getFormImages($context, (array) $item))
			{
				$this->initFormImages = $formImages;
			}

			if ($content = $item->{$contentKey})
			{
				$this->initContent = $content;
			}
		}

		return true;
	}

	/**
	 * Event that generates different sized versions of content and form images
	 *
	 * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
	 * @param   object   $article  A JTableContent object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public function onContentAfterSave($context, $article, $isNew): void
	{
		// Check type of article object
		if ($article instanceof Table)
		{
			// Generate responsive images for form and content
			if ($formImages = $this->_getFormImages($context, (array) $article))
			{
				MediaHelper::generateFormResponsiveImages($this->initFormImages, $formImages);
			}

			if ($content = $article->{$this->_getContentKey($context)})
			{
				MediaHelper::generateContentResponsiveImages($this->initContent, $content);
			}
		}
	}

	/**
	 * Event that handles deletion of responsive images once original one gets deleted
	 *
	 * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
	 * @param   object  $article  A JTableContent object.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public function onContentBeforeDelete($context, $article): void
	{
		// Remove responsive versions if file is an image
		if ($context === "com_media.file" && MediaHelper::isImage($article->path))
		{
			$imgObj = new Image(JPATH_ROOT . '/images' . $article->path);
			$imgObj->deleteMultipleSizes();
		}
	}

	/**
	 * Returns form images from data with specific context
	 *
	 * @param   string  $context  The context for the data
	 * @param   array   $data     The validated data
	 *
	 * @return  mixed   Array of form images or false if they don't exist
	 *
	 * @since   4.1.0
	 */
	private function _getFormImages($context, $data)
	{
		// Convert string images to array
		$data['images'] = (array) json_decode($data['images']);
		$data['params'] = (array) json_decode($data['params']);

		// Get form images depending on context
		switch ($context)
		{
			case "com_content.article":
			case "com_tags.tag":
				return array(
					'image_intro' => $data['images']['image_intro'], 'image_fulltext' => $data['images']['image_fulltext']
				);
			case "com_banners.banner":
				return array('image' => $data['params']['imageurl']);
			case "com_categories.category":
				return array('image' => $data['params']['image']);
			case "com_contact.contact":
				return array('image' => $data['image']);
			case "com_newsfeeds.newsfeed":
				return array(
					'image_first' => $data['images']['image_first'], 'image_second' => $data['images']['image_second']
				);
			default:
				return false;
		}
	}

	/**
	 * Returns content key in form object for specific context
	 *
	 * @param   string  $context  The context
	 *
	 * @return  string  Content key
	 *
	 * @since   4.1.0
	 */
	private function _getContentKey($context)
	{
		return $context === 'com_content.article' ? 'introtext' : (
			$context === 'com_contact.contact' ? 'misc' : 'description'
		);
	}
}
