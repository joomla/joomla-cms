<?php

/**
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\AdaptiveImage;

/**
 * Focus Store Interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface FocusStoreInterface
{
    /**
     * Pubic function for storing the focus points
     * to the file system.
     *
     * @param   array    $dataFocus  Focus point selected
     * @param   integer  $width      Width of the image
     * @param   string   $imgSrc     Path of the image
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function setFocus($dataFocus, $width, $imgSrc);

    /**
     * Public function for getting the focus point
     * from the file system
     *
     * @param   string   $imgSrc  Path of the image
     * @param   integer  $width   Width of the image
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    public function getFocus($imgSrc, $width);

    /**
     * Function for removing the focus points for all widths
     *
     * @param   string  $imgSrc  Path of the image
     *
     * @return  boolean
     *
     * @since __DEPLOY_VERSION__
     */
    public function deleteFocus($imgSrc);

    /**
     * Function for removing all the associated resized images
     *
     * @param   string  $imgSrc  Path of the image
     *
     * @return  boolean
     *
     * @since __DEPLOY_VERSION__
     */
    public function deleteResizedImages($imgSrc);
}
