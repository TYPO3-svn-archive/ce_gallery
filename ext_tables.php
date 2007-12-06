<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');

// Hide the formfields for layout and select_key
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

// Makes the field tx_cegallery_dam_category visible
//$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_cegallery_dam_category;;;;1-1-1";
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

// Add an entry to the selectorbox in the BE-Form
t3lib_extMgm::addPlugin(Array('LLL:EXT:ce_gallery/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

// Add an entry in the static template list found in sys_templates "static template files"
t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/', 'Photogallery(CSS)');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Photogallery(Slimbox)');

//Register FlexForm:
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1','FILE:EXT:ce_gallery/flexform_ds_pi1.xml');
?>