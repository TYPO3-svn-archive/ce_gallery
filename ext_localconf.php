<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','tt_content.CSS_editor.ch.tx_cegallery_pi1 = < plugin.tx_cegallery_pi1.CSS_editor',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_cegallery_pi1.php','_pi1','list_type',1);

$ce_gallery_conf = unserialize($_EXTCONF);
t3lib_extMgm::addTypoScriptConstants('extension.ce_gallery.typeNum = ' . $ce_gallery_conf['typeNum']);
unset($ce_gallery_conf);

if(t3lib_extMgm::isLoaded('pmkslimbox')) {
	require_once(PATH_site . t3lib_extMgm::siteRelPath('pmkslimbox') . 'ext_emconf.php');
	if (t3lib_div::int_from_ver($GLOBALS['EM_CONF']['pmkslimbox']['version']) >= 1001000)
		t3lib_extMgm::addTypoScript($_EXTKEY, 'constants',
			 'plugin.pmkslimbox.iframeScrolling = no',
			 'pmkslimbox/static/SlimBox/');
}
?>