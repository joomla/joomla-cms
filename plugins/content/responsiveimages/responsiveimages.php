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
			// Check if custom size options are specified
			if ($this->params->get('custom_sizes') && $this->params->get('custom_size_options'))
			{
				$this->responsiveSizes = explode(",", htmlspecialchars($this->params->get('custom_size_options')));
			}

			// Add srcset attribute to content images
			$contentKey = $this->_getContentKey($context);
			$table->{$contentKey} = MediaHelper::addContentSrcsetAndSizes($table->{$contentKey}, $this->responsiveSizes);

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
				MediaHelper::generateContentResponsiveImages($this->initContent, $content, $this->responsiveSizes);
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
			// $imgObj = new Image(JPATH_ROOT . '/images' . $article->path);
			// $imgObj->deleteMultipleSizes();
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
		// Convert string data to array
		$data['images'] = (array) json_decode($data['images']);
		$data['params'] = (array) json_decode($data['params']);

		// Get form images depending on context
		switch ($context)
		{
			case "com_content.article":
			case "com_tags.tag":
				return array(
					'image_intro' => (object) [
						'name'  => $data['images']['image_intro'], 
						'sizes' => MediaHelper::getSizes($data['images']['image_intro_sizes'], $data['images']['image_intro_size_options'])
					], 
					'image_fulltext' => (object) [
						'name'  => $data['images']['image_fulltext'], 
						'sizes' => MediaHelper::getSizes($data['images']['image_fulltext_sizes'], $data['images']['image_fulltext_size_options'])
					], 
				);
			case "com_banners.banner":
				return array(
					'image' => (object) [
						'name'  => $data['params']['imageurl'],
						'sizes' => MediaHelper::getSizes($data['params']['imageurl_sizes'], $data['params']['imageurl_size_options'])
					]
				);
			case "com_categories.category":
				return array(
					'image' => (object) [
						'name'  => $data['params']['image'],
						'sizes' => MediaHelper::getSizes($data['params']['image_sizes'], $data['params']['image_size_options'])
					]
				);
			case "com_contact.contact":
				return array(
					'image' => (object) [
						'name'  => $data['image'],
						'sizes' => MediaHelper::getSizes($data['image_sizes'], $data['image_size_options'])
					]
				);
			case "com_newsfeeds.newsfeed":
				return array(
					'image_first' => (object) [
						'name'  => $data['images']['image_first'], 
						'sizes' => MediaHelper::getSizes($data['images']['image_first_sizes'], $data['images']['image_first_size_options'])
					], 
					'image_second' => (object) [
						'name'  => $data['images']['image_second'], 
						'sizes' => MediaHelper::getSizes($data['images']['image_second_sizes'], $data['images']['image_second_size_options'])
					], 
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
