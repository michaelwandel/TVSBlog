<?php

	/*
	 * Addon TVSBlog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: index.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	$art_table		= $table_pre . "_articles";
	$cat_table		= $table_pre . "_categories";
	$thispage		= "tvsblog";
	$thissubpage	= "categories";
	$cat_title		= rex_post('cat_title', 'string', '');
	$mode			= rex_post('mode', 'string', '');
	$cancel			= rex_post('cancel', 'string', '');

	if ($func == "write" && $cancel == "")
	{
		$sql = new rex_sql();
		if ($mode == "add")
			$sql->setQuery("INSERT INTO " . $cat_table . " (title, create_date) VALUES ('" . $cat_title . "', " . time() . ")");
		else
			$sql->setQuery("UPDATE " . $cat_table . " SET title = '" . $cat_title . "' WHERE id = " .  rex_post('id', 'int', -1));

		$err = $sql->getError();
		if ($err == "") {
			echo rex_info("Einstellungen gespeichert!");
			if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
				rexseo_generate_pathlist(array());
			}			
		} else
			echo rex_warning($err);
	}
	elseif ($func == "delete")
	{
		$sql = new rex_sql();
		$sql->setQuery("DELETE FROM " . $cat_table . " WHERE id = " . rex_get('id', 'int', -1));
		echo rex_info("Kategorie gelöscht!");
		if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
			rexseo_generate_pathlist(array());
		}			
	}
	elseif ($func == "status")
	{
		$sql = new rex_sql();
		$sql->setQuery("UPDATE " . $cat_table . " SET status = NOT status WHERE id = " . rex_get('id', 'int', -1));
		if (OOAddon :: isAvailable('rexseo') || OOAddon :: isAvailable('rexseo42')) {
			rexseo_generate_pathlist(array());
		}			
	}

?>

<div class="rex-addon-output-v2">
	<form action="index.php?page=<?php echo $thispage; ?>&amp;subpage=<?php echo $thissubpage; ?>" method="post">
	<table class="rex-table" id="rex-articles">
		<colgroup>
			<col width="30" />
			<col width="30" />
			<col width="*" />
			<col width="50" />
			<col width="50" />
			<col width="50" />
		</colgroup>
		<thead>
			<tr>
				<th class="rex-icon"><a class="rex-i-element rex-i-article-add _rex488_icon_article-new" href="index.php?page=<?php echo $thispage; ?>&amp;subpage=<?php echo $thissubpage; ?>&amp;func=add"><span class="rex-i-element-text">hinzufügen</span></a></th>
				<th>ID</th>
				<th>Titel</th>
				<th colspan="3">Funktion / Status</th>
			</tr>
		</thead>
		<tbody>
<?php

	$sql = new rex_sql();
	$sql->setQuery("SELECT * FROM " . $cat_table);

	if ($sql->getRows() > 0 )
	{
		$baseURL = 'index.php?page='.$thispage.'&amp;subpage='.$thissubpage.'&amp;func=';
		for ($i = 1; $i <= $sql->getRows(); $i++) {
?>
			<tr>
				<td class="rex-icon"><a class="rex-i-element rex-i-article" href="<?php print $baseURL; ?>edit&amp;id=<?php echo $sql->getValue('id'); ?>"><span class="rex-i-element-text">Editieren</span></a></td>
				<td><?php echo $sql->getValue('id'); ?></td>
				<td><a href="<?php print $baseURL; ?>edit&amp;id=<?php echo $sql->getValue('id'); ?>"><?php echo htmlspecialchars($sql->getValue('title')); ?></a></td>
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
			<td colspan="6" align="center">
				<p>Es sind noch keine Kategorien vorhanden.</p>
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

	// Neue Kategorie anlegen
	if ($func == "add" || $func == "edit")
	{
		$cat_title = "";
		$id = rex_get('id', 'int', 0);

		if ($func == "edit" && $id > 0)
		{
			$sql = new rex_sql();
			$sql->setQuery("SELECT * FROM " . $cat_table . " WHERE id = " . $id);
			if ($sql->getRows() > 0 )
			{
				$cat_title = $sql->getValue('title');
			}
			$headtitle = "Kategorie bearbeiten:";
		}
		else
			$headtitle = "Neue Kategorie eingeben:";
?>

	<br />

	<div class="rex-addon-output">
		<h2 class="rex-hl2"><?php echo $headtitle; ?></h2>
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
					 <p class="rex-form-col-a rex-form-text">
						<label for="cat_title">Titel</label>
						<input id="cat_title" name="cat_title" type="text" class="rex-form-text" value="<?php echo htmlspecialchars($cat_title); ?>" />
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
	}

?>