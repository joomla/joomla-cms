<?php

/**
 * @package     Joomla.API
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\View\Requests;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Privacy\Administrator\Model\ExportModel;
use Tobscure\JsonApi\Resource;

/**
 * The requests view
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
    protected $fieldsToRenderItem = ['id', 'typeAlias', 'email', 'requested_at', 'status', 'request_type'];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = ['id', 'email', 'requested_at', 'status', 'request_type'];

    /**
     * Execute and display a template script.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function export()
    {
        /** @var ExportModel $model */
        $model = $this->getModel();

        $exportData = $model->collectDataForExportRequest();

        if ($exportData == false) {
            throw new RouteNotFoundException('Item does not exist');
        }

        $serializer = new JoomlaSerializer('export');
        $element = (new Resource($exportData, $serializer));

        $this->document->setData($element);
        $this->document->addLink('self', Uri::current());

        return $this->document->render();
    }
}
