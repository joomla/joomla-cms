<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

?>

<h1>Submit review - <?php echo $displayData->title; ?></h1>

<div id="guidelines">
    <h2>What are the guidelines for a review?</h2>

    <p>You are evaluating the extension based on your experience and usage. <strong>You MUST first use an extension prior to submitting a review</strong>. Only reviews that meet these guidelines will be published: </p>
    <div class="row">
        <div class="col-md-6 alert alert-success">
            <h3><span class="fa fa-check"></span> Allowed and Encouraged</h3>
            <ul class='review-allowed-list'>
                <li>A full evaluation of the extension</li>
                <li>Courtesy and Honesty</li>
                <li>Pros &amp; Cons of the extension and its performance</li>
                <li>Level of support received</li>
                <li>Ease of usage and deployment</li>
                <li>Purpose of using the extension (i.e., I used this extension for a magazine site.)</li>
                <li>Level of difficulty</li>
                <li>Your experience level with Joomla and web technologies</li>
            </ul>
        </div>
        <div class="col-md-6 alert alert-error">
            <h3><span class="fa fa-times"></span> Not allowed</h3>
            <ul class='review-disallowed-list'>
                <li>Commercial disputes</li>
                <li>Questions to the Developer or Others</li>
                <li>Support/Feature Requests</li>
                <li>Error Messages</li>
                <li>Links</li>
                <li>Code or Bug Reports</li>
                <li>Questionable Language (including cursing)</li>
                <li>Submissions via an IP masking service or VPN</li>
                <li>One-line Reviews</li>
                <li>Self promotion or spam</li>
            </ul>
        </div>
    </div>
    </div>
 </div>
<div><button id="reviewBtn" >Continue</button></div>
