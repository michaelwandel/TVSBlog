--------
EINGABE:
--------

<?php
	echo "<h1>TVSBlog-Kategoriemenü</h1><br />";
?>

--------
AUSGABE:
--------

<?php

	$table_pre = $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
	$art_table = $table_pre . "_articles";
	$cat_table = $table_pre . "_categories";

	$tvsblog_category = rex_get('tvsblog_category', 'int', -1);

	echo "<h2>Kategorien:</h2>";
	$sql = new rex_sql();
	$sql->setQuery("SELECT * FROM " . $cat_table . " WHERE status = 1 ORDER BY id");
	if ($sql->getRows() > 0 )
	{
		echo "<div id=\"tvsblog-categories\">";
		echo "	<ul>";
		if ($tvsblog_category == "")
			$tvsblog_class = "class=\"rex-current\"";
		else
			$tvsblog_class = "";
		// hier bei rex_getUrl evtl. noch die Artikelnummer der Blogausgabe angeben!
		echo "		<li><a " . $tvsblog_class . " href=\"" . rex_getUrl('','', array('tvsblog_category'=>'-1'), '&amp;') . "\">Alle</a></li>";
		for ($i = 1; $i <= $sql->getRows(); $i++) {
			if ($tvsblog_category == $sql->getValue('id'))
				$tvsblog_class = "class=\"rex-current\"";
			else
				$tvsblog_class = "";
			echo "		<li><a " . $tvsblog_class . " href=\"" . rex_getUrl('','', array('tvsblog_category'=>trim($sql->getValue('id'))), '&amp;') . "\">" . $sql->getValue('title') . "</a></li>";
			$sql->next();
		}
		echo "	</ul>";
		echo "</div>";
	}

?>