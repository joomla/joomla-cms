<?php

$git = 'git';

function insertDefineOrDie($file, $keyword)
{
    global $jexecfound, $skipped;

    $realfile           = dirname(__DIR__) . '/' . $file;

    if (!file_exists($realfile)) {
        if ($file === 'plugins/task/checkfiles/checkfiles.php') {
            return;
        }
    }

    $currentcontent     = file($realfile);
    $lastUse            = 0;
    $lastComment        = 0;
    $lastNamespace      = 0;
    foreach ($currentcontent as $k => $line) {
        if ($k > 200) {
            // we only test the first 200 lines for a jexec die
            break;
        }
        if (substr($line, 0, 4) === 'use ') {
            $lastUse = $k;
        }
        if (substr($line, 0, 10) === 'namespace ') {
            $lastNamespace = $k;
        }
        if ($lastComment === 0 && substr(trim($line), -2) === '*/') {
            $lastComment = $k;
        }

        if (preg_match('/^[ \t\\\]*(defined).*(_JEXEC|JPATH_PLATFORM|JPATH_BASE).*/', $line, $matches)) {
            $jexecfound[$file] = $file;
            unset($skipped[$file]);

            return;
        }
    }

    $insert = max($lastUse, $lastComment, $lastNamespace);

    $distance = 0;
    if (empty(trim($currentcontent[$insert + 2]))) {
        $distance++;
        if (empty(trim($currentcontent[$insert + 3]))) {
            $distance++;
        }
    }

    array_splice($currentcontent, $insert + 1, $distance, [
        chr(10),
        "// phpcs:disable PSR1.Files.SideEffects" . chr(10),
        "\defined('" . $keyword . "') or die;" . chr(10),
        "// phpcs:enable PSR1.Files.SideEffects" . chr(10),
    ]);

    file_put_contents($realfile, implode('', $currentcontent));
}

$additional = [
    "administrator/components/com_installer/src/Field/PackageField.php",
    "administrator/components/com_scheduler/src/Task/Status.php",
    "administrator/components/com_users/src/Controller/CallbackController.php",
    "administrator/components/com_users/src/Controller/CaptiveController.php",
    "administrator/components/com_users/src/Controller/MethodController.php",
    "administrator/components/com_users/src/Controller/MethodsController.php",
    "administrator/components/com_users/src/DataShape/CaptiveRenderOptions.php",
    "administrator/components/com_users/src/DataShape/MethodDescriptor.php",
    "administrator/components/com_users/src/DataShape/SetupRenderOptions.php",
    "administrator/components/com_users/src/Helper/Mfa.php",
    "administrator/components/com_users/src/Model/BackupcodesModel.php",
    "administrator/components/com_users/src/Model/CaptiveModel.php",
    "administrator/components/com_users/src/Model/MethodModel.php",
    "administrator/components/com_users/src/Model/MethodsModel.php",
    "administrator/components/com_users/src/Service/Encrypt.php",
    "administrator/components/com_users/src/Table/MfaTable.php",
    "administrator/components/com_users/src/View/Captive/HtmlView.php",
    "administrator/components/com_users/src/View/Method/HtmlView.php",
    "administrator/components/com_users/src/View/Methods/HtmlView.php",
    "administrator/components/com_users/src/View/SiteTemplateTrait.php",
    "components/com_users/src/Controller/CallbackController.php",
    "components/com_users/src/Controller/CaptiveController.php",
    "components/com_users/src/Controller/MethodController.php",
    "components/com_users/src/Controller/MethodsController.php",
    "components/com_users/src/Model/BackupcodesModel.php",
    "components/com_users/src/Model/CaptiveModel.php",
    "components/com_users/src/Model/MethodModel.php",
    "components/com_users/src/Model/MethodsModel.php",
    "components/com_users/src/View/Captive/HtmlView.php",
    "components/com_users/src/View/Method/HtmlView.php",
    "components/com_users/src/View/Methods/HtmlView.php",
    "layouts/joomla/button/action-button.php",
    "layouts/joomla/button/transition-button.php",
    "layouts/joomla/form/field/color/slider.php",
    "libraries/src/Application/CMSApplicationInterface.php",
    "libraries/src/Application/EventAwareInterface.php",
    "libraries/src/Application/MultiFactorAuthenticationHandler.php",
    "libraries/src/Button/ActionButton.php",
    "libraries/src/Button/FeaturedButton.php",
    "libraries/src/Button/PublishedButton.php",
    "libraries/src/Button/TransitionButton.php",
    "libraries/src/Categories/SectionNotFoundException.php",
    "libraries/src/Error/JsonApi/CheckinCheckoutExceptionHandler.php",
    "libraries/src/Error/JsonApi/ResourceNotFoundExceptionHandler.php",
    "libraries/src/Error/JsonApi/SaveExceptionHandler.php",
    "libraries/src/Error/JsonApi/SendEmailExceptionHandler.php",
    "libraries/src/Event/Plugin/System/Webauthn/Ajax.php",
    "libraries/src/Event/Plugin/System/Webauthn/AjaxChallenge.php",
    "libraries/src/Event/Plugin/System/Webauthn/AjaxCreate.php",
    "libraries/src/Event/Plugin/System/Webauthn/AjaxDelete.php",
    "libraries/src/Event/Plugin/System/Webauthn/AjaxInitCreate.php",
    "libraries/src/Event/Plugin/System/Webauthn/AjaxLogin.php",
    "libraries/src/Event/Plugin/System/Webauthn/AjaxSaveLabel.php",
    "libraries/src/MVC/Factory/MVCFactoryAwareTrait.php",
    "libraries/src/Toolbar/Button/AbstractGroupButton.php",
    "libraries/src/Toolbar/CoreButtonsTrait.php",
    "plugins/multifactorauth/email/src/Extension/Email.php",
    "plugins/multifactorauth/fixed/src/Extension/Fixed.php",
    "plugins/multifactorauth/totp/src/Extension/Totp.php",
    "plugins/multifactorauth/webauthn/src/CredentialRepository.php",
    "plugins/multifactorauth/webauthn/src/Extension/Webauthn.php",
    "plugins/multifactorauth/webauthn/src/Helper/Credentials.php",
    "plugins/multifactorauth/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php",
    "plugins/multifactorauth/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php",
    "plugins/multifactorauth/webauthn/src/Hotfix/Server.php",
    "plugins/multifactorauth/yubikey/src/Extension/Yubikey.php",
    "plugins/system/debug/src/AbstractDataCollector.php",
    "plugins/system/debug/src/DataCollector/InfoCollector.php",
    "plugins/system/debug/src/DataCollector/LanguageErrorsCollector.php",
    "plugins/system/debug/src/DataCollector/LanguageFilesCollector.php",
    "plugins/system/debug/src/DataCollector/LanguageStringsCollector.php",
    "plugins/system/debug/src/DataCollector/ProfileCollector.php",
    "plugins/system/debug/src/DataCollector/QueryCollector.php",
    "plugins/system/debug/src/DataCollector/SessionCollector.php",
    "plugins/system/debug/src/DataFormatter.php",
    "plugins/system/debug/src/JavascriptRenderer.php",
    "plugins/system/debug/src/JoomlaHttpDriver.php",
    "plugins/system/debug/src/Storage/FileStorage.php",
    "plugins/system/updatenotification/postinstall/updatecachetime.php",
    "plugins/task/checkfiles/services/provider.php",
    "plugins/task/checkfiles/src/Extension/Checkfiles.php",
];


$cmd    = $git . ' diff --name-only 6d9cc0fe..psr12final';
$output = [];
exec($cmd, $output, $result);
if ($result !== 0) {
    var_dump([$cmd, $output, $result]);
    echo "Error";
    die();
}
$files      = [];
$skipped    = [];
$nojexec    = [];
$jexecfound = [];
foreach ($output as $file) {
    if (substr($file, -4) !== '.php') {
        continue;
    }

    $skipped[$file] = $file;
    $cmd            = $git . ' show 6d9cc0fe:' . $file;
    $content        = [];
    exec($cmd, $content, $result);
    if ($result !== 0) {
        var_dump([$cmd, $content, $result]);
        echo "Error";
        die();
    }

    $keyword = '';
    foreach ($content as $k => $line) {
        if ($k > 200) {
            // we only test the first 200 lines for a jexec die
            break;
        }
        if (preg_match('/^[ \t\\\]*(defined).*(_JEXEC|JPATH_PLATFORM|JPATH_BASE).*/', $line, $matches)) {
            $keyword = $matches[2];
            break;
        }
    }

    if ($keyword === '') {
        if (!in_array($file, $additional)) {
            $nojexec[$file] = $file;
            unset($skipped[$file]);
            continue;
        }

        $keyword = '_JEXEC';
    }

    insertDefineOrDie($file, $keyword);
    unset($skipped[$file]);
}

$keyword = '_JEXEC';
insertDefineOrDie('plugins/task/checkfiles/services/provider.php', $keyword);
insertDefineOrDie('plugins/task/checkfiles/src/Extension/Checkfiles.php', $keyword);





var_dump([$skipped, $nojexec, $jexecfound]);
