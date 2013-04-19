<?php

	/*
	 * Addon tvsblog
	 * @author wandel[at]thavis[dot]com Michael Wandel
	 * @author <a href="http://www.thavis.com">www.thavis.com</a>
	 * @package redaxo4
	 * @version $Id: config.inc.php,v 1.0 2010/07/16 12:00:00 kills Exp $
	 */

	rex_deleteDir('../files/tvsblog', true);

	$REX['ADDON']['install']['tvsblog'] = 0;
	// ERRMSG IN CASE: $REX['ADDON']['installmsg']['tvsblog'] = "Deinstallation fehlgeschlagen weil...";

?>