<?php

	/*
	 * Addon TvsBlog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: index.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	// *************************************** INCLUDES

	$Basedir	= dirname(__FILE__);
	$table_pre	= $REX['TABLE_PREFIX'] . $REX['ADDON']['rxid']['tvsblog'];
	$version	= $REX['ADDON']['version']['tvsblog'];

	// *************************************** MAIN

	require $REX['INCLUDE_PATH']."/layout/top.php";

	//error_reporting(E_ALL);

	$subpages = array (
		array( 'articles', 'Artikel'),
		array( 'categories', 'Kategorien'),
		array( 'config', 'Konfiguration'),
	);

	rex_title('TvsBlog ' . $version, $subpages);

	$subpage = rex_request('subpage', 'string', '');
	$func    = rex_request('func', 'string', '');
	$view    = rex_request('view', 'string', '');

	switch ($subpage)
	{
	case 'articles' :
		$file = $Basedir.'/articles.inc.php';
		break;
	case 'categories' :
		$file = $Basedir.'/categories.inc.php';
		break;
	case 'config' :
		$file = $Basedir.'/config.inc.php';
		break;
	default:
		$file = $Basedir.'/articles.inc.php';
		break;
	}

    if ($file != "")
		require $file;

	require $REX['INCLUDE_PATH']."/layout/bottom.php";

?>