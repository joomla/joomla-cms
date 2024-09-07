<?php

/**
 * @package     Joomla.API
 * @subpackage  com_installer
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Api\View\Languages;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The languages view
 *
 * @since  5.2.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var  array
     * @since  5.2.0
     */
    protected $fieldsToRenderList = [
        'name',
        'element',
        'version',
        'type',
        'detailsurl',
    ];

    protected $i = 0;

    /**
     * Prepare item before render.
     *
     * @param   object  $item  The model item
     *
     * @return  object
     *
     * @since   5.2.0
     */
    protected function prepareItem($item)
    {
        $item->id = ++$this->i;

        return parent::prepareItem($item);
    }
}
