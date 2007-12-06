<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','tt_content.CSS_editor.ch.tx_cegallery_pi1 = < plugin.tx_cegallery_pi1.CSS_editor',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cegallery_pi1.php','_pi1','list_type',1);

$ce_gallery_conf = unserialize($_EXTCONF);
t3lib_extMgm::addTypoScriptConstants('extension.ce_gallery.typeNum = ' . $ce_gallery_conf['typeNum']);
unset($ce_gallery_conf);


if(t3lib_extMgm::isLoaded('pmkslimbox')) {
	$tmp_EXTKEY = $_EXTKEY;
	$_EXTKEY = 'pmkslimbox';
	require_once(PATH_site . t3lib_extMgm::siteRelPath($_EXTKEY) . 'ext_emconf.php');
	if (t3lib_div::int_from_ver($GLOBALS['EM_CONF'][$_EXTKEY]['version']) >= 1001000) {
		$content = 'plugin.pmkslimbox.iframeScrolling = no';
		$staticFile = $_EXTKEY . '/static/SlimBox/';
		t3lib_extMgm::addTypoScript($tmp_EXTKEY, 'constants', $content, $staticFile);
	}
	$_EXTKEY = $tmp_EXTKEY;
	unset($tmp_EXTKEY);
}
?>