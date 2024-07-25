<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Banners list controller class.
 *
 * @since  1.6
 */
class BannersController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_BANNERS_BANNERS';

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('sticky_unpublish', 'sticky_publish');
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Banner', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Stick items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function sticky_publish()
    {
        // Check for request forgeries.
        $this->checkToken();

        $ids    = (array) $this->input->get('cid', [], 'int');
        $values = ['sticky_publish' => 1, 'sticky_unpublish' => 0];
        $task   = $this->getTask();
        $value  = ArrayHelper::getValue($values, $task, 0, 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (empty($ids)) {
            $this->app->enqueueMessage(Text::_('COM_BANNERS_NO_BANNERS_SELECTED'), 'warning');
        } else {
            // Get the model.
            /** @var \Joomla\Component\Banners\Administrator\Model\BannerModel $model */
            $model = $this->getModel();

            // Change the state of the records.
            if (!$model->stick($ids, $value)) {
                $this->app->enqueueMessage($model->getError(), 'warning');
            } else {
                if ($value == 1) {
                    $ntext = 'COM_BANNERS_N_BANNERS_STUCK';
                } else {
                    $ntext = 'COM_BANNERS_N_BANNERS_UNSTUCK';
                }

                $this->setMessage(Text::plural($ntext, \count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_banners&view=banners');
    }

    /**
     * Method to get the number of published banners for quickicons
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('banners');

        $model->setState('filter.published', 1);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_BANNERS_N_QUICKICON_SRONLY', $amount);
        $result['name']   = Text::plural('COM_BANNERS_N_QUICKICON', $amount);

        echo new JsonResponse($result);
    }
}
