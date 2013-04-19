--------
EINGABE:
--------

<?php
	$table_pre = $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
	$art_table = $table_pre . "_articles";
	$cat_table = $table_pre . "_categories";

	$tvsblog_entries = "";
	
	echo "<h1>TVSBlog</h1><br />";

	if ("REX_VALUE[1]" == "")
		$tvsblog_entries = 5;
	else
		$tvsblog_entries = "REX_VALUE[1]";
?>

<div style="width: 100%;">
	<div style="width:200px; float:left;">
		Anzahl Blogeinträge pro Seite:
	</div>
	<div style="width:200px; float:left;">
		<input name="VALUE[1]" type="text" size="5" value="<?php echo $tvsblog_entries; ?>" />
	</div>
	<div style="clear:both;"></div>

	<div style="width:200px; float:left;">
		Nur aus folgender Kategorie: 
	</div>
	<div style="width:200px; float:left;">
		<select name="VALUE[2]" class="rex-form-select">
			<option value=""></option>
			<?php
				$cat_sql = new rex_sql();
				$cat_sql->setQuery("SELECT * FROM " . $cat_table);

				if ($cat_sql->getRows() > 0 )
				{
					for ($i = 1; $i <= $cat_sql->getRows(); $i++)
					{
						if ("REX_VALUE[2]" == $cat_sql->getValue('id'))
							$selected = "selected";
						else
							$selected = "";
						echo '<option value="' . $cat_sql->getValue('id') . '" ' . $selected . '>' . htmlspecialchars($cat_sql->getValue('title')) . '</option>';
						$cat_sql->next();
					}
				}
			?>
		</select>
	</div>
	<div style="clear:both;"></div>

	<div style="width:200px; float:left;">
		Beiträge "anteasern":<br />(Anzahl Zeichen)
	</div>
	<div style="width:200px; float:left;">
		<input name="VALUE[3]" type="text" size="5" value="REX_VALUE[id="3" ifempty="0"]" /> (0 = Keine Kürzung!)
	</div>
	<div style="clear:both;"></div>

</div>


--------
AUSGABE:
--------

<?php

	if($REX['REDAXO'] != 1) {
		require_once ($REX['INCLUDE_PATH'] .'/addons/tvsblog/classes/urlRewrite.inc.php');

		$table_pre	= $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
		$thispage	= "tvsblog";
		$art_table	= $table_pre . "_articles";
		$cat_table	= $table_pre . "_categories";

		$tvsblog_post_id = rex_get('post_id', 'int', -1);

		$tvsblog_tag = rex_get('tag', 'string', '');
		$tvsblog_teaser = "REX_VALUE[3]";

		$tvsblog_start = rex_get('tvsblog_start', 'int', -1);
		if ($tvsblog_start != -1) {
			$tvsblog_limit = "REX_VALUE[1]";
		}
		else
			if ("REX_VALUE[1]" == "") {
				$tvsblog_start = 0;
				$tvsblog_limit = 5;
			}
			else {
				$tvsblog_start = 0;
				$tvsblog_limit = "REX_VALUE[1]";
			}

		$myIniFile = $REX['INCLUDE_PATH'] . "/addons/" . $thispage . "/" . $thispage . ".ini";
		$settings = parse_ini_file($myIniFile);

		$sql_limit = " LIMIT " . $tvsblog_start . ", " . $tvsblog_limit;
		
		$tvsblog_category = rex_get('tvsblog_category', 'int', -1);
		$tvsblog_month = rex_get('tvsblog_month', 'int', -1);
		$tvsblog_year = rex_get('tvsblog_year', 'int', -1);
		
		if ($tvsblog_category == -1 && "REX_VALUE[2]" != "")
			$tvsblog_category = "REX_VALUE[2]";

		if ($tvsblog_month <> -1 && $tvsblog_year <> -1) {
			$tvssql = " AND YEAR(FROM_UNIXTIME(create_date)) = " . $tvsblog_year . " AND MONTH(FROM_UNIXTIME(create_date)) = " . $tvsblog_month;
		}
		else if($tvsblog_category <> -1) {
			$tvssql = " AND categories = " . $tvsblog_category;

			$sql = new rex_sql();
			$sql->setQuery("SELECT * FROM " . $cat_table . " WHERE id = " . $tvsblog_category);
			if ($sql->getRows() > 0 ) {
				//echo "<h1>Die neuesten Blogeinträge aus der Kategorie '" . $sql->getValue('title') . "':</h1>";
			}
		}
		else {
			$tvssql = "";
		}
		
		if ($tvsblog_tag != "")
			$tvssql .= " AND keywords like '%" . $tvsblog_tag . "%'";
			
		// post_id hat Priorität...
		if ($tvsblog_post_id != -1)
			$tvssql .= " AND id = " . $tvsblog_post_id;
			
		$cat_sql = new rex_sql();
		$sql = new rex_sql();
		//$art_query = "SELECT * FROM " . $art_table . " INNER JOIN " . $cat_table . " ON " . $art_table . ".categories = " . $cat_table . ".id WHERE " . $art_table . ".status = 1 AND " . $cat_table . ".status = 1 " . $tvssql . " ORDER BY " . $art_table . ".create_date DESC, " . $art_table . ".id DESC";
		$art_query = "SELECT * FROM " . $art_table . " WHERE status = 1 " . $tvssql . " ORDER BY create_date DESC, id DESC";
		$sql->setQuery($art_query);
		$total_rows = $sql->getRows();
		
		$art_query = "SELECT * FROM " . $art_table . " WHERE status = 1 " . $tvssql . " ORDER BY create_date DESC, id DESC";
		$sql->setQuery($art_query . " " . $sql_limit);

		if ($sql->getRows() > 0 )
		{
			for ($i = 1; $i <= $sql->getRows(); $i++) {
				$art_post_id = $sql->getValue('id');
				$cat_sql->setQuery("SELECT * FROM " . $cat_table . " WHERE id = " . $sql->getValue('categories'));
				if ($cat_sql->getRows() > 0 )
					$cat_name = strtolower(" tvsblog_" . $cat_sql->getValue('title'));
				else
					$cat_name = "";
					
				if ($cat_sql->getValue('status') == 1) {

					//
					// Ausgabe...
					//
					echo "<div class=\"tvsblog_entry" . $cat_name . "\">";
					
					if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
						echo "  <h2><a href='" . getTVSBlogURL($art_post_id, $REX['CUR_CLANG']) . "'>" . $sql->getValue('title') . "</a></h2>";
					} else {
						echo "  <h2><a href='" . rex_getUrl('','') . "?post_id=" . $art_post_id . "'>" . $sql->getValue('title') . "</a></h2>";
					}
					if ($sql->getValue('create_user') != "") {
						echo "	<div class=\"tvsblog_author\">";
						echo "		<p>Autor: " . $sql->getValue('create_user') . " am " . date("d.m.Y", $sql->getValue('create_date')) . "</p>";
						echo "	</div>";
					}
					//
					// Slider-/ Teaserbild-Ausgabe
					//
					if ($sql->getValue('filelist') != "") {
						$filelist = $sql->getValue('filelist');
						$sliderfiles = explode(",",$filelist);
						if (count($sliderfiles) == 1) {
							$media = OOMedia::getMediaByName($sliderfiles[0]);
							$img_title = $media->getTitle();

							echo "<div class=\"tvsblog_teaserimage\">";
							echo "	<img src=\"index.php?rex_img_type=rex_tvsblog_sliderimages&amp;rex_img_file=" . $sliderfiles[0] . "\" alt=\"" . $img_title . "\" title=\"" . $img_title . "\" />";
							echo "</div>";
						} else {
							echo "<div class=\"wmuSlider postslider" . $art_post_id . "\">";
							echo "	<div class=\"wmuSliderWrapper\">";
							foreach ($sliderfiles as $value) {
								$media = OOMedia::getMediaByName($value);
								$img_title = $media->getTitle();
								echo "<article>";
								echo "	<img src=\"index.php?rex_img_type=rex_tvsblog_sliderimages&amp;rex_img_file=" . $value . "\" alt=\"" . $img_title . "\" title=\"" . $img_title . "\" />";
								echo "</article>";
							}
							echo "	</div>";
							echo "</div>";
						}
						echo "<script>";
						echo "$('.postslider" . $art_post_id . "').wmuSlider({";
						echo "	touch: false,";
						echo "	animation: 'slide',";
						echo "	animationDuration:1000,";
						echo "	slideshowSpeed: 7000,";
						echo "	items:1";
						echo "});";
						echo "</script>";
					}
					
					$tvsoutput = $sql->getValue('description');
					if ($tvsblog_teaser > 0 && $tvsblog_post_id == -1) {
						if( (strlen($tvsoutput) > $tvsblog_teaser) ) {

							$whitespaceposition = strpos($tvsoutput," ",$tvsblog_teaser)-1;

							if( $whitespaceposition > 0 )
								$tvsoutput = substr($tvsoutput, 0, ($whitespaceposition+1));

							// close unclosed html tags
							if( preg_match_all("|<([a-zA-Z]+)>|",$tvsoutput,$aBuffer) ) {
								if( !empty($aBuffer[1]) ) {
									preg_match_all("|</([a-zA-Z]+)>|",$tvsoutput,$aBuffer2);
									if( count($aBuffer[1]) != count($aBuffer2[1]) ) {
										foreach( $aBuffer[1] as $index => $tag ) {
											if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
												$tvsoutput .= '</'.$tag.'>';
										}
									}
								}
							}
							if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
								$tvsoutput .= "<a href='" . getTVSBlogURL($art_post_id, $REX['CUR_CLANG']) . "'>Weiterlesen...</a>";
							} else {
								$tvsoutput .= "<a href=\"" . rex_getUrl('','', array('post_id'=>$sql->getValue('id')), '&amp;') . "\">Weiterlesen...</a>";
							}
						} 
					}
					
					// Editorenausgabe konfigurieren...
					if (OOAddon::isInstalled("tinymce")) {
						$article = new rex_article();
						echo $article->replaceLinks($tvsoutput);
					} else if(OOAddon::isAvailable('markitup')) {
						$textile = htmlspecialchars_decode($tvsoutput);
						$textile = str_replace("<br />","",$textile);
						$textile = rex_a79_textile($textile);
						echo $textile = markitup_previewlinks($textile);  
					} else {
						echo $tvsoutput;
					}

					if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
						$url = getTVSBlogURL($art_post_id, $REX['CUR_CLANG']);
					} else {
						$url = urlencode($REX["SERVER"] . rex_getUrl('','') . "?post_id=" . $sql->getValue('id'));
					}
					if ($settings['showFacebook'] == "1") {
						if (($tvsblog_post_id != "") && ($settings['Facebook_App_ID'] != "")) {
							?>
							<div id="fb-root"></div>
							<script>
								(function(d, s, id) {
									var js, fjs = d.getElementsByTagName(s)[0];
									if (d.getElementById(id)) return;
									js = d.createElement(s); js.id = id;
									js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1&appId=<?php echo $settings['Facebook_App_ID']; ?>";
									fjs.parentNode.insertBefore(js, fjs);
								}(document, 'script', 'facebook-jssdk'));
							</script>
							<div class="fb-comments" data-href="<?php echo $url; ?>" data-num-posts="2" data-width="470"></div>
							<?php
						}
						else {
							?>
							<div style="text-align:left; padding-top: 10px;">
								<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo $url; ?>&amp;layout=button_count" scrolling="no" frameborder="0" style="border:none; width:500px; height: 50px;"></iframe>
							</div>
							<?php
						}
					}
					if ($sql->getValue('keywords') != "") {
						echo "	<div class=\"tvsblog_tags\">";
						echo "		<h5>Schlagworte:</h5>";
						$tags = explode(",", $sql->getValue('keywords'));
						$tags_out = "";
						foreach ($tags as $value) {
							$tags_out .= "<a href='" . rex_getUrl('','', array('tag'=>trim($value)), '&amp;') . "'>$value</a>, ";
						}
						$tags_out = substr($tags_out, 0, - 2);
						echo $tags_out;
						echo "	</div>";
					}
					echo "</div>";
				}
				$sql->next();
			}
			
			// Zurück-zum-Blog-Button
			if ($tvsblog_post_id != -1) {
				echo "<div class=\"tvsblog_back\"><a href=\"" . rex_getUrl('','', array('tvsblog_start'=>trim($tvsblog_start), 'tvsblog_category'=>trim($tvsblog_category)), '&amp;') . "\">&laquo; Zurück zum Blog</a></div>";
			}
			
			//Navigation vor und zurück
			if ($tvsblog_start > 0) {
				echo "<div class=\"tvsblog_navigation tvsblog_left_navigation\">";
				echo "	<a href=\"" . rex_getUrl('','', array('tvsblog_start'=>trim($tvsblog_start - $tvsblog_limit), 'tvsblog_category'=>trim($tvsblog_category)), '&amp;') . "\">&laquo; Neuere Beiträge</a>";
				echo "</div>";
			}
			if (($tvsblog_start + $tvsblog_limit) < $total_rows) {
				echo "<div class=\"tvsblog_navigation tvsblog_right_navigation\">";
				echo "	<a href=\"" . rex_getUrl('','', array('tvsblog_start'=>trim($tvsblog_start + $tvsblog_limit), 'tvsblog_category'=>trim($tvsblog_category)), '&amp;') . "\">Ältere Beiträge &raquo;</a>";
				echo "</div>";
			}
		}
		else
			echo "<h1>Noch keine Beiträge in dieser Kategorie</h1>";
	} else {
		$filtercat = "REX_VALUE[2]";
		if ($filtercat == "") $filtercat = "(ALLE)";
		echo "TVSBlog-Ausgabe mit REX_VALUE[1] Beiträgen auf einer Seite aus der Kategorie: " . $filtercat;
	}
?>