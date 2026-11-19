<?php

/**
 * @package     Joomla.Libraries
 * @subpackage  Workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A namespace mapping transition outcomes to integer values.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class TransitionStatus
{
    /**
     * The transition ran, or there was nothing to move.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const SUCCESS = 0;

    /**
     * The transition does not exist, or the current user may not run it.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const INVALID_TRANSITION = 1;

    /**
     * An item is not in the stage the transition starts from, or belongs to another workflow.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const STAGE_MISMATCH = 2;

    /**
     * A plugin vetoed the transition through onWorkflowBeforeTransition.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const STOPPED_BY_PLUGIN = 3;

    /**
     * The stage change itself could not be written.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const UPDATE_FAILED = 4;
}
