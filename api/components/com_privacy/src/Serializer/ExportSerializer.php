<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Api\Serializer;

use Joomla\CMS\Serializer\JoomlaSerializer;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy export serializer
 *
 * @since  __DEPLOY_VERSION__
 */
class ExportSerializer extends JoomlaSerializer
{
    /**
     * Override the model id
     *
     * @param   \stdClass  $model  Item model
     *
     * @return  \stdClass  $model  Item model
     *
     * @since __DEPLOY_VERSION__
     */
    public function getId($model)
    {
        $model['id'] = '1';
        return $model['id'];
    }

}