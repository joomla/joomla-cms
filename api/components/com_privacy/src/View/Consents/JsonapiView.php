<?php
/**
 * @package     Joomla.API
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\View\Consents;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Resource;

/**
 * The consents view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
		'id',
		'user_id',
		'state',
		'created',
		'subject',
		'body',
		'remind',
		'token',
		'username',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'user_id',
		'state',
		'created',
		'subject',
		'body',
		'remind',
		'token',
		'username',
	];

	/**
	 * Execute and display a template script.
	 *
	 * @param   object  $item  Item
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayItem($item = null)
	{
		$id = $this->get('state')->get($this->getName() . '.id');

		if ($id === null)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ITEMID_MISSING'));
		}

		/** @var \Joomla\CMS\MVC\Model\ListModel $model */
		$model       = $this->getModel();
		$displayItem = null;

		foreach ($model->getItems() as $item)
		{
			$item = $this->prepareItem($item);

			if ($item->id === $id)
			{
				$displayItem = $item;
				break;
			}
		}

		if ($displayItem === null)
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if ($this->type === null)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_CONTENT_TYPE_MISSING'));
		}

		$serializer = new JoomlaSerializer($this->type);
		$element = (new Resource($displayItem, $serializer))
			->fields([$this->type => $this->fieldsToRenderItem]);

		$this->document->setData($element);
		$this->document->addLink('self', Uri::current());

		return $this->document->render();
	}
}
