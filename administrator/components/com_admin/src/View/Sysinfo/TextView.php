<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Sysinfo;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Component\Admin\Administrator\Model\SysinfoModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Sysinfo View class for the Admin component
 *
 * @since  3.5
 */
class TextView extends AbstractView implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.5
     *
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        // Access check.
        if (!$this->getCurrentUser()->authorise('core.admin')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="systeminfo-' . date('c') . '.txt"');
        header('Cache-Control: must-revalidate');

        $data = $this->getLayoutData();

        $lines = [];

        foreach ($data as $sectionName => $section) {
            $customRenderingMethod = 'render' . ucfirst($sectionName);

            if (method_exists($this, $customRenderingMethod)) {
                $lines[] = $this->$customRenderingMethod($section['title'], $section['data']);
            } else {
                $lines[] = $this->renderSection($section['title'], $section['data']);
            }
        }

        echo str_replace(JPATH_ROOT, 'xxxxxx', implode("\n\n", $lines));

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
            'info' => [
                'title' => Text::_('COM_ADMIN_SYSTEM_INFORMATION', true),
                'data'  => $model->getSafeData('info'),
            ],
            'phpSettings' => [
                'title' => Text::_('COM_ADMIN_PHP_SETTINGS', true),
                'data'  => $model->getSafeData('phpSettings'),
            ],
            'config' => [
                'title' => Text::_('COM_ADMIN_CONFIGURATION_FILE', true),
                'data'  => $model->getSafeData('config'),
            ],
            'directories' => [
                'title' => Text::_('COM_ADMIN_DIRECTORY_PERMISSIONS', true),
                'data'  => $model->getSafeData('directory', true),
            ],
            'phpInfo' => [
                'title' => Text::_('COM_ADMIN_PHP_INFORMATION', true),
                'data'  => $model->getSafeData('phpInfoArray'),
            ],
            'extensions' => [
                'title' => Text::_('COM_ADMIN_EXTENSIONS', true),
                'data'  => $model->getSafeData('extensions'),
            ],
        ];
    }

    /**
     * Render a section
     *
     * @param   string   $sectionName  Name of the section to render
     * @param   array    $sectionData  Data of the section to render
     * @param   integer  $level        Depth level for indentation
     *
     * @return  string
     *
     * @since   3.5
     */
    protected function renderSection(string $sectionName, array $sectionData, int $level = 0): string
    {
        $lines = [];

        $margin = ($level > 0) ? str_repeat("\t", $level) : null;

        $lines[] = $margin . '=============';
        $lines[] = $margin . $sectionName;
        $lines[] = $margin . '=============';
        $level++;

        foreach ($sectionData as $name => $value) {
            if (\is_array($value)) {
                if ($name == 'Directive') {
                    continue;
                }

                $lines[] = '';
                $lines[] = $this->renderSection($name, $value, $level);
            } else {
                if (\is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                if (\is_int($name) && ($name == 0 || $name == 1)) {
                    // The term "Master" is used because it is the term used in phpinfo() and this is a text representation of that.
                    $name = ($name == 0 ? 'Local Value' : 'Master Value');
                }

                $lines[] = $margin . $name . ': ' . $value;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Specific rendering for directories
     *
     * @param   string   $sectionName  Name of the section
     * @param   array    $sectionData  Directories information
     * @param   integer  $level        Starting level
     *
     * @return  string
     *
     * @since   3.5
     */
    protected function renderDirectories(string $sectionName, array $sectionData, int $level = -1): string
    {
        foreach ($sectionData as $directory => $data) {
            $sectionData[$directory] = $data['writable'] ? ' writable' : ' NOT writable';
        }

        return $this->renderSection($sectionName, $sectionData, $level);
    }
}
