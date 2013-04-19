<?php

/**
 * Addon Framework Classes 
 * @author staab[at]public-4u[dot]de Markus Staab
 * @author <a href="http://www.public-4u.de">www.public-4u.de</a>
 * @package redaxo3
 * @version $Id: help.inc.php,v 1.1 2006/08/04 17:46:28 kills Exp $
 */
 
if ( !isset( $mode)) $mode = '';
switch ( $mode) {
   case 'changelog': $file = '_changelog.txt'; break;
   default: $file = '_readme.txt'; 
}

echo str_replace( '+', '&nbsp;&nbsp;+', nl2br( file_get_contents( dirname( __FILE__) .'/'. $file)));

?>