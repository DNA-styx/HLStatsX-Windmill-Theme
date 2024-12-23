<?php
/*
HLstatsX Community Edition - Real-time player and clan rankings and statistics
Copyleft (L) 2008-20XX Nicholas Hastings (nshastings@gmail.com)
http://www.hlxcommunity.com

HLstatsX Community Edition is a continuation of 
ELstatsNEO - Real-time player and clan rankings and statistics
Copyleft (L) 2008-20XX Malte Bayer (steam@neo-soft.org)
http://ovrsized.neo-soft.org/

ELstatsNEO is an very improved & enhanced - so called Ultra-Humongus Edition of HLstatsX
HLstatsX - Real-time player and clan rankings and statistics for Half-Life 2
http://www.hlstatsx.com/
Copyright (C) 2005-2007 Tobias Oetzel (Tobi@hlstatsx.com)

HLstatsX is an enhanced version of HLstats made by Simon Garner
HLstats - Real-time player and clan rankings and statistics for Half-Life
http://sourceforge.net/projects/hlstats/
Copyright (C) 2001  Simon Garner
            
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

For support and installation notes visit http://www.hlxcommunity.com
*/

    if (!defined('IN_HLSTATS')) {
        die('Do not access this file directly.');
    }
	
	
	/*
	 * HLstats Page Header
	 * This file will be inserted at the top of every page generated by HLstats.
	 * This file can contain PHP code.
	 */
Header ('Cache-Control: no-cache');
$lastpage = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"";

// Only allow windmill css files to be applied
if (substr($g_options['style'], 0, 8) == 'windmill') {
	$windmill_style = $g_options['style'];
}
 else {
	$windmill_style = 'windmill-purple.css';
}

// include custom windmill functions 
include 'includes/inc_windmill_functions.php';

?>
<!-- start ingame/header.php -->
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
<head>
	<meta charset="UTF-8">
	<link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet">
	  <link rel="stylesheet" href="./assets/css/tailwind.output.css">
	  <link rel="stylesheet" type="text/css" href="./assets/css/windmill.css">
	  <link rel="stylesheet" type="text/css" href="./styles/<?php echo $windmill_style; ?>">
	  <title>HLstatsX</title>
	</head>
<body> 
    
<div>
<?php
	global $mode;
	if ($g_options['bannerdisplay'] != 0 && ($mode == 'contents' || $g_options['bannerdisplay']==1)) {
?>    
	<div class="flex justify-center items-center">
		<img src="<?php echo $g_options['siteurl'] . ((strncmp($g_options['bannerfile'], 'http:/', 6) == 0)?$g_options['bannerfile']:IMAGE_PATH.'/'.$g_options['bannerfile']); ?>" alt="Banner">
	</div>
<?php
	}
?>
</div>
<!-- end ingame/header.php -->