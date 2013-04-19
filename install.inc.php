<?php

	/*
	 * Addon tvsblog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: install.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	$error		= '';
	$table_pre	= $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
	$art_table	= $table_pre . "_articles";
	//$source_dir	= $REX['INCLUDE_PATH'] . '/addons/tvsblog/files';
	//$dest_dir	= $REX['MEDIAFOLDER'] . '/';
	//$start_dir	= $REX['MEDIAFOLDER'] . '/addons/tvsblog/wmuSlider';
	$source_dir	= $REX['INCLUDE_PATH'] . '/addons/tvsblog/files';
	$dest_dir	= $REX['MEDIAFOLDER'] . '/addons/tvsblog/wmuSlider';

	if (is_dir($source_dir)) {
		if(!rex_copyDir($source_dir, $dest_dir)) {
			$error .= 'Verzeichnis '.$source_dir.' konnte nicht nach '.$dest_dir.' kopiert werden!';
		}
	}

	if (!OOAddon::isInstalled("image_manager")) {
		echo rex_warning("Das Image_Manager-Addon ist nicht installiert! Bitte das Ausgabemodul entsprechend anpassen!");
	} else {
		$db = new rex_sql();
		$db->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['image_manager'] . "_types WHERE name = 'rex_tvsblog_sliderimages'");
		if ($db->getRows() == 0 ) {
			$db->setQuery("INSERT INTO " . $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['image_manager']. "_types SET name = 'rex_tvsblog_sliderimages', description = 'TVSBlog - Slider- / Teasergrafiken', status = 1");
			$lastId = $db->getLastId();
			$params = 'a:8:{s:15:"rex_effect_crop";a:6:{s:21:"rex_effect_crop_width";s:0:"";s:22:"rex_effect_crop_height";s:0:"";s:28:"rex_effect_crop_offset_width";s:0:"";s:29:"rex_effect_crop_offset_height";s:0:"";s:20:"rex_effect_crop_hpos";s:6:"center";s:20:"rex_effect_crop_vpos";s:6:"middle";}s:22:"rex_effect_filter_blur";a:3:{s:29:"rex_effect_filter_blur_amount";s:2:"80";s:29:"rex_effect_filter_blur_radius";s:1:"8";s:32:"rex_effect_filter_blur_threshold";s:1:"3";}s:25:"rex_effect_filter_sharpen";a:3:{s:32:"rex_effect_filter_sharpen_amount";s:2:"80";s:32:"rex_effect_filter_sharpen_radius";s:3:"0.5";s:35:"rex_effect_filter_sharpen_threshold";s:1:"3";}s:15:"rex_effect_flip";a:1:{s:20:"rex_effect_flip_flip";s:1:"X";}s:23:"rex_effect_insert_image";a:5:{s:34:"rex_effect_insert_image_brandimage";s:0:"";s:28:"rex_effect_insert_image_hpos";s:4:"left";s:28:"rex_effect_insert_image_vpos";s:3:"top";s:33:"rex_effect_insert_image_padding_x";s:3:"-10";s:33:"rex_effect_insert_image_padding_y";s:3:"-10";}s:17:"rex_effect_mirror";a:5:{s:24:"rex_effect_mirror_height";s:0:"";s:33:"rex_effect_mirror_set_transparent";s:7:"colored";s:22:"rex_effect_mirror_bg_r";s:0:"";s:22:"rex_effect_mirror_bg_g";s:0:"";s:22:"rex_effect_mirror_bg_b";s:0:"";}s:17:"rex_effect_resize";a:4:{s:23:"rex_effect_resize_width";s:3:"625";s:24:"rex_effect_resize_height";s:0:"";s:23:"rex_effect_resize_style";s:7:"maximum";s:31:"rex_effect_resize_allow_enlarge";s:7:"enlarge";}s:20:"rex_effect_workspace";a:8:{s:26:"rex_effect_workspace_width";s:0:"";s:27:"rex_effect_workspace_height";s:0:"";s:25:"rex_effect_workspace_hpos";s:4:"left";s:25:"rex_effect_workspace_vpos";s:3:"top";s:36:"rex_effect_workspace_set_transparent";s:7:"colored";s:25:"rex_effect_workspace_bg_r";s:0:"";s:25:"rex_effect_workspace_bg_g";s:0:"";s:25:"rex_effect_workspace_bg_b";s:0:"";}}';
			$db->setQuery("INSERT INTO " . $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['image_manager'] . "_type_effects SET type_id = " . $lastId . ", effect = 'resize', prior = 1, parameters = '" . $params . "'");
			echo rex_info("Image_Manager-Bildtyp und Effekt wurde angelegt!");
			echo rex_info("Bitte beachten das jQuery (http://jquery.com) vorausgesetzt wird!");
			$db->setQuery("INSERT INTO " . $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog']. "_categories SET title = 'Allgemein', create_date = " . time() . ", status = 1");
		}
	}

	//
	// DB updaten...
	//
	function recursiveArraySearch($haystack, $needle, $index = null) {
		$aIt = new RecursiveArrayIterator($haystack);
		$it = new RecursiveIteratorIterator($aIt);
	   
		while($it->valid())	{
			if (((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle)) {
			 return $aIt->key();
			}
			$it->next();
		}
		return false;
	}
	
	$db = new rex_sql();
	$cols = $db->showColumns($art_table);
	if (recursiveArraySearch($cols,'filelist') == "")
		$db->setQuery('ALTER TABLE ' . $art_table . ' ADD filelist varchar(1024) NOT NULL AFTER description;');
	if (recursiveArraySearch($cols,'fb_image') == "")
		$db->setQuery('ALTER TABLE ' . $art_table . ' ADD fb_image varchar(255) NOT NULL AFTER description;');	
	//
	// Ende DB-Update
	//

	if($error != "")
		$REX['ADDON']['installmsg']['tvsblog'] = $error;
	else
		$REX['ADDON']['install']['tvsblog'] = true;

?>