<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Sysinfo;

use Exception;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\Component\Admin\Administrator\Model\SysinfoModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Sysinfo View class for the Admin component
 *
 * @since  3.5
 */
class JsonView extends AbstractView
{
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.5
     *
     * @throws  Exception
     */
    public function display($tpl = null): void
    {
        // Access check.
        if (!Factory::getUser()->authorise('core.admin')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        header('MIME-Version: 1.0');
        header('Content-Disposition: attachment; filename="systeminfo-' . date('c') . '.json"');
        header('Content-Transfer-Encoding: binary');

        $data = $this->getLayoutData();

        echo json_encode($data, JSON_PRETTY_PRINT);

        Factory::getApplication()->close();
    }

    /**
     * Get the data for the view
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutData(): array
    {
        /** @var SysinfoModel $model */
        $model = $this->getModel();

        return [
            'info'        => $model->getSafeData('info'),
            'phpSettings' => $model->getSafeData('phpSettings'),
            'config'      => $model->getSafeData('config'),
            'directories' => $model->getSafeData('directory', true),
            'phpInfo'     => $model->getSafeData('phpInfoArray'),
            'extensions'  => $model->getSafeData('extensions'),
        ];
    }
}
