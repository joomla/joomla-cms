<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
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
	 * Query filter parameters => model state mappings
	 *
	 * @var  array
	 */
	protected $queryFilterModelStateMap = [
		'access' => [
			'name' => 'filter.access',
			'type' => 'INT'
		],
		'author_id' => [
			'name' => 'filter.author_id',
			'type' => 'INT'
		],
		'category_id' => [
			'name' => 'filter.category_id',
			'type' => 'INT'
		],
		'search' => [
			'name' => 'filter.search',
			'type' => 'STRING'
		],
		'state' => [
			'name' => 'filter.published',
			'type' => 'INT'
		],
		'language' => [
			'name' => 'filter.language',
			'type' => 'STRING'
		],
	];

	/**
	 * Method to allow extended classes to manipulate the data to be saved for an extension.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function preprocessSaveData(array $data): array
	{
		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		return $data;
	}
}
