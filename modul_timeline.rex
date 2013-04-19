--------
EINGABE:
--------

<?php
	echo "<h1>TVSBlog-Timeline-Menü</h1><br />";
?>

--------
AUSGABE:
--------

<?php

	$table_pre = $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
	$art_table = $table_pre . "_articles";
	$cat_table = $table_pre . "_categories";

	$tvsblog_month = rex_get('tvsblog_month', 'int', -1);
	$tvsblog_year = rex_get('tvsblog_year', 'int', -1);

	echo "<h2>Archiv:</h2>";
	$sql = new rex_sql();
	$sql->setQuery("SELECT * FROM " . $art_table . " WHERE status = 1 GROUP BY YEAR(FROM_UNIXTIME(create_date)), MONTH(FROM_UNIXTIME(create_date)) ORDER BY create_date DESC");
	if ($sql->getRows() > 0 )
	{
		echo "<ul>";
		if ($tvsblog_category == "")
			$tvsblog_class = "class=\"rex-current\"";
		else
			$tvsblog_class = "";
		// hier bei rex_getUrl evtl. noch die Artikelnummer der Blogausgabe angeben!
		echo "<li><a " . $tvsblog_class . " href=\"" . rex_getUrl('','', array('tvsblog_category'=>'-1'), '&amp;') . "\">Alle</a></li>";
		for ($i = 1; $i <= $sql->getRows(); $i++) {
			if ($tvsblog_category == $sql->getValue('id'))
				$tvsblog_class = "class=\"rex-current\"";
			else
				$tvsblog_class = "";
				
			$create = date("d.m.Y",$sql->getValue('create_date'));
			$date_exp = explode(".",$create);
			setlocale(LC_TIME, 'de_DE');
			$comment_date = strftime("%B %Y", mktime(0,0,0, $date_exp[1], $date_exp[0], $date_exp[2]));

			// hier bei rex_getUrl evtl. noch die Artikelnummer der Blogausgabe angeben!
			echo "<li><a " . $tvsblog_class . " href=\"" . rex_getUrl('','', array('tvsblog_month'=>$date_exp[1],'tvsblog_year'=>$date_exp[2]), '&amp;') . "\">" . $comment_date . "</a></li>";
			$sql->next();
		}
		echo "</ul>";
	}

?>