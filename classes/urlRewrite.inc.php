<?php
/**
 * @author designworks Kemmereit - Ronny Kemmereit
 * @author <a href="http://www.dw-k.net">www.dw-k.net</a>
 * @package redaxo4 
 * @version $Id: urlRewrite.inc.php 357 2012-11-12 17:04:35Z rkemmere $
 */
 
 
	if (OOAddon :: isAvailable('rexseo')) {
		require_once dirname(__FILE__) ."/../../rexseo/classes/class.rexseo_rewrite.inc.php";
	}
	
	if (OOAddon :: isAvailable('rexseo42')) {
		require_once dirname(__FILE__) ."/../../rexseo42/classes/class.rexseo_rewrite.inc.php";
	}

/*
 * Gibt die URL des Blogposts zurueck.
 * @param int $cat_id ID der Kategorie
 * @param int $clang Redaxo SprachID
 * @return String URL des Blogposts
 */
function getTVSBlogURL($m_post_id = '', $clang = 0) {
  
	global $REX;

	$mytvsBlogIniFile	= $REX['INCLUDE_PATH'] . "/addons/tvsblog/tvsblog.ini";
	$tvsblogsettings	= parse_ini_file($mytvsBlogIniFile );
	$blogArticle_id		= $tvsblogsettings['blogArticle_id'];

	$url = $REX['SERVER'];

	if ($blogArticle_id != "") {
		$table_pre			= $REX['TABLE_PREFIX'].$REX['ADDON']['rxid']['tvsblog'];
		$tvsblog_art_table	= $table_pre . "_articles";
		$tvsblog_cat_table	= $table_pre . "_categories";

		$query_art = "SELECT  a.title as art_title, a.id as art_id, a.categories, c.title as cat_title, c.id as cat_id
					  FROM " . $tvsblog_art_table . " as a
					  INNER JOIN 
					  " . $tvsblog_cat_table . " c ON (a.categories  =  c.id)
					  WHERE a.status = 1 
					  AND a.id = ". $m_post_id;
					  
		$sql_art = new rex_sql();
		$sql_art -> setQuery($query_art);
		$rows_art = $sql_art -> getRows();

		for($j = 0; $j < $rows_art; $j++) {
		  
			//fb( $sql_art->getValue("title") ,FirePHP::INFO);   

			$art_id	= $sql_art->getValue("art_id");       
			$art_title= $sql_art->getValue("art_title");   
			$cat_id	= $sql_art->getValue("cat_id");   
			$cat_title= $sql_art->getValue("cat_title");   

		  
			$pathname = '';

			// Option fuer Mehrsprachigkeit
			if (OOAddon :: isAvailable('rexseo'))
				if (count($REX['CLANG']) > 1 && $clang != $REX['ADDON']['rexseo']['settings']['hide_langslug'])
					$pathname = rexseo_appendToPath($pathname, $REX['CLANG'][$clang], $REX['START_ARTICLE_ID'], $clang);

			if (OOAddon :: isAvailable('rexseo42'))
				if (count($REX['CLANG']) > 1 && $clang != $REX['ADDON']['rexseo42']['settings']['hide_langslug'])
					$pathname = rexseo_appendToPath($pathname, $REX['CLANG'][$clang], $REX['START_ARTICLE_ID'], $clang);
		   
			// Erzeugung Artikelname
			$blog_article = OOArticle::getArticleByID($blogArticle_id);
			$blog_article_name = $blog_article->getName();
			$pathname = rexseo_appendToPath($pathname, $blog_article_name, $blogArticle_id, $lang_id);

			// Erzeugung Kategoriename
			$pathname = rexseo_appendToPath($pathname, $cat_title, $cat_id, $clang);
			
			// Erzeugung Alternativ wenn SEO URL im Blogpost gespeichert wird
			// DB Schema muss erweitert werden
			/*
			if ($sql -> getValue("seourl") != "") {
					$pathname = rexseo_appendToPath($pathname, $sql -> getValue("seourl"), $article_id, $clang);
				} else {
					$pathname = rexseo_appendToPath($pathname, $art_title, $art_id, $lang_id);
				}   */       
			
			// Erzeugung Artikelname
			$pathname = rexseo_appendToPath($pathname, $art_title, $art_id, $clang);
									
				// ALLGEMEINE URL ENDUNG
			if (OOAddon :: isAvailable('rexseo'))
				$pathname = substr($pathname,0,strlen($pathname)-1).$REX['ADDON']['rexseo']['settings']['url_ending'];

			if (OOAddon :: isAvailable('rexseo42'))
				$pathname = substr($pathname,0,strlen($pathname)-1).$REX['ADDON']['rexseo42']['settings']['url_ending'];
			
			$sql_art -> next();
		}

		$url .= $pathname;
	}

	return $url;
}

/*
 * Aendert die RexSEO Pfadliste und fuegt die Kategorien/Posttitle des Addons hinzu.
 */
function tvsblog_extended_urls($_params) {
   
	global $REX;

	$mytvsBlogIniFile	= $REX['INCLUDE_PATH'] . "/addons/tvsblog/tvsblog.ini";
	$tvsblogsettings	= parse_ini_file($mytvsBlogIniFile );
	$blogArticle_id		= $tvsblogsettings['blogArticle_id'];

	if ($blogArticle_id != "") {
		$table_pre			= $REX['TABLE_PREFIX'].$REX['ADDON']['rxid']['tvsblog'];
		$tvsblog_art_table	= $table_pre . "_articles";
		$tvsblog_cat_table	= $table_pre . "_categories";
		$tvsblog_category	= rex_get('tvsblog_category', 'int', -1);

		$query = "SELECT * FROM " . $tvsblog_cat_table . " WHERE status = 1 ORDER BY id";

		$sql = new rex_sql();
		$sql -> setQuery($query);
		$rows = $sql -> getRows();

		for($i = 0; $i < $rows; $i++) {

			$cat_id = $sql->getValue("id");
			$cat_title = $sql->getValue("title");

			$query_art = "SELECT * FROM " . $tvsblog_art_table . " 
							WHERE status = 1 
							AND categories = ". $cat_id;
					  
			$sql_art = new rex_sql();
			$sql_art -> setQuery($query_art);
			$rows_art = $sql_art -> getRows();
			
			for($j = 0; $j < $rows_art; $j++) {
			  
				//fb( $sql_art->getValue("title") ,FirePHP::INFO);   

				$art_id = $sql_art->getValue("id");       
				$art_title = $sql_art->getValue("title");   
			  
				//foreach($REX['CLANG'] as $lang_id => $lang_name) {
			  
					$lang_id = 0;
					$pathname = '';
					// Option fuer Mehrsprachigkeit
					if (OOAddon :: isAvailable('rexseo'))
						if (count($REX['CLANG']) > 1 && $lang_id != $REX['ADDON']['rexseo']['settings']['hide_langslug'])
							$pathname = rexseo_appendToPath($pathname, $REX['CLANG'][$lang_id], $REX['START_ARTICLE_ID'], $lang_id);

					if (OOAddon :: isAvailable('rexseo42'))
						if (count($REX['CLANG']) > 1 && $lang_id != $REX['ADDON']['rexseo42']['settings']['hide_langslug'])
							$pathname = rexseo_appendToPath($pathname, $REX['CLANG'][$lang_id], $REX['START_ARTICLE_ID'], $lang_id);

					// Erzeugung Artikelname
					$blog_article = OOArticle::getArticleByID($blogArticle_id);
					$blog_article_name = $blog_article->getName();
					$pathname = rexseo_appendToPath($pathname, $blog_article_name, $blogArticle_id, $lang_id);

					// Erzeugung Kategoriename
					$pathname = rexseo_appendToPath($pathname, $cat_title, $cat_id, $lang_id);

					// Erzeugung Alternativ wenn SEO URL im Blogpost gespeichert wird
					// DB Schema muss erweitert werden
					/*
					if ($sql -> getValue("seourl") != "") {
					$pathname = rexseo_appendToPath($pathname, $sql -> getValue("seourl"), $article_id, $clang);
					} else {
					$pathname = rexseo_appendToPath($pathname, $art_title, $art_id, $lang_id);
					}   */       

					// Erzeugung Artikelname
					$pathname = rexseo_appendToPath($pathname, $art_title, $art_id, $lang_id);
						
					// ALLGEMEINE URL ENDUNG
					if (OOAddon :: isAvailable('rexseo'))
						$pathname = substr($pathname,0,strlen($pathname)-1).$REX['ADDON']['rexseo']['settings']['url_ending'];

					if (OOAddon :: isAvailable('rexseo42'))
						$pathname = substr($pathname,0,strlen($pathname)-1).$REX['ADDON']['rexseo42']['settings']['url_ending'];

					// Todo
					// Ausgabekatgorie im Moment 10 muss noch ausgelesen werden           

					$_params['subject']['REXSEO_URLS'][$pathname] = array (
					'id'     => $blogArticle_id, // hier noch ersetzen
					'clang'  => $lang_id,
					'params' => array('post_id', $art_id)
					);
				//}
				$sql_art -> next();
			}
			$sql -> next();
		}
	}

	return $_params['subject'];

    /*
    Allgemeiner Hinweis:
    1. In der config.inc.php muessen folgende Zeilen hinzugefuegt werden:
    -----%<-----
    require_once dirname(__FILE__) ."/classes/urlRewrite.inc.php";
    rex_register_extension('REXSEO_PATHLIST_CREATED', 'tvsblog_extended_urls');
    -----%<-----

    2. An der Stelle, an der der Post geaendert werden kann muss nach der Aenderung noch folgender Befehl ausgefuehrt werden:
    -----%<-----
    // Pathliste neu generieren
    rexseo_generate_pathlist(array());
    -----%<-----
    */
}

/*
 * Aendert die RexSEO Sitemap und fuegt die URLs des Addons hinzu.
*/
function tvsblog_extended_sitemap($_params) {
  
	global $REX;
    
	$mytvsBlogIniFile	= $REX['INCLUDE_PATH'] . "/addons/tvsblog/tvsblog.ini";
	$tvsblogsettings	= parse_ini_file($mytvsBlogIniFile );
	$blogArticle_id		= $tvsblogsettings['blogArticle_id'];

	if ($blogArticle_id != "") {
		$table_pre			= $REX['TABLE_PREFIX'].$REX['ADDON']['rxid']['tvsblog'];
		$tvsblog_art_table	= $table_pre . "_articles";
		$tvsblog_cat_table	= $table_pre . "_categories";
			 
		$query = "SELECT * FROM " . $tvsblog_cat_table . " WHERE status = 1 ORDER BY id";
		
		$sql = new rex_sql();
		$sql->setQuery($query);
		$rows = $sql->getRows();
		
		for($i = 0; $i < $rows; $i++) {

			$cat_id		= $sql->getValue("id");
			$cat_title	= $sql->getValue("title");

			$query_art	= "SELECT * FROM " . $tvsblog_art_table . " 
							WHERE status = 1 
							AND categories = ". $cat_id;

			$sql_art = new rex_sql();
			$sql_art -> setQuery($query_art);
			$rows_art = $sql_art -> getRows();
				
			for($j = 0; $j < $rows_art; $j++) {
				  
				//fb( $sql_art->getValue("title") ,FirePHP::INFO);   

				$art_id	= $sql_art->getValue("id");       
				$art_title= $sql_art->getValue("title"); 

				//foreach($REX['CLANG'] as $lang_id => $lang_name) {

					$lang_id = 0;
					$pathname = '/';

					// Option fuer Mehrsprachigkeit
					if (OOAddon :: isAvailable('rexseo'))
						if (count($REX['CLANG']) > 1 && $lang_id != $REX['ADDON']['rexseo']['settings']['hide_langslug'])
							$pathname = rexseo_appendToPath($pathname, $REX['CLANG'][$lang_id], $REX['START_ARTICLE_ID'], $lang_id);

					if (OOAddon :: isAvailable('rexseo42'))
						if (count($REX['CLANG']) > 1 && $lang_id != $REX['ADDON']['rexseo42']['settings']['hide_langslug'])
							$pathname = rexseo_appendToPath($pathname, $REX['CLANG'][$lang_id], $REX['START_ARTICLE_ID'], $lang_id);

					// Erzeugung Artikelname
					$blog_article = OOArticle::getArticleByID($blogArticle_id);
					$blog_article_name = $blog_article->getName();
					$pathname = rexseo_appendToPath($pathname, $blog_article_name, $blogArticle_id, $lang_id);

					// Erzeugung Kategoriename
					$pathname = rexseo_appendToPath($pathname, $cat_title, $cat_id, $lang_id);
						
					// Erzeugung Alternativ wenn SEO URL im Blogpost gespeichert wird
					// DB Schema muss erweitert werden
					/*
					if ($sql -> getValue("seourl") != "") {
					$pathname = rexseo_appendToPath($pathname, $sql -> getValue("seourl"), $article_id, $clang);
					} else {
					$pathname = rexseo_appendToPath($pathname, $art_title, $art_id, $lang_id);
					}   */       

					// Erzeugung Artikelname
					$pathname = rexseo_appendToPath($pathname, $art_title, $art_id, $lang_id);
									
					// ALLGEMEINE URL ENDUNG
					if (OOAddon :: isAvailable('rexseo'))
						$pathname = substr($pathname,0,strlen($pathname)-1).$REX['ADDON']['rexseo']['settings']['url_ending'];

					if (OOAddon :: isAvailable('rexseo42'))
						$pathname = substr($pathname,0,strlen($pathname)-1).$REX['ADDON']['rexseo42']['settings']['url_ending'];

					$add_array[$lang_id] = array(
							'loc' => $pathname,
							'lastmod' => date('c'),
							'changefreq' => 'daily',
							'priority'   => 0.9
						);
				//}
				$_params['subject'][] = $add_array;

				$sql_art -> next();
				
			}
			$sql -> next();
		}
	}
	return $_params['subject'];
}

?>