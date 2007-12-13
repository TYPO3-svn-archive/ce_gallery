<?php
/**
 * Copyright notice
 *
 * (c) 2006 Christian Ehret <chris@ehret.name>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Plugin 'Photogallery' for the 'ce_gallery' extension.
 *
 * @since 2006-07-26
 * @author Christian Ehret <chris@ehret.name>
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');

class tx_cegallery_pi1 extends tslib_pibase {
	var $prefixId = 'tx_cegallery_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_cegallery_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'ce_gallery'; // The extension key.
	var $pi_checkCHash = true;
	var $pi_USER_INT_obj = 0;
	var $slimbox = false;
	var $smoothslideshow = false;

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content : The PlugIn content
	 * @param array $conf : The PlugIn configuration
	 * @return The content that is displayed on the website
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function main($content, $conf)	{
		 // Getting configuration:
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();

		 // Loading localization data:
		$this->pi_loadLL();

		 // Flexform stuff needed for type slideshow
		if (!$this->cObj->data['pi_flexform']) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pi_flexform',
				'tt_content',
				'pid = ' . $GLOBALS['TSFE']->id . ' AND CType="list" AND list_type="ce_gallery_pi1"'
			);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$this->cObj->data['pi_flexform'] = $row['pi_flexform'];
		}

		$this->pi_initPIFlexForm();

		$this->slimbox = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'slimbox', 'general');
		if ($this->slimbox && !t3lib_extMgm::isLoaded('pmkslimbox'))
			return $this->pi_wrapInBaseClass('EXTENSION:pmslimbox is not loaded!');

		$thepage = $this->piVars['page'];

		// the page to display
		if (isset($thepage) && is_numeric($thepage)) {
				$page = (int)$thepage;
		} else {
				$page = 1;
		}

		$theapage = $this->piVars['apage'];;
		// the page to display
		if (isset($theapage) && is_numeric($theapage)) {
				$apage = (int)$theapage;
		} else {
				$apage = 1;
		}

		$content = "";
		$album = $this->piVars['album'];
		$detail = $this->piVars['detail'];
		$slideshow = $this->piVars['slideshow'];

		if (isset($detail)) {
			$content .= $this->getDetail($album, $detail);
		} elseif (is_numeric($slideshow)) {
			if ($this->slimbox) {
				$content .= '
					<script type="text/javascript" src="/' . $GLOBALS['TSFE']->tmpl->getFileName('EXT:ce_gallery/js/prototype.lite.js') . '"></script>
					<script type="text/javascript" src="/' . $GLOBALS['TSFE']->tmpl->getFileName('EXT:ce_gallery/js/moo.fx.js') . '"></script>
					<script type="text/javascript" src="/' . $GLOBALS['TSFE']->tmpl->getFileName('EXT:ce_gallery/js/moo.fx.pack.js') . '"></script>
					<script type="text/javascript" src="/' . $GLOBALS['TSFE']->tmpl->getFileName('EXT:ce_gallery/js/timed.slideshow.js') . '"></script>';
				$content .= $this->getSmoothDetail($slideshow, $detail, true);
			} else {
				$jsFile1 = $GLOBALS['TSFE']->tmpl->getFileName('EXT:ce_gallery/js/client_sniff.js');
				$jsFile2 = $GLOBALS['TSFE']->tmpl->getFileName('EXT:ce_gallery/js/slideshow.js');
				$jsCode = '<script type="text/javascript">
					var js_play = \'' . $this->pi_getLL('js_play') . '\';
					var js_status_stop = \'' . $this->pi_getLL('js_status_stop') . '\';
					var js_stop = \'' . $this->pi_getLL('js_stop') . '\';
					var js_status_playing = \'' . $this->pi_getLL('js_status_playing') . '\';
					var js_forwards = \'' . $this->pi_getLL('js_forwards') . '\';
					var js_backwards = \'' . $this->pi_getLL('js_backwards') . '\';
					var js_status_loading = \'' . $this->pi_getLL('js_status_loading') . '\';
					var js_of = \'' . $this->pi_getLL('js_of') . '\';
					var js_status_wait = \'' . $this->pi_getLL('js_status_wait') . '\';
				</script>';
				$jsCode .= '<script type="text/javascript" src="/' . $jsFile1 . '"></script>
						<script type="text/javascript" src="/' . $jsFile2 . '"></script>';
				$content .= $jsCode;
				$content .= $this->getSlideshow($slideshow);
			}
		} elseif (is_numeric($album)) {
			$content .= $this->getAlbumContents($album, $apage, $page);
		} else {
         	//get the first category id and set album = category id
            $album = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'category', 'categoryView');
            //if there is only one category, already open the album and display its content
            if (($this->getNumCat() == 1) && is_numeric($album)) {
            	$content .= $this->getAlbumContents($album, $apage, $page);
            //if there are more than one categories or if the album is not set correctly display the list of albums
            } else {
            	$content .= $this->getAlbumList($page);
            }
		}
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * This function generates list of albums with paging.
	 *
	 * @param integer $page page number
	 * @return string HTML code for album list
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getAlbumList($page)	{
		$pagebrowser = $this->pageBrowser(
			$this->getNumPages($this->getNumCat()),
			$page,
			$this->prefixId.'[page]'
		);
		$list = $pagebrowser.$this->getAlbum($page).$pagebrowser;
		return $list;
	}

	/**
	 * This function shows photos of a albums with paging.
	 *
	 * @param integer $album album id
	 * @param integer $start start
	 * @param integer $page page of listview
	 * @return string HTML code for album list
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getAlbumContents($album, $start, $page)	{
		$pagebrowser = $this->pageBrowser(
		$this->getNumPages($this->getNumItems($album)),
			$start,
			$this->prefixId.'[apage]',
			array($this->prefixId.'[album]' => $album)
		);
		$content = $pagebrowser.$this->getContent($album, $start, $page).$pagebrowser;
		return $content;
	}

	/**
	 * This function returns all albums.
	 *
	 * @param number $start start
	 * @return array albums
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getAlbum($start = -1) {
		$displayrows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbnumber', 'thumbnails');
		if ($start <= 1 || !is_numeric($start)) {
				$start = 0;
		} else {
				$start = ($start-1) * $displayrows;
		}

		$where = '';
		$categories = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'category', 'categoryView');

		  /* @TODO We need an error handler here */
		if ($categories) {
			$where .= 'tx_dam_cat.uid IN (' . $categories. ')';
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive', 'categoryView'))
				$where .= ' or tx_dam_cat.parent_id IN ('.$categories.')';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.title, tx_dam_cat.uid AS uid',
			'tx_dam_cat',
			$where .  $this->cObj->enableFields('tx_dam_cat'),
			'',
			'sorting',
			"$start,  $displayrows"
		);

		$albums = "";
		$i = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			unset($lastitem);
			$crdate = "DATE_FORMAT(tx_dam.crdate, '" . $this->pi_getLL('date_format') . "')";
			// random album image
			$orderBy = 'RAND()';
			if (!$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'randomAlbumImage', 'general')) {
				$orderBy = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 'thumbnails'); // ORDER BY ...
			}

			$res_lastitem = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tx_dam.uid, tx_dam.title, tx_dam.file_path, tx_dam.file_name, tx_dam.alt_text, tx_dam.crdate',
				'tx_dam_mm_cat damcat LEFT JOIN tx_dam ON damcat.uid_local = tx_dam.uid',
				'damcat.uid_foreign = ' . $row['uid'] . ' AND tx_dam.file_mime_type = \'image\' ' . $this->cObj->enableFields('tx_dam') , // WHERE ...
				'', // GROUP BY ...
				$orderBy,
				'0 , 1' // LIMIT
			);

			$lastitem = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_lastitem);
			$imagePath = $lastitem['file_path'] . $lastitem['file_name'];
			$altTag = $lastitem['alt_text'] ? $lastitem['alt_text'] : $lastitem['title'];
			$uid = $lastitem['uid'];
			if ($lastitem['uid']) {
				$albums .= '<div' . $this->pi_classParam('album_entry') . '>';

				$thumbstr = '<br/>' . $this->pi_linkTP($row['title'], array($this->prefixId .'[album]' => $row['uid'], $this->prefixId .'[apage]' => 1), 1) . '<br/>';
				$thumbstr .= '<span' . $this->pi_classParam('album_date') . '>' . $this->pi_getLL('last_entry') . date($this->pi_getLL('date_format'), $lastitem['crdate']) . '</span>';

				$albums .= $this->buildLinkToThumb($imagePath, $uid, $altTag, '&'.$this->prefixId.'[album]='.$row['uid'].'&'.$this->prefixId.'[apage]=1', $thumbstr);
				$albums .= '</div>';
				$i++;
			}
			if ($i % $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbrownumber', 'thumbnails') == 0) {
					$albums .= '<div' . $this->pi_classParam('clearer') . '></div>';
			}
		}
		$albums .= '<div' . $this->pi_classParam('clearer') . '></div>';
		return $albums;
		}

	/**
	 * Get contents of a album
	 *
	 * @param integer $album album id
	 * @param integer $start start for album
	 * @param integer $page start for overview
	 * @param integer $num number of items
	 * @return string HTML Code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getContent($album, $start = -1, $page = -1) {
		global $EM_CONF;
		$displayrows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbnumber', 'thumbnails');

		if ($start <= 1 || !is_numeric($start)) {
				$start = 0;
		} else {
				$start = ($start-1) * $displayrows;
		}
		if ($page <= 1 || !is_numeric($page)) {
				$page = 0;
		}

		$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title, description',
			'tx_dam_cat', // FROM ...
			'uid = ' . $album . ' ' . $this->cObj->enableFields('tx_dam_cat')
		);

		$thealbum = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1);

		$items = '<div' . $this->pi_classParam('album_header') . '>' .
			'<h2' . $this->pi_classParam('album_header') . '>' . $thealbum['title'] . '</h2>' .
			'<p' . $this->pi_classParam('album_header') . '>' . $thealbum['description'] . '</p>' .
			'</div>' .
			'<div' . $this->pi_classParam('album_backlink') . '>';

		if ($this->slimbox && t3lib_extMgm::isLoaded('pmkslimbox')) {
			$detailWidth = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'detail');
			$detailHeight = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'detail');
			if (t3lib_div::int_from_ver($GLOBALS['EM_CONF']['pmkslimbox']['version']) < 1001000) {
					$detailWidth += 50;
					$detailHeight += 50;
			}

			$conf = array(
				'useCacheHash' => 1,
				'parameter' => $GLOBALS['TSFE']->id,
				'additionalParams' => '&'.$this->prefixId.'[slideshow]=' . $album . '&type=' . $this->conf['slideshowTypeNum'],
				'ATagParams' => 'rel="lightbox" rev="width=' . $detailWidth . ',height=' . $detailHeight . '"',
				'title' => $this->pi_getLL('slideshow')
			);
			$items .= $this->cObj->typoLink('&raquo; ' . $this->pi_getLL('slideshow'), $conf);
		} else {
			$items .= $this->pi_linkTP('&raquo; ' . $this->pi_getLL('slideshow'),
				array($this->prefixId . '[slideshow]' => $album), 1);
		}

		if ($this->getNumCat() > 1) {
			$items .= '&nbsp;&nbsp;&nbsp;';
			$items .= $this->pi_linkToPage($this->pi_getLL('back_to_overview'), $GLOBALS['TSFE']->id, '', '');
		}

		$items .= '</div>';

		$limit = '';
		if (!$this->slimbox || !t3lib_extMgm::isLoaded('pmkslimbox')) {
			$limit = $start . ', ' . $displayrows;
		}
		$crdate = "DATE_FORMAT(tx_dam.crdate, '" . $this->pi_getLL('date_format') . "')";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam.uid, tx_dam.title, tx_dam.file_path, tx_dam.file_name, tx_dam.alt_text,' .
				'tx_dam.crdate, tx_dam.description',
			'tx_dam_mm_cat damcat LEFT JOIN tx_dam ON damcat.uid_local = tx_dam.uid',
			'damcat.uid_foreign = ' . $album . ' AND tx_dam.file_mime_type = \'image\' ' . $this->cObj->enableFields('tx_dam'),
			'',
			$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 'thumbnails'),
			$limit
		);

		$i = 0;
		$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			++$i;
			$imagePath = $row['file_path'] . $row['file_name'];

			$altTag = $row['alt_text'] ? $row['alt_text'] : $row['title'];
			$title = $row['description'] ? $row['description'] : $row['title'];

			$pmkSlimbox = false;
			if ($this->slimbox && t3lib_extMgm::isLoaded('pmkslimbox')) {
				$pmkSlimbox = true;
				$conf = array();
				$conf['parameter'] = $this->buildImage($imagePath, $row['uid'], 'detail');
				$conf['ATagParams'] = 'rel="lightbox[sb' . $GLOBALS['TSFE']->id . '_links]"';
				$conf['title'] = $title;
				if ($i > $start && $i <= ($start + $displayrows)) {
					$itemstr = '<br /><span ' . $this->pi_classParam('imagetitle') . $this->cObj->typoLink($row['title'], $conf) . '</span>';
				} else {
					if ($i == $start + $displayrows + 1 || ($start != 0 && $i == 1)) {
						$itemstr = '<div ' . $this->pi_classParam('slimbox_hidden_links') . '>';
					} else {
						$itemstr = '';
					}

					$conf['ATagParams'] = 'rel="lightbox[sb' . $GLOBALS['TSFE']->id . ']"';
					$itemstr .= $this->cObj->typoLink('&nbsp;', $conf);
					if ($i == $num || ($start != 0 && $i == $start)) {
						$itemstr .= '</div>';
					}
				}
			} else {
				$itemstr = '<br/>' . $this->pi_linkTP($row['title'],
					array(
						$this->prefixId.'[detail]' => $row['uid'],
						$this->prefixId.'[album]' => $album
					),
					true
				);
			}
			if (!$pmkSlimbox || ($i > $start && $i <= ($start + $displayrows))) {
				$conf = array();
				$conf['parameter'] = $imagePath;
				if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fullscreen', 'thumbnails')) {
					$itemstr .= $this->cObj->typoLink(' (' . $this->pi_getLL('fullscreen') . ')', $conf) . '<br />';
				}
				$itemstr2 = $this->buildLinkToThumb($imagePath,
					$row['uid'],
					$altTag,
					'&' . $this->prefixId . '[detail]=' . $row['uid'] . '&' . $this->prefixId .  '[album]=' . $album,
					$itemstr,
					$title,
					$pmkSlimbox
				);
				$items .= '<div' . $this->pi_classParam('album_entry') . '>' . $itemstr2 . '</div>';
				if ($i % $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbrownumber', 'thumbnails') == 0) {
					$items .= '<div' . $this->pi_classParam('clearer') . '></div>';
				}
			} else {
				$items .= $itemstr;
			}
		}

		$items .= '<div' . $this->pi_classParam('clearer') . '></div>';
		return $items;
	}

	/**
	 * Get contents of a album for slideshow
	 *
	 * @param integer $album album id
	 * @return string HTML Code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getSlideshow($album)	{
		$detailQuality = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'quality', 'detail');
		$detailWidth = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'detail');
		$detailHeight = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'detail');

		$this->pi_loadLL(); // Loading the LOCAL_LANG values
		$photo = '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_dam.uid, tx_dam.title, tx_dam.file_path, tx_dam.file_name, tx_dam.alt_text, tx_dam.crdate', // SELECT ...
			'tx_dam_mm_cat damcat LEFT JOIN tx_dam ON damcat.uid_local = tx_dam.uid', // FROM ...
			'damcat.uid_foreign = ' . $album . ' AND tx_dam.file_mime_type = \'image\' ' . $this->cObj->enableFields('tx_dam') , // WHERE ...
			'', // GROUP BY ...
			$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 'thumbnails'), // ORDER BY ...
			''// LIMIT
		);
		$i = 0;
		$captions = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$i++;
			$imagePath = $row['file_path'] . $row['file_name'];
			$imgPathInfo = pathinfo($imagePath);
			$detailName = substr($imgPathInfo['basename'], 0 , strlen($imgPathInfo['basename']) - strlen($imgPathInfo['extension']) - 1);
			$detailPath = 'typo3temp/ce_gallery/d_' . $row['uid'] . '_' . $detailWidth . '_' . $detailHeight . '_' . $detailQuality . '.' . $imgPathInfo['extension'];

			if (!file_exists($detailPath)) {
				$resize_arr = $this->resizeImage($detailWidth, $detailHeight, $detailQuality, $imagePath, $detailPath);
			}

			$altTag = $row['alt_text'] ? $row['alt_text'] : $row['title'];
			$photo .= '<a id="photo_urls_' . $i . '" href="' . $detailPath . '"></a>';
			$captions[$i] = $row['title'];
		}

		$photo .= '<div' . $this->pi_classParam('detail_entry') . '>';

		$photo .= '<div' . $this->pi_classParam('slideControls') . '>
			<form name="TopForm">
			<a href="#" onClick="stopOrStart(); return false;">[<span id="stopOrStartText">' . $this->pi_getLL('js_play') . '</span>]</a>&nbsp;<a href="#" onClick="changeDirection(); return false;">[<span id="changeDirText">' . $this->pi_getLL('backwards') . '</span>]</a>&nbsp;
			&nbsp;' . $this->pi_getLL('repeat') . ':&nbsp;<input type="checkbox" name="loopCheck"  onclick="toggleLoop();">
			<br/>' . $this->pi_getLL('delay') . ':&nbsp;<select name="time" size="1"  onchange="reset_timer()" style="font-size:10px;">
			<option value="1">1 ' . $this->pi_getLL('second') . '</option>
			<option value="2">2 ' . $this->pi_getLL('seconds') . '</option>
			<option value="3" selected="selected">3 ' . $this->pi_getLL('seconds') . '</option>
			<option value="4">4 ' . $this->pi_getLL('seconds') . '</option>
			<option value="5">5 ' . $this->pi_getLL('seconds') . '</option>
			<option value="10">10 ' . $this->pi_getLL('seconds') . '</option>
			<option value="15">15 ' . $this->pi_getLL('seconds') . '</option>
			<option value="30">30 ' . $this->pi_getLL('seconds') . '</option>
			<option value="45">45 ' . $this->pi_getLL('seconds') . '</option>
			<option value="60">60 ' . $this->pi_getLL('seconds') . '</option>
			</select>
		';
		$photo .= '
			<script language="Javascript" type="text/javascript">
			/* show the blend select if appropriate */
			if (browserCanBlend) {
				document.write(\'&nbsp;' . $this->pi_getLL('fade') . ':<select name="transitionType" size="1"  onchange="change_transition()" style="font-size:10px;"><option value="0" selected="selected">' . $this->pi_getLL('blend') . '</option><option value="1" >' . $this->pi_getLL('blinds') . '</option><option value="2" >' . $this->pi_getLL('checkerboard') . '</option><option value="3" >' . $this->pi_getLL('diagonal') . '</option><option value="4" >' . $this->pi_getLL('doors') . '</option><option value="5" >' . $this->pi_getLL('gradient') . '</option><option value="6" >' . $this->pi_getLL('iris') . '</option><option value="7" >' . $this->pi_getLL('pinwheel') . '</option><option value="8" >' . $this->pi_getLL('pixelate') . '</option><option value="9" >' . $this->pi_getLL('radial') . '</option><option value="10" >' . $this->pi_getLL('rain') . '</option><option value="11" >' . $this->pi_getLL('slide') . '</option><option value="12" >' . $this->pi_getLL('snow') . '</option><option value="13" >' . $this->pi_getLL('spiral') . '</option><option value="14" >' . $this->pi_getLL('stretch') . '</option><option value="15" >' . $this->pi_getLL('random') . '</option></select> \');
			}
			</script>
			</form>
			</div>
			<script language="JavaScript" type="text/javascript">
				firstPhotoURL = document.getElementById("photo_urls_" + 1).href;
				document.write(\'<img src="\');
				document.write(firstPhotoURL);
				document.write(\'" name="slide">\');
			</script>
			<script language="Javascript" type="text/javascript">
				/* show the caption */
				document.write(\'<div class="modcaption" id="caption"></div>\');
				</script>
				<script language="JavaScript" type="text/javascript">
				var timer;
				var current_location = 1;
				var next_location = 1;
				var pics_loaded = 0;
				var onoff = 0;
				var fullsized = 0;
				var direction = 1;
				var timeout_value;
				var images = new Array;
				var photo_urls = new Array;
				var full_photo_urls = new Array;
				var photo_captions = new Array;
				var transitionNames = new Array;
				var transitions = new Array;
				var current_transition = 0;
				var loop = 0;
		';
		for ($i = 1; $i < sizeof($captions) + 1; $i++) {
			$photo .= 'photo_captions[' . $i . '] = "' . $captions[$i] . '";';
		}

		$photo .= 'transitions[0] = "progid:DXImageTransform.Microsoft.Fade(duration=1)";
			transitions[1] = "progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)";
			transitions[2] = "progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)";
			transitions[3] = "progid:DXImageTransform.Microsoft.Strips(Duration=1,motion=rightdown)";
			transitions[4] = "progid:DXImageTransform.Microsoft.Barn(Duration=1,orientation=vertical)";
			transitions[5] = "progid:DXImageTransform.Microsoft.GradientWipe(duration=1)";
			transitions[6] = "progid:DXImageTransform.Microsoft.Iris(Duration=1,motion=out)";
			transitions[7] = "progid:DXImageTransform.Microsoft.Wheel(Duration=1,spokes=12)";
			transitions[8] = "progid:DXImageTransform.Microsoft.Pixelate(maxSquare=10,duration=1)";
			transitions[9] = "progid:DXImageTransform.Microsoft.RadialWipe(Duration=1,wipeStyle=clock)";
			transitions[10] = "progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)";
			transitions[11] = "progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)";
			transitions[12] = "progid:DXImageTransform.Microsoft.RandomDissolve(Duration=1,orientation=vertical)";
			transitions[13] = "progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)";
			transitions[14] = "progid:DXImageTransform.Microsoft.Stretch(Duration=1,stretchStyle=push)";
			transitions[15] = "special case";
			var transition_count = 15;
			var photo_count = ' . count($captions);
		$photo .= '/* Load the first picture */
			setCaption(photo_captions[1]);
			preload_photo(1);
			/* Start the show. */
			/*play();*/
		';
		$photo .= '</script>';
		$photo .= $this->pi_linkToPage($this->pi_getLL('back'), $GLOBALS['TSFE']->id, '', array('album' => $album));
		$photo .= '</div>';

		return $photo;
	}

	/**
	 * Get details of a photo
	 *
	 * @param integer $album album id
	 * @param integer $detail photo id
	 * @return string HTML Code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getDetail($album, $detail)	{
		$photo = '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam.uid, tx_dam.title, tx_dam.file_path, tx_dam.file_name, tx_dam.date_cr,' .
				'tx_dam.date_mod, tx_dam.alt_text, tx_dam.crdate, tx_dam.description, tx_dam.tstamp',
			'tx_dam',
			'tx_dam.uid = ' . $detail . ' AND tx_dam.file_mime_type = "image" ' . $this->cObj->enableFields('tx_dam')
		);

		$thephoto = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$imagePath = $thephoto['file_path'] . $thephoto['file_name'];

		$sortingToogle = array (
			'DESC' => array('>', '<', 'ASC', ),
			'ASC' => array('<', '>', 'DESC'));

		$sorting = split(' ',
			$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 'thumbnails'));
		list(, $sorting[3]) = split('\.', $sorting[0]);

		$criterion = (is_numeric($thephoto[$sorting[3]]) ?
			$thephoto[$sorting[3]]  : '\'' . $thephoto[$sorting[3]] . '\'');

		$sql = array (
			'SELECT' => 'tx_dam.uid',
			'FROM' => 'tx_dam_mm_cat damcat LEFT JOIN tx_dam ON damcat.uid_local = tx_dam.uid',
			'WHERE' => 'damcat.uid_foreign = ' . $album .
				' AND tx_dam.file_mime_type = "image" ' .
				' AND tx_dam.uid!=' . $detail .
				$this->cObj->enableFields('tx_dam'),
			'LIMIT' => '0,1');


		$resPrev = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$sql['SELECT'],
			$sql['FROM'],
			$sql['WHERE'] . ' AND (' . $sorting[0] . $sortingToogle[$sorting[1]][0] . $criterion .
				' OR (tx_dam.uid' .$sortingToogle[$sorting[1]][0] . $thephoto['uid'] .
				' AND ' . $sorting[0] . '=' . $criterion .  '))',
			'',
			$sorting[0] . ' ' . $sortingToogle[$sorting[1]][2] .
				', tx_dam.uid  ' . $sortingToogle[$sorting[1]][2],
			$sql['LIMIT']);

		$resNext = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$sql['SELECT'],
			$sql['FROM'],
			$sql['WHERE'] . ' AND (' . $sorting[0] . $sortingToogle[$sorting[1]][1] . $criterion .
				' OR (tx_dam.uid' .$sortingToogle[$sorting[1]][1] . $thephoto['uid'] .
				' AND ' . $sorting[0] . '=' . $criterion .  '))',
			'',
			$sorting[0] . ' ' . $sorting[1] . ', tx_dam.uid '  . $sorting[1],
			$sql['LIMIT']);

		$prevphoto = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resPrev);
		$nextphoto = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resNext);
/*
		t3lib_div::debug($resPrev);
		t3lib_div::debug($resNext);
		t3lib_div::debug($criterion);
		t3lib_div::debug($prevphoto);
		t3lib_div::debug($nextphoto);
*/

		$photo .= '<div' . $this->pi_classParam('detail_header') . '>';
		$photo .= '<h2' . $this->pi_classParam('detail_header') . '>' . $thephoto['title'] . '</h2>';
		$conf = array();
		$conf['parameter'] = $imagePath;
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fullscreen', 'thumbnails')) {
				$photo .= '<span' . $this->pi_classParam('detail_fulllink') . '>' . $this->cObj->typoLink(' (' . $this->pi_getLL('fullscreen') . ')', $conf) . '</span>';
		}
		$photo .= '</div>';
		$photo .= '<div' . $this->pi_classParam('detail_nav') . '>';
		$photo .= '<span' . $this->pi_classParam('photo_prev') . '>';
		if (isset($prevphoto['uid'])) {
			$photo .= $this->pi_linkTP($this->pi_getLL('prev'),
				array(
					$this->prefixId . '[album]' => $album,
					$this->prefixId . '[detail]' => $prevphoto['uid']
				),
				true
			);
		}
		$photo .= '&nbsp;</span>';
		$photo .= '<span' . $this->pi_classParam('album_back_link') . '>';
		$photo .= $this->albumBackLink($album);
		$photo .= '</span>';
		$photo .= '<span' . $this->pi_classParam('photo_next') . '>';
		if (isset($nextphoto['uid'])) {
			$photo .= $this->pi_linkTP($this->pi_getLL('next'),
				array(
					$this->prefixId . '[album]' => $album,
					$this->prefixId . '[detail]' => $nextphoto['uid']
				),
				true
			);
		}
		$photo .= '&nbsp;</span>';
		$photo .= '</div>';
		$photo .= '<div' . $this->pi_classParam('detail_entry') . '>';

		$imagePath = $thephoto['file_path'] . $thephoto['file_name'];

		$altTag = $thephoto['alt_text'] ? $thephoto['alt_text'] : $thephoto['title'];

		$photo .= $this->buildDetail($imagePath, $thephoto['uid'], $altTag);
		$photo .= '</div>';
		$photo .= '<p' . $this->pi_classParam('album_header') . '>' . $thephoto['description'] . '</p>';

		return $photo;
	}

	/**
	 * Get details of a photo for smooth slideshow
	 *
	 * @param integer $album album id
	 * @param integer $detail photo id
	 * @param boolean $slideshow display slideshow
	 * @return string HTML Code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getSmoothDetail($album, $detail, $slideshow = false)	{
		$this->pi_loadLL(); // Loading the LOCAL_LANG values
		$detailWidth = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'detail');
		$detailHeight = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'detail');

		$photo .= '<div class="timedSlideshow" id="mySlideshow" style="width: ' . $detailWidth . 'px; height: ' . $detailHeight . 'px;"></div>';

		$res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_dam.uid, tx_dam.title, tx_dam.file_path, tx_dam.file_name, tx_dam.alt_text, tx_dam.crdate, tx_dam.description', // SELECT ...
			'tx_dam_mm_cat damcat LEFT JOIN tx_dam ON damcat.uid_local = tx_dam.uid', // FROM ...
			'damcat.uid_foreign = ' . $album . ' AND tx_dam.file_mime_type = \'image\' ' . $this->cObj->enableFields('tx_dam') , // WHERE ...
			'', // GROUP BY ...
			$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 'thumbnails'), // ORDER BY ...
			"" // LIMIT
		);
		$photo .= '<script type="text/javascript">';
		$photo .= 'var mySlideData = new Array(); countArticle = 0;';

		$i = 0;
		$start = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1)) {
			if ($row['uid'] == $detail)
				$start = $i;

			$i++;
			$imagePath = $row['file_path'] . $row['file_name'];
			$altTag = $row['alt_text'] ? $row['alt_text'] : $row['title'];
			$tmp = $this->buildDetail($imagePath, $row['uid'], $altTag, false);

			if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] == 1) {
				$stdGraphic = t3lib_div::makeInstance("t3lib_stdGraphic");
				$info = $stdGraphic->getImageDimensions($tmp);
				$width = $info[0];
				$height = $info[1];
			} else {
				 // Get new dimensions
				list($width, $height) = getimagesize($tmp);
			}
			$left = ($detailWidth - $width) / 2;
			$top = ($detailHeight - $height) / 2;
			$photo .= 'mySlideData[countArticle++] = new Array(
				\'/' . $tmp . '\',
				\'' . $left . 'px\',
				\'' . $top . 'px\',
				\'' . str_replace("\n", ' - ', str_replace("\r", ' - ', addslashes($row['title']))) . '\',
				\'' . str_replace("\n", ' - ', str_replace("\r", ' - ', addslashes($row['description']))) . '\'
				);
			';
		}

		$photo .= 'function addLoadEvent(func) {
			var oldonload = window.onload;
			if (typeof window.onload != \'function\') {
			window.onload = func;
			} else {
			window.onload = function() {
			oldonload();
			func();
			} } }

			function startSlideshow() {
			currentIter = ' . $start . ';
			slideShowDelay = ' . $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'smoothslideshowdelay', 'general') . ';
			initSlideShow($(\'mySlideshow\'), mySlideData);
			}
			addLoadEvent(startSlideshow);';

		$photo .= ' </script>';

		return $photo;
	}

	/**
	 * Get number of Categories to show
	 *
	 * @return integer numer of categories
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getNumCat()	{
		$categories = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'category', 'categoryView');
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive', 'categoryView')) {
			$whereAdd = ' AND (tx_dam_cat.uid IN (' . $categories. ')' .
				' or tx_dam_cat.parent_id IN ('.$categories.'))';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tx_dam_cat.uid',
				'tx_dam_cat, tx_dam_mm_cat',
				'tx_dam_mm_cat.uid_foreign=tx_dam_cat.uid' . $whereAdd .  $this->cObj->enableFields('tx_dam_cat'),
				'tx_dam_cat.uid');
			$ret = $GLOBALS['TYPO3_DB']->sql_num_rows ($res);
		} else {
			$ret = count(split(',', $categories));
		}
		return $ret;
	}

	/**
	 * Get number of items
	 *
	 * @param integer $album album uid
	 * @return integer number of items
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getNumItems($album)	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(tx_dam.uid) as count', // SELECT ...
			'tx_dam_mm_cat damcat LEFT JOIN tx_dam ON damcat.uid_local = tx_dam.uid', // FROM ...
			'damcat.uid_foreign = ' . $album . ' AND tx_dam.file_mime_type = \'image\' ' . $this->cObj->enableFields('tx_dam') , // WHERE ...
			'', // GROUP BY ...
			'', // ORDER BY ...
			'' // LIMIT
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row['count'];
	}

	/**
	 * Get number of pages
	 *
	 * @param integer $numberOfItems number of items
	 * @return integer number of pages
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function getNumPages($numberOfItems) {
		$this->pi_setPiVarDefaults();
		$displayrows = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbnumber', 'thumbnails');
		$pages = floor ($numberOfItems / $displayrows);
		if ($numberOfItems % $displayrows <> 0) {
				$pages++;
		}
		return $pages;
	}

	/**
	 * Back to album link
	 *
	 * @param integer $album album uid
	 * @return string backlink html code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function albumBackLink($album) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_dam_cat.title', // SELECT ...
			'tx_dam_cat', // FROM
			'tx_dam_cat.uid = ' . $album, // WHERE
			'', // GROUP BY
			'', // ORDER BY
			'0,1' // LIMIT
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$backlink = '';
		$backlink .= $this->pi_getLL('back_to');
		$backlink .= $this->pi_linkTP($row['title'],
			array($this->prefixId . '[album]' => $album),
			true
		);
		return $backlink;
	}

	/**
	 * Build page browser
	 *
	 * @param integer $numPages number of pages
	 * @param integer $thispage actual page
	 * @param string $pagevar page url variable
	 * @param array $addvar additional url variables
	 * @return string pagebrowser html code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 * @author Stephan Bauer <stephan_bauer@gmx.de>
	 */
	function pageBrowser($numPages, $thispage, $pagevar, $addvar = array()) {
		$pagebrowser = '';
		$pagebrowser .= '<div ' . $this->pi_classParam('pagebrowser') . '>';
		if ($numPages > 1) {
			$pagebrowser .= '<div' . $this->pi_classParam('page') . '>' . $this->pi_getLL('page') . '</div>';
			if ($thispage > 1) {
				$pagebrowser .= '<div' . $this->pi_classParam('pagebrowser_back') . '><span ' . $this->pi_classParam('pagebrowser_normal') . '>';
				$vararr = $addvar;
				array_push($vararr, array($pagevar => 1));
				$pagebrowser .= $this->pi_linkTP('&lt;&lt;', $vararr, 1);
				$pagebrowser .= '</span>';
				$pagebrowser .= '<span ' . $this->pi_classParam('pagebrowser_normal') . '>';
				$vararr = $addvar;
				array_push($vararr, array($pagevar => $thispage - 1));
				$pagebrowser .= $this->pi_linkTP('&lt;', $vararr, 1);
				$pagebrowser .= '</span></div>';
			} else {
				// $pagebrowser .= '<div' . $this->pi_classParam('pagebrowser_back') . '>'.$this->pi_getLL('page').'</div>';
			}
			$pagebrowser .= '<div' . $this->pi_classParam('pagebrowser_pages') . '>';
			for ($i = 1;
				$i <= $numPages;
				$i++) {
				if ($i == $thispage) {
						$pagebrowser .= '<span ' . $this->pi_classParam('pagebrowser_actual') . '>';
				} else {
						$pagebrowser .= '<span ' . $this->pi_classParam('pagebrowser_normal') . '>';
				}
				$vararr = $addvar;
				array_push($vararr, array($pagevar => $i));
				$pagebrowser .= $this->pi_linkTP($i, $vararr, 1);
				$pagebrowser .= '</span>';
			}
			$pagebrowser .= '</div>';
			if ($thispage < $numPages) {
				$pagebrowser .= '<div' . $this->pi_classParam('pagebrowser_next') . '><span ' . $this->pi_classParam('pagebrowser_normal') . '>';
				$vararr = $addvar;
				array_push($vararr, array($pagevar => $thispage + 1));
				$pagebrowser .= $this->pi_linkTP('&gt;', $vararr, 1);
				$pagebrowser .= '</span>';
				$pagebrowser .= '<span ' . $this->pi_classParam('pagebrowser_normal') . '>';
				$vararr = $addvar;
				array_push($vararr, array($pagevar => $numPages));
				$pagebrowser .= $this->pi_linkTP('&gt;&gt;', $vararr, 1);
				$pagebrowser .= '</span></div>';
			} else {
				$pagebrowser .= '<div' . $this->pi_classParam('pagebrowser_next') . '></div>';
			}
		}
		$pagebrowser .= '</div>';
		return $pagebrowser;
	}


	/**
	 * Generates the html-code for a thumbnail
	 *
	 * @param string $path Path to the ImageFile
	 * @param integer $uid DAM-UID of the Image. Used for linking.
	 * @param string $altTag String to use as AltTag
	 * @param string $linkstr additional link string
	 * @param string $text addition text
	 * @param string $title title
	 * @param boolean $pmkSlimbox set to true if the link should start a slimbox (lightbox alternative)
	 * @return string HTML-Code
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 * @subauthor Stefan Galinski <stefan.galinski@frm2.tum.de>
	 */
	function buildLinkToThumb($path, $uid, $altTag, $linkstr = '', $text = '', $title = '', $pmkSlimbox = false)	{

		$conf = array();
		$conf['altText'] = $altTag;
		$conf['titleText'] = (!empty($title)) ? $title : $altTag;
		if ($pmkSlimbox) {
			$conf['stdWrap.']['typolink.']['parameter'] = $this->buildImage($path, $uid, 'detail');
			$conf['stdWrap.']['typolink.']['ATagParams'] = 'rel="lightbox[sb' . $GLOBALS['TSFE']->id . ']"';
		} else {
			$conf['stdWrap.']['typolink.']['parameter'] = $GLOBALS['TSFE']->id;
			$conf['stdWrap.']['typolink.']['additionalParams'] = $linkstr;
		}
		$conf['stdWrap.']['typolink.']['title'] = $title;
		$conf['stdWrap.']['typolink.']['useCacheHash'] = 1;

		$options = array('IMAGE' => $conf);
		$image = $this->buildImage($path, $uid, 'thumbnails', $options);

		// Render the HTML and return it:
		$html = '<div style="position: relative; display: block;">';
		$html .= $image;
		$html .= $text;
		$html .= '</div>';
		return $html;
	}


	/**
	 * Generates the html-code for the detail photo
	 *
	 * @param string $path Path to the ImageFile
	 * @param integer $uid DAM-UID of the Image. Used for linking.
	 * @param string $altTag String to use as AltTag
	 * @param string $html return html
	 * @return string HTML-Code or image path
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function buildDetail($path, $uid, $altTag, $html = true)	{
		$options = array();
		if ($html) {
			$options['IMAGE'] = array();
			$options['IMAGE']['altText'] = $altTag;
			$options['IMAGE']['titleText'] = $altTag;
		}
		return $this->buildImage($path, $uid, 'detail', $options);
	}


	function buildImage($img, $uid, $profile = 'thumbnails', $options = array()) {
			$quality = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'quality', $profile);
			$width = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', $profile);
			$height = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', $profile);
			$info = pathinfo($img);
			$name = substr($info['basename'], 0 , strlen($info['basename']) - strlen($info['extension']) - 1);
			$ret = $target = 'typo3temp/ce_gallery/d_' . $uid . '_' . $width . '_' . $height . '_' . $quality . '.' . $info['extension'];
			if (!file_exists($target))
				 $this->resizeImage($width, $height, $quality, $img, $target);
			if (!empty($options)) {
				if (is_array($options['IMAGE'])) {
					$cObj = t3lib_div::makeInstance('tslib_cObj');
					$cObj->setParent($this->cObj->data, $this->cObj->currentRecord);
					$options['IMAGE']['file'] = $target;
					$ret = $cObj->cObjGetSingle('IMAGE',$options['IMAGE']);
				}
			}
			return $ret;
	}

	/**
	 * function resizeImage($width, $height, $source, $target)
	 * Resizes the Image to the given values
	 *
	 * @param integer $width The maximum image width
	 * @param integer $height The maximum image height
	 * @param integer $quality Quality
	 * @param string $source The source file
	 * @param string $target The target file
	 * @return void
	 * @since 2006-07-26
	 * @author Christian Ehret <chris@ehret.name>
	 */
	function resizeImage($width, $height, $quality, $source, $target) {
		$gfxObj = t3lib_div::makeInstance("t3lib_stdgraphic");
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] == 1) {
			$stdGraphic = t3lib_div::makeInstance("t3lib_stdGraphic");
			$info = $stdGraphic->getImageDimensions($source);
			$options = array();
			$options["maxH"] = $height;
			$options["maxW"] = $width;
			$data = $stdGraphic->getImageScale($info, $width . "m", $height . "m", $options);
			$params = '-geometry ' . $data[0] . 'x' . $data[1] . '! -quality ' . $quality . ' ';
			$im = $stdGraphic->imageMagickExec($source, $target, $params);
			return $im;
		} else {
			// Get new dimensions
			list($width_orig, $height_orig) = getimagesize($source);
			if ($width && ($width_orig < $height_orig)) {
				$width = ($height / $height_orig) * $width_orig;
			} else {
				$height = ($width / $width_orig) * $height_orig;
			}
			// Resample
			$image_p = imagecreatetruecolor($width, $height);
			$image = imagecreatefromjpeg($source);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			// Output
			imagejpeg($image_p, $target, $quality);
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ce_gallery/pi1/class.tx_cegallery_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ce_gallery/pi1/class.tx_cegallery_pi1.php']);
}

?>