<script type="text/javascript">
	function moveWindow (){window.location.hash="showentry";}
</script>

<?php

	/*
	 * Addon TvsBlog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: index.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	$rexversion	= $REX['VERSION'] . $REX['SUBVERSION'];
	$art_table	= $table_pre . "_articles";
	$cat_table	= $table_pre . "_categories";
	$thispage	= "tvsblog";
	$thissubpage= "articles";

	//print_r($_GET);
	//print_r($_POST);

	$id				= rex_post('id', 'int', 0);
	if ($id == 0)
		$id			= rex_get('id', 'int', 0);
	$kategorie		= rex_post('kategorie', 'int', 0);
	$art_title		= rex_post('art_title', 'string', '');
	$description	= rex_post('description', 'string', '');
	$filelist		= rex_post('REX_MEDIALIST_1', 'string', '');
	$fb_image		= rex_post('REX_MEDIA_1', 'string', '');
	$keywords		= rex_post('keywords', 'string', '');
	$create_user	= rex_post('create_user', 'string', $REX['USER']->getValue('login'));
	$create_date	= rex_post('create_date', 'string', '');
	if ($create_date == "")
		$create_date= date("d.m.Y");
		
	$mode			= rex_post('mode', 'string', '');
	$cancel			= rex_post('cancel', 'string', '');

	$myIniFile = $REX['INCLUDE_PATH'] . "/addons/" . $thispage . "/" . $thispage . ".ini";

	function updateSitemap() {
		if (OOAddon::isAvailable('tvssitemap')) {
			if (function_exists('tvssitemapupdate')) {
				tvssitemapupdate();
			}
		}
	}

	function generateRss() {
		   
		global $REX;
		global $art_table;
		global $myIniFile;

		$settings = parse_ini_file($myIniFile);

		if ($settings['rssGenerate'] == 1) {
			$rssFilename = $settings['rssFilename'];
			if ($rssFilename == "")
				$rssFilename = "tvsblog.xml";

			if ($settings['rssFilename'] == "")
				$filename = $REX['MEDIAFOLDER'] . '/tvsblog.xml';
			else
				$filename = $REX['MEDIAFOLDER'] . '/' . $settings['rssFilename'];
			$file = @fopen($filename, "w-", $use_include_path);
			if ($file) {
				$actDate = date("D, j M Y H:i:s O");
				$output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
				$output .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
				$output .= "	<channel>\n";
				$output .= "	<atom:link href=\"http://" . $_SERVER['HTTP_HOST'] . "/files/" . $rssFilename . "\" rel=\"self\" type=\"application/rss+xml\" />\n";
				$output .= "    <title>" . $settings['rssTitle'] . "</title>\n";
				$output .= "    <link>" . $settings['rssLink'] . "</link>\n";
				$output .= "    <description>" . $settings['rssDescription'] . "</description>\n";
				$output .= "    <language>" . $settings['rssLanguage'] . "</language>\n";
				$output .= "    <copyright>" .$settings['rssCopyright']."</copyright>\n";
				$output .= "  <pubDate>" . $actDate . "</pubDate>\n";

				$sql = new rex_sql();
				$sql->setQuery("SELECT * FROM " . $art_table . " WHERE status = 1 ORDER BY create_date DESC");

				if ($sql->getRows() > 0 ) {
					for ($i = 1; $i <= $sql->getRows(); $i++) {
						$datum = date("D, j M Y H:i:s O",$sql->getValue('create_date'));
						$output .= "    <item>\n";
						$output .= "      <title>" . htmlspecialchars(urldecode($sql->getValue('title'))) . "</title>\n";
						$description = $sql->getValue('description');
						$article = new rex_article();
						$description = $article->replaceLinks($description);
						if ($sql->getValue('filelist') != "") {
							$imgfiles = explode(",",$sql->getValue('filelist'));
							$description = "<img src=\"http://" . $_SERVER['HTTP_HOST'] . "/index.php?rex_img_type=rex_tvsblog_sliderimages&amp;rex_img_file=" . $imgfiles[0] . "\" />" . $description;
						}
						$description = str_replace("src=\"files","src=\"http://" . $_SERVER['HTTP_HOST']."/files",$description);
						$description = str_replace("href=\"files","href=\"http://" . $_SERVER['HTTP_HOST']."/files",$description);
						
						$output .= "      <description><![CDATA[" . $description . "]]></description>\n";
						//$output .= "      <link>" . $settings['rssLink'] . "/index.php?article_id=" . $settings['blogArticle_id'] . "&amp;post_id=" . $sql->getValue('id') . "</link>\n";
						$output .= "      <link>http://" . $_SERVER['HTTP_HOST'] . "/" . rex_getUrl($settings['blogArticle_id'],'', array('post_id'=>$sql->getValue('id')), '&amp;') . "</link>\n";
						$output .= "      <author>" . $settings['rssAuthor'] . " (" . $settings['rssCopyright'] . ")</author>\n";
						$output .= "      <guid>http://" . $_SERVER['HTTP_HOST'] . "/index.php?article_id=" . $settings['blogArticle_id'] . "&amp;post_id=" . $sql->getValue('id') . "</guid>\n";
						$output .= "      <pubDate>" . $datum . "</pubDate>\n";
						$output .= "    </item>\n";
						$sql->next();
					}
				}
				$output .= "  </channel>\n</rss>\n";
				fwrite($file, $output);
				fclose($file);
				echo rex_info("RSS-Feed generiert!");
			}
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
	
	if ($func == "write" && $cancel == "") {
		// Bisschen Input-Checking...
		if ($kategorie == "") {
			echo rex_warning("Bitte eine Kategorie vergeben!");
		} else {
			$arr_date = explode(".", $create_date);
			$sql_create_date = mktime(0, 0, 0, $arr_date[1], $arr_date[0], $arr_date[2]);
			$sql = new rex_sql();
			if ($mode == "add")
				$sql->setQuery("INSERT INTO " . $art_table . " (categories, title, description, fb_image, filelist, keywords, create_user, create_date) VALUES (" . $kategorie . ", '" . $art_title . "', '" . $description . "', '" . $fb_image . "', '" . $filelist . "', '" . $keywords . "', '" . $create_user . "', " . $sql_create_date . ")");
			else {
				$sql->setQuery("UPDATE " . $art_table . " SET categories = " . $kategorie . ", title = '" . $art_title . "', description = '" . $description . "', fb_image = '" . $fb_image . "', filelist = '" . $filelist . "', keywords = '" . $keywords . "', create_user = '" . $create_user . "', create_date = " . $sql_create_date . " WHERE id = " . $id);
			}

			$err = $sql->getError();
			if ($err == "") {
				echo rex_info("Einstellungen gespeichert!");
				if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
					rexseo_generate_pathlist(array());
				}			
			} else
				echo rex_warning($err);
			generateRss();
			updateSitemap();
		}
	}
	elseif ($func == "delete")	{
		$sql = new rex_sql();
		$sql->setQuery("DELETE FROM " . $art_table . " WHERE id = " . rex_get('id', 'int', 0));
		echo rex_info("Artikel gelöscht!");
		if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
			rexseo_generate_pathlist(array());
		}			
		generateRss();
		updateSitemap();
	}
	elseif ($func == "status")
	{
		$sql = new rex_sql();
		$sql->setQuery("UPDATE " . $art_table . " SET status = NOT status WHERE id = " . rex_get('id', 'int', 0));
		if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
			rexseo_generate_pathlist(array());
		}			
		generateRss();
		updateSitemap();
	}
	elseif ($func == "add" || $func == "edit") {
		echo "<script type=\"text/javascript\">";
		echo "window.onload = function() {";
		echo "	moveWindow();";
		echo "}";
		echo "</script>";
	}

?>

<div class="rex-addon-output-v2">
	<form action="index.php?page=<?php echo $thispage; ?>&amp;subpage=<?php echo $thissubpage; ?>" method="post">
	<table class="rex-table" id="rex-articles">
		<caption>Liste der Artikel</caption>
		<colgroup>
			<col width="30" />
			<col width="30" />
			<col width="*" />
			<col width="150" />
			<col width="50" />
			<col width="50" />
			<col width="50" />
			<col width="50" />
		</colgroup>
		<thead>
			<tr>
				<th class="rex-icon"><a class="rex-i-element rex-i-article-add _rex488_icon_article-new" href="index.php?page=<?php echo $thispage; ?>&amp;subpage=<?php echo $thissubpage; ?>&amp;func=add"><span class="rex-i-element-text">hinzufügen</span></a></th>
				<th>ID</th>
				<th>Titel</th>
				<th>Kategorie</th>
				<th>Datum</th>
				<th colspan="3">Funktion / Status</th>
			</tr>
		</thead>
		<tbody>
<?php

	$cat_sql = new rex_sql();

	$sql = new rex_sql();
	$sql->setQuery("SELECT * FROM " . $art_table . " ORDER BY id DESC");

	$baseURL = 'index.php?page='.$thispage.'&amp;subpage='.$thissubpage.'&amp;func=';
	
	if ($sql->getRows() > 0 )
	{
		for ($i = 1; $i <= $sql->getRows(); $i++) {
?>
			<tr>
				<td class="rex-icon"><a class="rex-i-element rex-i-article" href="<?php print $baseURL; ?>edit&amp;id=<?php echo $sql->getValue('id'); ?>"><span class="rex-i-element-text">Editieren</span></a></td>
				<td><?php echo $sql->getValue('id'); ?></td>
				<td><a href="<?php print $baseURL; ?>edit&amp;id=<?php echo $sql->getValue('id'); ?>"><?php echo htmlspecialchars($sql->getValue('title')); ?></a></td>

<?php
				$cat_sql->setQuery("SELECT * FROM " . $cat_table . " WHERE id = " . $sql->getValue('categories'));
				if ($cat_sql->getRows() > 0 )
					echo "<td>" . $cat_sql->getValue('title') . "</td>";
				else
					echo "<td></td>";
?>
				<td><?php echo date("d.m.Y",$sql->getValue('create_date')); ?></td>
				<td><a href="<?php print $baseURL; ?>edit&amp;id=<?php echo $sql->getValue('id'); ?>">ändern</a></td>
				<td><a href="<?php print $baseURL; ?>delete&amp;id=<?php echo $sql->getValue('id'); ?>" onclick="return confirm('Sicher löschen?');">löschen</a></td>
<?php
				$status_description = ($sql->getValue('status') == 1) ? 'online' : 'offline';
				$status_classname = ($sql->getValue('status') == 1) ? 'rex-online' : 'rex-offline';
?>
				<td><a class="<?php echo $status_classname; ?>" href="<?php print $baseURL; ?>status&amp;id=<?php echo $sql->getValue('id'); ?>"><?php echo $status_description; ?></a></td>
			</tr>
<?php
			$sql->next();
		}
	}
	else
	{
?>
		<tr>
			<td colspan="8" align="center">
				<p>Es sind noch keine Beiträge vorhanden.</p>
			</td>
		</tr>
<?php
	}
?>
		</tbody>
	</table>
	</form>
</div>

	<br />

<?php

	// Neuen Artikel anlegen
	if ($func == "add" || $func == "edit") {
	
		$kategorie	= "";
		$art_title	= "";
		$description= "";
		$fb_image	= "";
		$filelist	= "";
		$keywords	= "";
		$create_user= "";
		$create_date= date("d.m.Y");

//		$id = rex_get('id', 'int', 0);

		if ($func == "edit" && $id > 0)
		{
			$sql = new rex_sql();
			$sql->setQuery("SELECT * FROM " . $art_table . " WHERE id = " . $id);
			if ($sql->getRows() > 0 )
			{
				$kategorie	= $sql->getValue('categories');
				$art_title	= $sql->getValue('title');
				$description= $sql->getValue('description');
				$fb_image	= $sql->getValue('fb_image');
				$filelist	= $sql->getValue('filelist');
				$keywords	= $sql->getValue('keywords');
				$create_user= $sql->getValue('create_user');
				$create_date= date("d.m.Y",$sql->getValue('create_date'));
			}
			$headtitle = "Artikel bearbeiten:";
		}
		else
			$headtitle = "Neuen Artikel eingeben:";
?>

<br />

<div class="rex-addon-output">
	<h2 class="rex-hl2" id="showentry"><?php echo $headtitle; ?></h2>
		<div id="rex-addon-editmode" class="rex-form">
		<form action="index.php?page=<?php echo $thispage; ?>&amp;subpage=<?php echo $thissubpage; ?>" method="post">
			<fieldset class="rex-form-col-1">

				<input type="hidden" name="page" value="<?php echo $thispage; ?>" />
				<input type="hidden" name="subpage" value="<?php echo $thissubpage; ?>" />
				<input type="hidden" name="func" value="write" />
				<input type="hidden" name="mode" value="<?php echo htmlspecialchars($func); ?>" />
				<input type="hidden" name="id" value="<?php echo $id; ?>" />

				<div class="rex-form-wrapper">

				<div class="rex-form-row">
				 <p class="rex-form-col-a rex-form-select">
					<label for="kategorie">Kategorie</label>
					<select name="kategorie" class="rex-form-select">
					<?php
						$cat_sql = new rex_sql();
						$cat_sql->setQuery("SELECT * FROM " . $cat_table);

						if ($cat_sql->getRows() > 0 )
						{
							for ($i = 1; $i <= $cat_sql->getRows(); $i++)
							{
								$catID = $cat_sql->getValue('id');
								
								if ($kategorie == $catID)
									$selected = "selected";
								else
									$selected = "";
								echo '<option value="' . $catID . '" ' . $selected . '>' . htmlspecialchars($cat_sql->getValue('title')) . '</option>';
								$cat_sql->next();
							}
						}
					?>
					</select>
					</p>
				</div>

				<div class="rex-form-row">
					 <p class="rex-form-col-a rex-form-text">
						<label for="art_title">Titel</label>
						<input id="art_title" name="art_title" type="text" class="rex-form-text" value="<?php echo htmlspecialchars($art_title); ?>" />
					</p>
				</div>

				<div class="rex-form-row">
					<p class="rex-form-col-a rex-form-text">
						<label for="art_filelist">Bilder / Slideshow</label>
							<table style="margin-left:150px;">
								<tr class="picchanger">
								<td>
									<table class="rexbutton">
										<tr>
											<td valign="top">
												<select name="REX_MEDIALIST_SELECT_1" id="REX_MEDIALIST_SELECT_1" size="8" style="width:200px;" class="inpgrey100">
												<?php
													if (trim($filelist) != "") {
														$arr = explode(",",$filelist);
														foreach ($arr as &$value) {
															echo "<option value=\"" . $value . "\">" . $value . "</option>";
														}
													}
												?>
												</select>
											</td>
											<td style="vertical-align: top;">
												<a href="javascript:moveREXMedialist(1,'top');"><img src="media/file_top.gif" width="16" height="16" vspace="2" title='^^' border="0" /></a><br/>
												<a href="javascript:moveREXMedialist(1,'up');"><img src="media/file_up.gif" width="16" height="16" vspace="2" title='^' border="0" /></a><br/>
												<a href="javascript:moveREXMedialist(1,'down');"><img src="media/file_down.gif" width="16" height="16" vspace="2" title='v' border="0" /></a><br/>
												<a href="javascript:moveREXMedialist(1,'bottom');"><img src="media/file_bottom.gif" width="16" height="16" vspace="2" title='vv' border="0" /></a>
											</td>
											<td class="inpicon" style="vertical-align: top;">
												<a href="javascript:openREXMedialist(1);"><img src="media/file_add.gif" width="16" height="16" vspace="2" title='+' border="0" /></a><br/>
												<a href="javascript:deleteREXMedialist(1);"><img src="media/file_del.gif" width="16" height="16" vspace="2" title='-' border="0" /></a>
											</td>
										</tr>
										<tr>
											<input type="hidden" name="REX_MEDIALIST_1" value="<?php echo $filelist; ?>" id="REX_MEDIALIST_1" />
										</tr>
									</table>
								</td>
								<td style="width:100px;"><div style="overflow:hidden;"></div></td>					
							</tr>
						</table>
					</p>
				</div>

				<?php
					// Editoren konfigurieren...
					if (OOAddon::isAvailable("tinymce")) {
						$textareaclass = "tinyMCEEditor";
						$textareaid = "";
						$textareastyle = "width:750px; height:400px;";
					} else if(OOAddon::isAvailable('markitup')) {
						a287_markitup::markitup('textarea.inp100');
						$textareaclass = "inp100";
						$textareaid = "";
						$textareastyle = "width:750px; height:400px;";
					} else if(OOAddon::isAvailable('ckeditor')) {
						if ($REX['ADDON']['ckeditor']['settings']['lazy_load']) {
							// lazy load ckeditor files
							echo rex_ckeditor_utils::getHtml();
							$textareaclass = "ckeditor";
							$textareaid = "ckeditor";
							$textareastyle = "";
						}
					} else {
						$textareaclass = "";
					}
				?>

				<div class="rex-form-row">
					<p class="rex-form-col-a rex-form-textarea">
						<label for="description">Beschreibung</label>
						<textarea name="description" class="<?php echo $textareaclass; ?>" id="<?php echo $textareaid; ?>" style="<?php echo $textareastyle; ?>"><?php echo htmlspecialchars($description); ?></textarea>
					</p>
				</div>

				<div class="rex-form-row">
					 <p class="rex-form-col-a rex-form-text">
						<label for="create_date">Datum</label>
						<input id="create_date" name="create_date" type="text" class="rex-form-text" value="<?php echo $create_date; ?>" />
					</p>
				</div>

				<div class="rex-form-row">
					 <p class="rex-form-col-a rex-form-text">
						<label for="internal_link">FB-Image<br /><br />(200x200px min.)<br /><br />(Für Facebook-Metatags)</label>
						<input type="hidden" name="REX_MEDIA_DELETE_1" value="0" id="REX_MEDIA_DELETE_1" />
						<input type="text" size="30" name="REX_MEDIA_1" value="<?php echo $fb_image; ?>" id="REX_MEDIA_1" readonly="readonly" />
						<a href="javascript:openREXMedia(1);"><img src="media/file_open.gif" width="16" height="16" title="Medienpool" border="0" /></a>
						<a href="javascript:deleteREXMedia(1);"><img src="media/file_del.gif" width="16" height="16" title="-" border="0" /></a>
						<a href="javascript:addREXMedia(1);"><img src="media/file_add.gif" width="16" height="16" title="+" border="0" /></a>
         				<label for="internal_link">&nbsp;</label>
         				<?
						if ($fb_image != "") {
							echo "<br /><br /><strong>Vorschau</strong>:<br />";
							echo "<img src=\"index.php?rex_resize=250w__$fb_image\" /><br />";
						}
						?>
					</p>
				</div>

				<div class="rex-form-row">
					 <p class="rex-form-col-a rex-form-text">
						<label for="keywords">Schlagworte<br /><br />(Komma-getrennt)</label>
						<input id="keywords" name="keywords" type="text" class="rex-form-text" value="<?php echo htmlspecialchars($keywords); ?>" />
					</p>
				</div>

				<div class="rex-form-row">
					 <p class="rex-form-col-a rex-form-text">
						<label for="keywords">Autor:</label>
						<input id="create_user" name="create_user" type="text" class="rex-form-text" value="<?php echo htmlspecialchars($create_user); ?>" />
					</p>
				</div>

				<div class="rex-form-row">
				</div>

				<div class="rex-form-row">
					<p class="rex-form-col-a rex-form-submit rex-form-submit-2">
						<input type="submit" name="submit" class="rex-form-submit submit" value="Speichern" />&nbsp;
						<input type="submit" name="cancel" class="rex-form-submit cancel" value="Abbrechen" />
					</p>
				</div>

			</div>
			</fieldset>
		</form>
		</div>
	</div>

<?php
		if (OOAddon::isAvailable("ckeditor")) {
?>
			<script type="text/javascript">
			jQuery(document).ready( function($) {
				$('#REX_FORM').submit(function() {
					// strip empty paragraphs out if there are any, can also be done via php in output module
					var data = CKEDITOR.instances.ckeditor.getData();
				
					if (data.match(/<p>\s*<\/p>\s\s+/g) || data.indexOf("<p></p>") != -1 || data.indexOf("<p>&nbsp;</p>") != -1) {
						data = data.replace(/<p>\s*<\/p>\s\s+/g, '');
						data = data.replace("<p></p>", "");
						data = data.replace("<p>&nbsp;</p>", "");
						CKEDITOR.instances.ckeditor.setData(data);
					}

					return true;
				});
			});    
			</script>
<?php
		}
	}

?>