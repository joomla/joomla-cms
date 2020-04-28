<?php
defined('JPATH_BASE') or die;

// Convert icomoon to fa
$icon = $displayData['icon'];
if ( $icon === 'archive' )
{
$icon = 'folder';
}

if ( $icon === 'publish' )
{
$icon = 'check';
}

if ( $icon === 'unpublish' || $icon === 'cancel' || $icon === 'delete' || $icon === 'remove' )
{
$icon = 'times';
}

if ( $icon === 'new' || $icon === 'save-new' )
{
$icon = 'plus';
}

if ( $icon === 'apply' )
{
$icon = 'save';
}

if ( $icon === 'mail' )
{
$icon = 'envelope';
}

if ( $icon === 'featured' || $icon === 'unfeatured' || $icon === 'asterisk' || $icon === 'default' )
{
$icon = 'star';
}

if ( $icon === 'checkedout' )
{
$icon = 'lock';
}

if ( $icon === 'eye-close' )
{
$icon = 'eye-slash';
}

if ( $icon === 'eye-open' )
{
$icon = 'eye';
}

if ( $icon === 'loop' || $icon === 'refresh' || $icon === 'unblock')
{
$icon = 'sync';
}

if ( $icon === 'contract' )
{
$icon = 'compress';
}

if ( $icon === 'purge' )
{
$icon = 'trash';
}

if ( $icon === 'options' )
{
$icon = 'cog';
}

if ( $icon === 'expired' )
{
$icon = 'minus-circle';
}

if ( $icon === 'save-copy' )
{
$icon = 'copy';
}

if ( $icon === 'checkin' )
{
$icon = 'check-square';
}

if ( $icon === 'generic' )
{
$icon = 'dot-circle';
}

if ( $icon === 'list-2' )
{
$icon = 'fa-list-ul';
}

?>
fas fa-<?php echo $icon;
