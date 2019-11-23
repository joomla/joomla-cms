<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The article controller
 *
 * @since  4.0.0
 */
class ArticlesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'articles';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'articles';

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		$data = (array) json_decode($this->input->json->getRaw(), true);

		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		$this->input->set('data', $data);

		return parent::save($recordKey);
	}

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$this->input->set('model_state',
			[
				'filter.author_id' => $this->getAuthorIdFromInput(),
				'filter.condition' => $this->getConditionFromInput(),
				'filter.stage'     => $this->getStageFromInput(),
				'filter.language'  => $this->getLanguageFromInput(),
			]
		);

		return parent::displayList();
	}

	/**
	 * Get author id from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getAuthorIdFromInput()
	{
		return $this->input->exists('author') ?
			$this->input->get('author') : $this->input->post->get('author');
	}

	/**
	 * Get condition from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getConditionFromInput()
	{
		return $this->input->exists('condition') ?
			$this->input->get('condition') : $this->input->post->get('condition');
	}

	/**
	 * Get stage from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getStageFromInput()
	{
		return $this->input->exists('stage') ?
			$this->input->get('stage') : $this->input->post->get('stage');
	}

	/**
	 * Get language from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getLanguageFromInput()
	{
		return $this->input->exists('language') ?
			$this->input->get('language') : $this->input->post->get('language');
	}
}
