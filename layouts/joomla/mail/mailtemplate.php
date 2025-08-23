<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

// Check if we have all the data
if (!array_key_exists('mail', $displayData)) {
    return;
}

// Setting up for display
$mailBody = $displayData['mail'];

if (!$mailBody) {
    return;
}

$extraData = [];

if (array_key_exists('extra', $displayData)) {
    $extraData = $displayData['extra'];
}

$siteUrl = Uri::root(false);

?>
<!DOCTYPE html>
<html lang="<?php echo (isset($extraData['lang'])) ?  $extraData['lang'] : 'en' ?>" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="x-apple-disable-message-reformatting">
        <!--[if !mso]><!-->
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!--<![endif]-->
        <title></title>
        <!--[if mso]>
            <style>
                table {border-collapse:collapse;border-spacing:0;border:none;margin:0;}
                div, td {padding:0;}
                div {margin:0 !important;}
                </style>
            <noscript>
                <xml>
                <o:OfficeDocumentSettings>
                    <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
                </xml>
            </noscript>
            <![endif]-->
        <style>
            html {height: 100%;}
            table, td, div, h1, p { font-family: Arial, sans-serif; }
        </style>
    </head>
    <body style="margin:0;padding:0;word-spacing:normal;background-color:#efefef;height:100%;">
        <div role="article" aria-roledescription="email" style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#efefef;height:100%;">
            <table role="presentation" style="width:100%;border:none;border-spacing:0;height:100%;">
                <tr>
                    <td align="center" style="vertical-align:baseline; padding:30px 0">
                        <!--[if mso]>
                        <table role="presentation" align="center" style="width:630px;">
                        <tr>
                        <td>
                        <![endif]-->
                        <table role="presentation" style="width:94%;max-width:630px;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                            <tr>
                                <td style="padding:40px 30px 0 30px;text-align:center;font-size:24px;font-weight:bold;background-color:#ffffff;">
                                <?php if (isset($extraData['logo']) || isset($extraData['siteName'])) : ?>
                                    <?php if (isset($extraData['logo'])) : ?>
                                    <img src="cid:<?php echo htmlspecialchars($extraData['logo'], ENT_QUOTES);?>" alt="<?php echo (isset($extraData['siteName']) ? $extraData['siteName'] . ' ' : '');?>Logo" style="max-width:80%;height:auto;border:none;text-decoration:none;color:#ffffff;">
                                    <?php else : ?>
                                    <h1 style="margin-top:0;margin-bottom:0;font-size:26px;line-height:32px;font-weight:bold;letter-spacing:-0.02em;color:#112855;">
                                        <?php echo $extraData['siteName']; ?>
                                    </h1>
                                    <?php endif; ?>
                                    <table style="width:100%;">
                                        <tr>
                                            <td style="border:15px solid #ffffff;"></td>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid #efefef;background-color:#efefef;padding:0;"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:30px;background-color:#ffffff;">
                                <?php endif; ?>
                                    <?php echo $mailBody; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:30px;text-align:center;font-size:12px;background-color:#112855;color:#cccccc;">
                                    <p style="margin:0;font-size:14px;line-height:20px;">&copy; <?php echo isset($extraData['siteName']) ? $extraData['siteName'] . ' ' : ''; ?><?php echo date("Y"); ?>
                                    <br><a title="<?php echo $siteUrl;?>" href="<?php echo $siteUrl; ?>" style="color:#cccccc;text-decoration:underline;"><?php echo $siteUrl; ?></a>
                                </td>
                            </tr>
                        </table>
                        <!--[if mso]>
                        </td>
                        </tr>
                        </table>
                        <![endif]-->
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
