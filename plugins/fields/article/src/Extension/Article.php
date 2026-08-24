<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.article
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Article\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\CustomFields\PrepareDomEvent;
use Joomla\CMS\Event\CustomFields\PrepareFieldEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Article Fields Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class Article extends FieldsPlugin implements SubscriberInterface
{
    /**
     * Prepares the field value.
     *
     * @param   PrepareFieldEvent  $event  The event instance.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function prepareField(PrepareFieldEvent $event): void
    {
        $field = $event->getField();

        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        $articleId = (int) ($field->rawvalue ?? 0);

        if (!$articleId) {
            return;
        }

        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = $this->getApplication();

        $articleModel = $app->bootComponent('com_content')
            ->getMVCFactory()
            ->createModel('Article', 'Site', ['ignore_request' => true]);

        // Clone, the component params are a shared instance and must not be modified.
        $params = clone ComponentHelper::getParams('com_content');

        // Works like the equivalent menu item setting: an explicit value wins, 'use_article'
        // lets ArticleModel::getItem() resolve it from the article, empty keeps the global one.
        $showNoauth = (string) $field->params->get('show_noauth', '');

        if ($showNoauth !== '') {
            $params->set('show_noauth', $showNoauth);
        }

        $articleModel->setState('params', $params);

        $user  = $app->getIdentity();
        $asset = 'com_content.article.' . $articleId;

        // populateState() is skipped (ignore_request), so replicate its access check here -
        // deliberately against the referenced article, not the one from the request.
        if (!$user->authorise('core.edit.state', $asset) && !$user->authorise('core.edit', $asset)) {
            $articleModel->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);
            $articleModel->setState('filter.archived', ContentComponent::CONDITION_ARCHIVED);
        }

        // @todo Decide whether 'filter.language' should be set here as populateState() does.
        // Without it a referenced article is shown on multilingual sites even when its language
        // does not match the active one.

        try {
            // ArticleModel::getItem() throws when the article is filtered out (e.g. unpublished, trashed, or missing).
            $article = $articleModel->getItem($articleId);
        } catch (\Exception $e) {
            return;
        }

        // Trashed articles are treated as deleted and are never displayed, regardless of the viewer's permissions.
        if (!$article || !isset($article->id) || $article->state == ContentComponent::CONDITION_TRASHED) {
            return;
        }

        if (!$field->params->get('show_archived', 1) && $article->state == ContentComponent::CONDITION_ARCHIVED) {
            return;
        }

        $currentDate = Factory::getDate()->format('Y-m-d H:i:s');

        $isUnpublished = ($article->state == ContentComponent::CONDITION_UNPUBLISHED || $article->publish_up > $currentDate)
            || ($article->publish_down !== null && $article->publish_down < $currentDate);

        if ($isUnpublished && !$article->params->get('access-edit')) {
            return;
        }

        // Mirrors the check in com_content's article view: with show_noauth disabled a restricted
        // article must not be revealed at all, not even its title behind a login link.
        if (
            !$article->params->get('access-view')
            && !$article->params->get('access-edit')
            && !$article->params->get('show_noauth', 0)
        ) {
            return;
        }

        $article->params->set('isUnpublished', $isUnpublished);

        $field->value = $article;

        // Let the parent render the layout and add the result to the event.
        parent::prepareField($event);
    }

    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   PrepareDomEvent  $event  The event instance.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function prepareDom(PrepareDomEvent $event): void
    {
        $field = $event->getField();

        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        $field->type = 'Modal_Article';

        // The parent event method discards the created node, so call the inner hook directly to get hold of it.
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $event->getFieldset(), $event->getForm());

        if (!$fieldNode) {
            return;
        }

        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = $this->getApplication();

        /** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $articleModel */
        $articleModel = $app->bootComponent('com_content')->getMVCFactory()
            ->createModel('Article', 'Administrator', ['ignore_request' => true]);

        $user = $app->getIdentity();

        $allowNew  = (bool) $field->fieldparams->get('allow_new', 1);
        $allowEdit = (bool) $field->fieldparams->get('allow_edit', 1);

        $canCreate = $allowNew && $user->authorise('core.create', 'com_content');
        $canEdit   = $allowEdit && $user->authorise('core.edit', 'com_content');

        $articleId = (int) ($field->rawvalue ?? 0);

        if ($articleId > 0) {
            // If the article id is set, load the article and check edit permissions
            $articleModel->setState('article.id', $articleId);
            $article = $articleModel->getItem();

            if ($article) {
                // Since we don't track these assets at the item level, use the category id.
                $canDo = ContentHelper::getActions('com_content', 'category', $article->catid);

                $canEdit = $allowEdit && ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $article->created_by == $user->id));

                // @todo Check out should be done in the core field itself, leave it here until it is fixed.
                $checkedOut = !(\is_null($article->checked_out) || $article->checked_out == $user->id);

                if ($canEdit && $checkedOut) {
                    $canEdit = false;
                    $fieldNode->setAttribute('description', Text::_('PLG_FIELDS_ARTICLE_CHECKOUT_MISMATCH_DESC'));
                }
            }
        }

        $fieldNode->setAttribute('validate', 'ArticleId');
        $fieldNode->setAttribute('select', 'true');
        $fieldNode->setAttribute('new', $canCreate ? 'true' : 'false');
        $fieldNode->setAttribute('edit', $canEdit ? 'true' : 'false');
        $fieldNode->setAttribute('clear', 'true');
        $fieldNode->setAttribute('addfieldprefix', 'Joomla\\Component\\Content\\Administrator\\Field');
        $fieldNode->setAttribute('addruleprefix', 'Joomla\\CMS\\Form\\Rule');

        // Restrict the article selection modal to the language of the item being edited.
        // The form is not bound yet at prepareDom time (Form::bind() runs after preprocessForm()),
        // so the language can only be taken from the request. As a result the restriction applies on
        // form round trips (e.g. after a failed validation) but not on the initial load of the edit form.
        // @todo decide whether we want to restrict the modal to the language at all
        $jform    = $app->getInput()->get('jform', [], 'array');
        $language = $jform['language'] ?? '';

        if (\is_string($language) && $language !== '' && $language !== '*') {
            $fieldNode->setAttribute('language', $language);
        }
    }

    /**
     * Returns the custom fields types.
     *
     * This deliberately overrides the inner hook instead of the getFieldTypes() event method:
     * FieldsPlugin::isTypeSupported() calls onCustomFieldsGetTypes() directly, so overriding here
     * makes the com_content check a single choke point for both the event and the internal lookup.
     *
     * @return  string[][]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onCustomFieldsGetTypes()
    {
        if (!ComponentHelper::isEnabled('com_content')) {
            return [];
        }

        return parent::onCustomFieldsGetTypes();
    }

    /**
     * Returns true if the given type is supported by the plugin.
     *
     * @param   string  $type  The type
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function isTypeSupported($type): bool
    {
        if (!ComponentHelper::isEnabled('com_content')) {
            return false;
        }

        if ($type === 'Modal_Article') {
            return true;
        }

        return parent::isTypeSupported($type);
    }
}
