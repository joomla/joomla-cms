<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * @var $state {}
 * @var $transitions []
 */
extract($displayData);

$canDo = ContentHelper::getActions('com_content', 'category', $state->get('filter.category_id'));
$user  = Factory::getUser();

// Get the toolbar object instance
$toolbar = Toolbar::getInstance('toolbar');

ToolbarHelper::title(Text::_('COM_CONTENT_ARTICLES_TITLE'), 'copy article');

if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_content', 'core.create')) > 0)
{
	$toolbar->addNew('article.add');
}

if ($canDo->get('core.edit.state') || count($transitions))
{
	$dropdown = $toolbar->dropdownButton('status-group')
		->text('JTOOLBAR_CHANGE_STATUS')
		->toggleSplit(false)
		->icon('icon-ellipsis-h')
		->buttonClass('btn btn-action')
		->listCheck(true);

	$childBar = $dropdown->getChildToolbar();

	if (count($transitions))
	{
		$childBar->separatorButton('transition-headline')
			->text('COM_CONTENT_RUN_TRANSITIONS')
			->buttonClass('text-center py-2 h3');

		$cmd = "Joomla.submitbutton('articles.runTransition');";
		$messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
		$alert = 'Joomla.renderMessages(' . $messages . ')';
		$cmd   = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

		foreach ($transitions as $transition)
		{
			$childBar->standardButton('transition')
				->text($transition['text'])
				->buttonClass('transition-' . (int) $transition['value'])
				->icon('icon-project-diagram')
				->onclick('document.adminForm.transition_id.value=' . (int) $transition['value'] . ';' . $cmd);
		}

		$childBar->separatorButton('transition-separator');
	}

	if ($canDo->get('core.edit.state'))
	{
		$childBar->publish('articles.publish')->listCheck(true);

		$childBar->unpublish('articles.unpublish')->listCheck(true);

		$childBar->standardButton('featured')
			->text('JFEATURE')
			->task('articles.featured')
			->listCheck(true);

		$childBar->standardButton('unfeatured')
			->text('JUNFEATURE')
			->task('articles.unfeatured')
			->listCheck(true);

		$childBar->archive('articles.archive')->listCheck(true);

		$childBar->checkin('articles.checkin')->listCheck(true);

		if ($state->get('filter.published') != ContentComponent::CONDITION_TRASHED)
		{
			$childBar->trash('articles.trash')->listCheck(true);
		}
	}

	// Add a batch button
	if ($user->authorise('core.create', 'com_content')
		&& $user->authorise('core.edit', 'com_content')
		&& $user->authorise('core.execute.transition', 'com_content'))
	{
		$childBar->popupButton('batch')
			->text('JTOOLBAR_BATCH')
			->selector('collapseModal')
			->listCheck(true);
	}
}

if ($state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
{
	$toolbar->delete('articles.delete')
		->text('JTOOLBAR_EMPTY_TRASH')
		->message('JGLOBAL_CONFIRM_DELETE')
		->listCheck(true);
}

if ($user->authorise('core.admin', 'com_content') || $user->authorise('core.options', 'com_content'))
{
	$toolbar->preferences('com_content');
}

$toolbar->help('JHELP_CONTENT_ARTICLE_MANAGER');
