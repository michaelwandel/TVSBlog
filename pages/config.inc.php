<?php

	/*
	 * Addon TvsBlog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: config.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	$art_table = $table_pre . "_articles";
	$cat_table = $table_pre . "_categories";
	$thispage = "tvsblog";
	$thissubpage = "config";
	$myIniFile = $REX['INCLUDE_PATH'] . "/addons/" . $thispage . "/" . $thispage . ".ini";

	$ini		= rex_post('ini', 'string', '');
	
	if ($ini != "") {
		$fh = fopen($myIniFile, 'w') or die("Can't open " . $thispage . ".ini.");
		$stringData = stripslashes($ini);
		fwrite($fh, $stringData);
		fclose($fh);
		echo rex_info("Einstellungen gespeichert!");
	}

	if (file_exists($myIniFile)) {
		$stringData = file_get_contents($myIniFile);
		$settings = parse_ini_file($myIniFile);
	}

?>

<style type="text/css">
	textarea {
		width		: 740px;
		height		: 300px;
		border		: 1px solid #CCCCCC;
		font-family	: monospace;
		font-size	: 12px;
	}
</style>

<div class="rex-addon-output">
	<h2 class="rex-hl2">Konfiguration</h2>
	<div class="rex-addon-content" >
		<form action="?page=<?php echo $thispage; ?>&subpage=<?php echo $thissubpage; ?>" method="post">
			<textarea name="ini"><?php echo $stringData; ?></textarea><br /><br />
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-submit rex-form-submit-2">
					<input type="submit" name="save" value="Speichern" />
				</p>
			</div>
		</form>
	</div>
</div>

<p>&nbsp;</p>

<?php
	$rssFilename = $settings['rssFilename'];
	if (trim($rssFilename) == "")
		$rssFilename = "tvsblog.xml";
	else
?>

<p>Der RSS-Feed kann dann per Link so verknüpft werden:<br /><br /><b>&lt;a href="files/<?php echo $rssFilename; ?>" title="RSS-Feed abonnieren" type="application/rss+xml"&gt;RSS-Feed&lt;/a&gt;</b></p>