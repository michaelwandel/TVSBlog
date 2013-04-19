<?php
	// TVSBlog-Title-Tag-Changer...zur Einbindung in das Seitentemplate...wer es braucht ;-)
	$table_pre = $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
	$art_table = $table_pre . "_articles";
	$tvsblog_post_id = rex_get('post_id', 'int', -1);
	
	$sql = new rex_sql();
	$sql->setQuery("SELECT * FROM " . $art_table . " WHERE id = " . $tvsblog_post_id);

	$title_add = "";
	if ($sql->getRows() > 0 )
		$title_add .= $sql->getValue('title') . " | ";
?>
