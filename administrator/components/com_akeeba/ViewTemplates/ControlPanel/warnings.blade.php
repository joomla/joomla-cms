<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

$cloudFlareTestFile = 'CLOUDFLARE::' . $this->getContainer()->template->parsePath('media://com_akeeba/js/ControlPanel.min.js');
$cloudFlareTestFile .= '?' . $this->getContainer()->mediaVersion;

?>
{{-- Configuration Wizard pop-up --}}
@if($this->promptForConfigurationWizard)
    @include('admin:com_akeeba/Configuration/confwiz_modal')
@endif

{{-- Stuck database updates warning --}}
@if ($this->stuckUpdates)
    <div class="akeeba-block--warning">
        <p>
            @sprintf('COM_AKEEBA_CPANEL_ERR_UPDATE_STUCK', $this->getContainer()->db->getPrefix(), 'index.php?option=com_akeeba&view=ControlPanel&task=forceUpdateDb')
        </p>
    </div>
@endif

{{-- Potentially web accessible output directory --}}
@if ($this->isOutputDirectoryUnderSiteRoot)
    <!--
    Oh, hi there! It looks like you got curious and are peeking around your browser's developer tools – or just the
    source code of the page that loaded on your browser. Cool! May I explain what we are seeing here?

    Just to let you know, the next three DIVs (outDirSystem, insecureOutputDirectory and missingRandomFromFilename) are
    HIDDEN and their existence doesn't mean that your site has an insurmountable security issue. To the contrary.
    Whenever Akeeba Backup detects that the backup output directory is under your site's root it will CHECK its security
    i.e. if it's really accessible over the web. This check is performed with an AJAX call to your browser so if it
    takes forever or gets stuck you won't see a frustrating blank page in your browser. If AND ONLY IF a problem is
    detected said JavaScript will display one of the following DIVs, depending on what is applicable.

    So, to recap. These hidden DIVs? They don't indicate a problem with your site. If one becomes visible then – and
    ONLY then – should you do something about it, as instructed. But thank you for being curious. Curiosity is how you
    get involved with and better at web development. Stay curious!
    -->
    {{-- Web accessible output directory that coincides with or is inside in a CMS system folder --}}
    <div class="akeeba-block--failure" id="outDirSystem" style="display: none">
        <h3>@lang('COM_AKEEBA_CPANEL_HEAD_OUTDIR_INVALID')</h3>
        <p>
            @sprintf('COM_AKEEBA_CPANEL_LBL_OUTDIR_LISTABLE', realpath($this->getModel()->getOutputDirectory()))
        </p>
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_ISSYSTEM')
        </p>
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_ISSYSTEM_FIX')
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_DELETEORBEHACKED')
        </p>
    </div>

    {{-- Output directory can be listed over the web --}}
    <div class="akeeba-block--{{ $this->hasOutputDirectorySecurityFiles ? 'failure' : 'warning' }}" id="insecureOutputDirectory" style="display: none">
        <h3>
            @if ($this->hasOutputDirectorySecurityFiles)
            @lang('COM_AKEEBA_CPANEL_HEAD_OUTDIR_UNFIXABLE')
            @else
            @lang('COM_AKEEBA_CPANEL_HEAD_OUTDIR_INSECURE')
            @endif
        </h3>
        <p>
            @sprintf('COM_AKEEBA_CPANEL_LBL_OUTDIR_LISTABLE', realpath($this->getModel()->getOutputDirectory()))
        </p>
        @if (!$this->hasOutputDirectorySecurityFiles)
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_CLICKTHEBUTTON')
        </p>
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_FIX_SECURITYFILES')
        </p>

        <form action="index.php" method="POST" class="akeeba-form--inline">
            <input type="hidden" name="option" value="com_akeeba">
            <input type="hidden" name="view" value="ControlPanel">
            <input type="hidden" name="task" value="fixOutputDirectory">
            <input type="hidden" name="@token()" value="1">

            <button type="submit" class="akeeba-btn--block--green">
                <span class="akion-hammer"></span>
                @lang('COM_AKEEBA_CPANEL_BTN_FIXSECURITY')
            </button>
        </form>
        @else
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_TRASHHOST')
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_DELETEORBEHACKED')
        </p>
        @endif
    </div>

    {{-- Output directory cannot be listed over the web but I can download files --}}
    <div class="akeeba-block--warning" id="missingRandomFromFilename" style="display: none">
        <h3>
            @lang('COM_AKEEBA_CPANEL_HEAD_OUTDIR_INSECURE_ALT')
        </h3>
        <p>
            @sprintf('COM_AKEEBA_CPANEL_LBL_OUTDIR_FILEREADABLE', realpath($this->getModel()->getOutputDirectory()))
        </p>
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_CLICKTHEBUTTON')
        </p>
        <p>
            @lang('COM_AKEEBA_CPANEL_LBL_OUTDIR_FIX_RANDOM')
        </p>

        <form action="index.php" method="POST" class="akeeba-form--inline">
            <input type="hidden" name="option" value="com_akeeba">
            <input type="hidden" name="view" value="ControlPanel">
            <input type="hidden" name="task" value="addRandomToFilename">
            <input type="hidden" name="@token()" value="1">

            <button type="submit" class="akeeba-btn--block--green">
                <span class="akion-hammer"></span>
                @lang('COM_AKEEBA_CPANEL_BTN_FIXSECURITY')
            </button>
        </form>
    </div>

@endif

{{-- mbstring warning --}}
@unless($this->checkMbstring)
    <div class="akeeba-block--warning">
        @sprintf('COM_AKEEBA_CPANL_ERR_MBSTRING', PHP_VERSION)
    </div>
@endunless

{{-- Front-end backup secret word reminder --}}
@unless(empty($this->frontEndSecretWordIssue))
    <div class="akeeba-block--failure">
        <h3>@lang('COM_AKEEBA_CPANEL_ERR_FESECRETWORD_HEADER')</h3>
        <p>@lang('COM_AKEEBA_CPANEL_ERR_FESECRETWORD_INTRO')</p>
        <p>{{ $this->frontEndSecretWordIssue }}</p>
        <p>
            @lang('COM_AKEEBA_CPANEL_ERR_FESECRETWORD_WHATTODO_JOOMLA')
            @sprintf('COM_AKEEBA_CPANEL_ERR_FESECRETWORD_WHATTODO_COMMON', $this->newSecretWord)
        </p>
        <p>
            <a class="akeeba-btn--green akeeba-btn--big"
               href="index.php?option=com_akeeba&view=ControlPanel&task=resetSecretWord&@token(true)=1">
                <span class="akion-refresh"></span>
                @lang('COM_AKEEBA_CPANEL_BTN_FESECRETWORD_RESET')
            </a>
        </p>
    </div>
@endunless

{{-- Old PHP version reminder --}}
@include('admin:com_akeeba/ControlPanel/warning_phpversion')

{{-- Wrong media directory permissions --}}
@unless($this->areMediaPermissionsFixed)
    <div id="notfixedperms" class="akeeba-block--failure">
        <h3>@lang('COM_AKEEBA_CONTROLPANEL_WARN_WARNING')</h3>
        <p>@lang('COM_AKEEBA_CONTROLPANEL_WARN_PERMS_L1')</p>
        <p>@lang('COM_AKEEBA_CONTROLPANEL_WARN_PERMS_L2')</p>
        <ol>
            <li>@lang('COM_AKEEBA_CONTROLPANEL_WARN_PERMS_L3A')</li>
            <li>@lang('COM_AKEEBA_CONTROLPANEL_WARN_PERMS_L3B')</li>
        </ol>
        <p>@lang('COM_AKEEBA_CONTROLPANEL_WARN_PERMS_L4')</p>
    </div>
@endunless

{{-- You need to enter your Download ID --}}
@if($this->needsDownloadID)
    <div class="akeeba-block--warning">
        <h3>
            @lang('COM_AKEEBA_CPANEL_MSG_MUSTENTERDLID')
        </h3>
        <p>
            @sprintf('COM_AKEEBA_LBL_CPANEL_NEEDSDLID','https://www.akeeba.com/download/official/add-on-dlid.html')
        </p>
        <form name="dlidform" action="index.php" method="post" class="akeeba-form--inline">
            <input type="hidden" name="option" value="com_akeeba" />
            <input type="hidden" name="view" value="ControlPanel" />
            <input type="hidden" name="task" value="applydlid" />
            <input type="hidden" name="@token(true)" value="1" />
            <div class="akeeba-form-group">
                <label for="dlid">@lang('COM_AKEEBA_CPANEL_MSG_PASTEDLID')</label>
                <input type="text" name="dlid" placeholder="@lang('COM_AKEEBA_CONFIG_DOWNLOADID_LABEL')"
                       class="akeeba-input--wide">

                <button type="submit" class="akeeba-btn--green">
                    <span class="akion-checkmark-round"></span>
                    @lang('COM_AKEEBA_CPANEL_MSG_APPLYDLID')
                </button>
            </div>
        </form>
    </div>
@endif

{{-- You have CORE; you need to upgrade, not just enter a Download ID --}}
@if($this->coreWarningForDownloadID)
    <div class="akeeba-block--warning">
        @sprintf('COM_AKEEBA_LBL_CPANEL_NEEDSUPGRADE','http://akee.ba/abcoretopro')
    </div>
@endif

{{-- Warn about CloudFlare Rocket Loader --}}
<div class="akeeba-block--failure" style="display: none;" id="cloudFlareWarn">
    <h3>@lang('COM_AKEEBA_CPANEL_MSG_CLOUDFLARE_WARN')</h3>
    <p>@sprintf('COM_AKEEBA_CPANEL_MSG_CLOUDFLARE_WARN1', 'https://support.cloudflare.com/hc/en-us/articles/200169456-Why-is-JavaScript-or-jQuery-not-working-on-my-site-')</p>
</div>
<?php
/**
 * DO NOT USE INLINE JAVASCRIPT FOR THIS SCRIPT. DO NOT REMOVE THE ATTRIBUTES.
 *
 * This is a specialised test which looks for CloudFlare's completely broken RocketLoader feature and warns the user
 * about it.
 */
?>
<script type="text/javascript" data-cfasync="true">
    var test = localStorage.getItem('<?php echo $cloudFlareTestFile?>');
    if (test)
    {
        document.getElementById("cloudFlareWarn").style.display = "block";
    }
</script>
