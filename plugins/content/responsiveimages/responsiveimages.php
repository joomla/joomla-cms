<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.responsiveimages
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

/**
 * Plugin for implementing responsive images with art direction functionality
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgContentResponsiveImages extends CMSPlugin
{
	/**
	 * Custom image sizes
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $customSizes;

	/**
	 * Custom image creation method
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $creationMethod;

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
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentBeforeSave($context, $table, $isNew, $data)
	{
		// Check type of table object
		if ($table instanceof Table)
		{
			$this->customSizes    = MediaHelper::getFormSizes($this->params->get('custom_sizes'), $this->params->get('custom_size_options'));
			$this->creationMethod = MediaHelper::getFormMethod($this->params->get('custom_sizes'), $this->params->get('creation_method'));
			$contentKey           = $this->_getContentKey($context);

			// Add srcset attribute to content images
			$table->{$contentKey} = MediaHelper::addContentSrcsetAndSizes(
				$table->{$contentKey}, $this->customSizes, $this->creationMethod
			);
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentAfterSave($context, $article, $isNew): void
	{
		// Check type of article object
		if ($article instanceof Table)
		{
			// Generate responsive images for form and content
			if ($formImages = $this->_getFormImages($context, (array) $article))
			{
				MediaHelper::generateFormResponsiveImages($formImages);
			}

			if ($content = $article->{$this->_getContentKey($context)})
			{
				MediaHelper::generateContentResponsiveImages($content, $this->customSizes, $this->creationMethod);
			}
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
	 * @since   __DEPLOY_VERSION__
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
						'name'   => $data['images']['image_intro'],
						'sizes'  => MediaHelper::getFormSizes($data['images']['image_intro_sizes'], $data['images']['image_intro_size_options']),
						'method' => MediaHelper::getFormMethod($data['images']['image_intro_sizes'], $data['images']['image_intro_method'])
					],
					'image_fulltext' => (object) [
						'name'   => $data['images']['image_fulltext'],
						'sizes'  => MediaHelper::getFormSizes(
							$data['images']['image_fulltext_sizes'], $data['images']['image_fulltext_size_options']
						),
						'method' => MediaHelper::getFormMethod($data['images']['image_fulltext_sizes'], $data['images']['image_fulltext_method'])
					],
				);
			case "com_banners.banner":
				return array(
					'image' => (object) [
						'name'   => $data['params']['imageurl'],
						'sizes'  => MediaHelper::getFormSizes($data['params']['imageurl_sizes'], $data['params']['imageurl_size_options']),
						'method' => MediaHelper::getFormMethod($data['params']['imageurl_sizes'], $data['params']['imageurl_method'])
					]
				);
			case "com_categories.category":
				return array(
					'image' => (object) [
						'name'   => $data['params']['image'],
						'sizes'  => MediaHelper::getFormSizes($data['params']['image_sizes'], $data['params']['image_size_options']),
						'method' => MediaHelper::getFormMethod($data['params']['image_sizes'], $data['images']['image_method'])
					]
				);
			case "com_contact.contact":
				return array(
					'image' => (object) [
						'name'   => $data['image'],
						'sizes'  => MediaHelper::getFormSizes($data['image_sizes'], $data['image_size_options']),
						'method' => MediaHelper::getFormMethod($data['image_sizes'], $data['images']['image_method'])
					]
				);
			case "com_newsfeeds.newsfeed":
				return array(
					'image_first' => (object) [
						'name'   => $data['images']['image_first'],
						'sizes'  => MediaHelper::getFormSizes($data['images']['image_first_sizes'], $data['images']['image_first_size_options']),
						'method' => MediaHelper::getFormMethod($data['images']['image_first_sizes'], $data['images']['image_first_method'])
					],
					'image_second' => (object) [
						'name'   => $data['images']['image_second'],
						'sizes'  => MediaHelper::getFormSizes($data['images']['image_second_sizes'], $data['images']['image_second_size_options']),
						'method' => MediaHelper::getFormMethod($data['images']['image_second_sizes'], $data['images']['image_second_method'])
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
	 * @since   __DEPLOY_VERSION__
	 */
	private function _getContentKey($context)
	{
		return $context === 'com_content.article' ? 'introtext' : (
			$context === 'com_contact.contact' ? 'misc' : 'description'
		);
	}
}
