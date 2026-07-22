<?php
/**
 * TPMS Auth Head Snippet
 * Included at the top of every auth page <head> to apply the saved theme
 * without flash and load dark-mode.css.
 */
$_authTheme = $_SESSION['user_theme'] ?? $_COOKIE['tpms_theme'] ?? 'light';
$_authAllowed = ['light','dark','blue','purple','emerald','sunset','midnight','glassmorphism'];
if (!in_array($_authTheme, $_authAllowed, true)) $_authTheme = 'light';
?>
<script>/* TPMS instant theme – no flash */(function(){var a=['light','dark','blue','purple','emerald','sunset','midnight','glassmorphism'],s=null;try{s=localStorage.getItem('tpms_theme');}catch(e){}if(!s){var m=document.cookie.match(/(?:^|;\s*)tpms_theme=([^;]+)/);s=m?m[1]:null;}if(!s||a.indexOf(s)<0)s='<?= $_authTheme ?>';document.documentElement.setAttribute('data-theme',s);try{localStorage.setItem('tpms_theme',s);}catch(e){}document.cookie='tpms_theme='+s+'; path=/; max-age=31536000; SameSite=Lax';})();</script>
