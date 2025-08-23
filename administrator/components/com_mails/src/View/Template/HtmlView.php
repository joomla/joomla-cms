<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\View\Template;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Mails\Administrator\Helper\MailsHelper;
use Joomla\Component\Mails\Administrator\Model\TemplateModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a mail template.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The active item
     *
     * @var  \stdClass
     */
    protected $item;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * The template data
     *
     * @var  array
     */
    protected $templateData;

    /**
     * Master data for the mail template
     *
     * @var  CMSObject
     */
    protected $master;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function display($tpl = null)
    {
        /** @var TemplateModel $model */
        $model = $this->getModel();

        $this->state  = $model->getState();
        $this->item   = $model->getItem();
        $this->master = $model->getMaster();
        $this->form   = $model->getForm();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        [$extension, $template_id] = explode('.', $this->item->template_id, 2);
        $fields                    = ['subject', 'body', 'htmlbody'];
        $this->templateData        = [];

        MailsHelper::loadTranslationFiles($extension, $this->item->language);

        $this->master->subject = Text::_($this->master->subject);
        $this->master->body    = Text::_($this->master->body);

        if ($this->master->htmlbody) {
            $this->master->htmlbody = Text::_($this->master->htmlbody);
        } else {
            $this->master->htmlbody = nl2br($this->master->body, false);
        }

        $this->templateData = [
            'subject'  => $this->master->subject,
            'body'     => $this->master->body,
            'htmlbody' => $this->master->htmlbody,
        ];

        foreach ($fields as $field) {
            if (\is_null($this->item->$field) || $this->item->$field == '') {
                $this->item->$field = $this->master->$field;
                $this->form->setValue($field, null, $this->item->$field);
            }
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            Text::_('COM_MAILS_PAGE_EDIT_MAIL'),
            'pencil-2 article-add'
        );

        $toolbar->apply('template.apply');
        $toolbar->divider();
        $toolbar->save('template.save');
        $toolbar->divider();
        $toolbar->cancel('template.cancel', 'JTOOLBAR_CLOSE');
        $toolbar->divider();
        $toolbar->help('Mail_Template:_Edit');
    }
}
