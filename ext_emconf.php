<?php

########################################################################
# Extension Manager/Repository config file for ext: "ce_gallery"
#
# Auto generated 23-12-2007 20:57
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Photogallery',
	'description' => 'Photogallery with AJAX based Slideshow - uses DAM and DAM categories. Thumbnails are automatically generated and cached. PKM Slimbox can be integrated. Easy configuration.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.0.5',
	'dependencies' => 'cms,dam,dam_catedit',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/ce_gallery',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Christian Ehret',
	'author_email' => 'chris@ehret.name',
	'author_company' => 'Dipl.-Ing. Christian Ehret',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'dam' => '',
			'dam_catedit' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:30:{s:9:"ChangeLog";s:4:"e90d";s:10:"README.txt";s:4:"c1df";s:21:"ext_conf_template.txt";s:4:"d0d1";s:12:"ext_icon.gif";s:4:"f918";s:17:"ext_localconf.php";s:4:"2f6a";s:14:"ext_tables.php";s:4:"6f2a";s:14:"ext_tables.sql";s:4:"c757";s:19:"flexform_ds_pi1.xml";s:4:"e3a1";s:13:"locallang.xml";s:4:"619b";s:16:"locallang_db.xml";s:4:"03e9";s:14:"doc/manual.sxw";s:4:"7a67";s:15:"doc/realurl.txt";s:4:"3511";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:30:"pi1/class.tx_cegallery_pi1.php";s:4:"f1f2";s:38:"pi1/class.tx_cegallery_pi1_wizicon.php";s:4:"ce05";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"6561";s:24:"pi1/static/constants.txt";s:4:"d41d";s:24:"pi1/static/editorcfg.txt";s:4:"b9b0";s:20:"pi1/static/setup.txt";s:4:"1bbb";s:18:"js/client_sniff.js";s:4:"1054";s:12:"js/moo.fx.js";s:4:"ec2e";s:17:"js/moo.fx.pack.js";s:4:"ae77";s:20:"js/prototype.lite.js";s:4:"503c";s:24:"js/showcase.slideshow.js";s:4:"9b73";s:15:"js/slideshow.js";s:4:"734c";s:21:"js/timed.slideshow.js";s:4:"5c27";s:12:"res/left.gif";s:4:"f6e3";s:13:"res/right.gif";s:4:"e1d5";s:16:"static/setup.txt";s:4:"2040";}',
	'suggests' => array(
	),
);

?>