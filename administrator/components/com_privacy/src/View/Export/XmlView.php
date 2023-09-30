<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\View\Export;

use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\Component\Privacy\Administrator\Helper\PrivacyHelper;
use Joomla\Component\Privacy\Administrator\Model\ExportModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Export view class
 *
 * @since  3.9.0
 *
 * @property-read   \Joomla\CMS\Document\XmlDocument  $document
 */
class XmlView extends AbstractView
{
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.9.0
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        /** @var ExportModel $model */
        $model = $this->getModel();

        $exportData = $model->collectDataForExportRequest();

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $requestId = $model->getState($model->getName() . '.request_id');

        // This document should always be downloaded
        $this->getDocument()->setDownload(true);
        $this->getDocument()->setName('export-request-' . $requestId);

        echo PrivacyHelper::renderDataAsXml($exportData);
    }
}
