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
	 * HLstatsX Page Header This file will be inserted at the top of every page
	 * generated by HLstats. This file can contain PHP code.
	 */
	 
	// hit counter
	$db->query("UPDATE hlstats_Options SET value=value+1 WHERE keyname='counter_hits';"); 
  
	// visit counter
	if (isset($_COOKIE['ELstatsNEO_Visit']) && $_COOKIE['ELstatsNEO_Visit'] == 0) {
		// kein cookie gefunden, also visitcounter erh�hen und cookie setzen
		$db->query("UPDATE hlstats_Options SET value=value+1 WHERE keyname='counter_visits';");
		@setcookie('ELstatsNEO_Visit', '1', time() + ($g_options['counter_visit_timeout'] * 60), '/');   
	}
     
	global $game,$mode;

	// see if they have a defined style or a new style they'd like
	$selectedStyle = (isset($_COOKIE['style']) && $_COOKIE['style']) ? $_COOKIE['style'] : "";
	$selectedStyle = isset($_POST['stylesheet']) ? $_POST['stylesheet'] : $selectedStyle; 

	// if they do make sure it exists
	if(!empty($selectedStyle))
	{
		// this assumes that styles is up a directory from page_path, this might be a bad assumption
		$testfile=sprintf("%s/%s/%s", PAGE_PATH, '../styles', $selectedStyle);
		if(!file_exists($testfile))
		{
			$selectedStyle = "";
		}
	}
	
	// if they don't have one defined or the defined was is invalid use the default	
	if(empty($selectedStyle))
	{
		$selectedStyle=$g_options['style'];
	}	

	// if they had one, or tried to have one, set it to whatever we resolved it to
	if (isset($_POST['stylesheet']) || isset($_COOKIE['style']))
	{
		setcookie('style', $selectedStyle, time()+60*60*24*30);
	}

// this code here assumes that styles end with .css (the selector box for users and for admin does NOT check), someone may want to change this -octo
	// Determine if we have custom nav images available
    if ($selectedStyle) {
        $style = preg_replace('/\.css$/','',$selectedStyle);
    } else {
        $style = preg_replace('/\.css$/','',$g_options['style']);
    }
	$iconpath = IMAGE_PATH . "/icons";
	if (file_exists($iconpath . "/" . $style)) {
			$iconpath = $iconpath . "/" . $style;
	}
	

// include custom windmill functions 
include 'inc_functions.php';
?>
<!-- start header.php -->
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
<head>

<!-- DNA.styx -->
	<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="./assets/css/tailwind.output.css" />
    <script
      src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"
      defer
    ></script>
    <script src="./assets/js/init-alpine.js"></script>


<!-- 
	<link rel="stylesheet" type="text/css" href="hlstats.css" />
	<link rel="stylesheet" type="text/css" href="styles/<?php echo $selectedStyle; ?>" />
	<link rel="stylesheet" type="text/css" href="css/SqueezeBox.css" />
-->
	<!-- U R A SMACKHEAD -->
<!--
<?php
	if ($mode == 'players')
	{
		echo "\t<link rel=\"stylesheet\" type=\"text/css\" href=\"css/Autocompleter.css\" />\n";
	}
?>
	<link rel="SHORTCUT ICON" href="favicon.ico" />
	<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/mootools.js"></script>
	<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/SqueezeBox.js"></script>
	<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/heatmap.js"></script>
<?php
	if ($g_options['playerinfo_tabs'] == '1') {
?>
	<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/tabs.js"></script>
<?php
	}
?>
-->
	<title>DEV 
<?php
	echo $g_options['sitename']; 
	foreach ($title as $t)
	{
		echo " - $t";
	}
?>
	</title>
</head>
<body> 
<!--
<?php
	//JS Check

	if (isset($_POST['js']) && $_POST['js']) {
		$_SESSION['nojs'] = 0;
	} else {
		if ((!isset($_SESSION['nojs'])) or ($_SESSION['nojs'] == 1)) {
			// Send javascript form - if they have javascript enabled it will POST the JS variable, and the code above will update their session variable
			echo '
			<!-- Either this is your first visit in a while, or you don\'t have javascript enabled -->
			<form name="jsform" id="jsform" action="" method="post" style="display:none">
			<div>
			<input name="js" type="text" value="true" />
			<script type="text/javascript">
			document.jsform.submit();
			</script>
			</div>
			</form>'
			;
			$_SESSION['nojs'] = 1;
			$g_options['playerinfo_tabs'] = 0;
			$g_options['show_google_map'] = 0;
		}
	}
	
?>
<div class="block">
	
	<div class="headerblock">
		<div class="title">
			<a href="<?php echo $g_options['scripturl']; ?>"><img src="<?php echo $iconpath; ?>/title.png" alt="HLstatsX Community Edition" title="HLstatsX Community Edition" /></a>
		</div>

<?php

		// Grab count of active games -- if 1, we won't show the games list icons
		$resultGames = $db->query("
			SELECT
				COUNT(code)
			FROM
				hlstats_Games
			WHERE
				hidden='0'
		");
		
		list($num_games) = $db->fetch_row($resultGames);
		
		if ($num_games > 1 && $g_options['display_gamelist'] == 1) {
?>
		<div class="header_gameslist"><?php @include(PAGE_PATH .'/gameslist.php'); ?></div>
<?php	
		}
?>
	</div>
	<div class="location" style="clear:both;width:100%;">
		<ul class="fNormal" style="float:left">
<?php
			if ($g_options['sitename'] && $g_options['siteurl'])
			{
				echo '<li><a href="http://' . preg_replace('/http:\/\//', '', $g_options['siteurl']) . '">'. $g_options['sitename'] . '</a> <span class="arrow">&raquo;</span></li>';
			}
			echo '<li><a href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '">HLstatsX</a>';


			$i=0;
			foreach ($location as $l=>$url)
			{
				$url = preg_replace('/%s/', $g_options['scripturl'], $url);
				$url = preg_replace('/&/', '&amp;', $url);
				echo ' <span class="arrow">&raquo;</span></li><li>';
				if ($url) {
					echo "<a href=\"$url\">$l</a>";
				} else {
					echo "<strong>$l</strong>";
				}
				$i++;
		}
?>			</li>
		</ul>

<?php 
		if ($g_options['display_style_selector'] == 1) {
?>
		<div class="fNormal" style="float:right;"> 
			<form name="style_selection" id="style_selection" action="" method="post"> Style: 
				<select name="stylesheet" onchange="document.style_selection.submit()"> 
				<?php 
					$d = dir('styles'); 
					while (false !== ($e = $d->read())) { 
						if (is_file("styles/$e") && ($e != '.') && ($e != '..') && $e != $g_options['style']) { 
							$ename = ucwords(strtolower(str_replace(array('_','.css'), array(' ',''), $e))); 
							$styles[$e] = $ename; 
						} 
					}
					$d->close(); 
					asort($styles); 
					$styles = array_merge(array($g_options['style'] => 'Default'),$styles);
					foreach ($styles as $e => $ename) { 
						$sel = ''; 
						if ($e == $selectedStyle) $sel = ' selected="selected"'; 
						echo "\t\t\t\t<option value=\"$e\"$sel>$ename</option>\n"; 
					} ?> 
				</select> 
			</form> 
		</div> 
<?php
		}
?>
	</div>
	<div class="location_under" style="clear:both;width:100%;"></div>
</div>

<br />
      
<div class="content" style="clear:both;">
<?php
	global $mode;
	if ($g_options['bannerdisplay'] != 0 && ($mode == 'contents' || $g_options['bannerdisplay']==1)) {
?>    
	<div class="block" style="text-align:center;">
		<img src="<?php echo ((strncmp($g_options['bannerfile'], 'http:/', 6) == 0)?$g_options['bannerfile']:IMAGE_PATH.'/'.$g_options['bannerfile']); ?>" alt="Banner" />
	</div>
<?php
	}
?>        
-->


<!-- start Windmill Header -->
<div
      class="flex h-screen bg-gray-50 dark:bg-gray-900"
      :class="{ 'overflow-hidden': isSideMenuOpen}">
      <!-- Desktop sidebar -->
      <aside
        class="z-20 flex-shrink-0 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block">
        <div class="py-4 text-gray-500 dark:text-gray-400">
          <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="/"><?php echo $g_options['sitename']; ?></a>


<?php		  
if ($game != '') { 
	
	echo "		<ul class=\"mt-6\">\r\n";
	
	display_menu_item("Servers", "?game=$game");
	
	if ($g_options['nav_globalchat']==1) {
		display_menu_item("Chat", "?mode=chat&amp;game=$game");
	}

	display_menu_item("Players", "?mode=players&amp;game=$game");

	display_menu_item("Clans", "?mode=clans&amp;game=$game");

	if ($g_options["countrydata"]==1) {
		display_menu_item("Countries", "?mode=countryclans&amp;game=$game&amp;sort=nummembers");
	}

	display_menu_item("Awards", "?mode=awards&amp;game=$game");

	// look for actions
	$db->query("SELECT game FROM hlstats_Actions WHERE game='".$game."' LIMIT 1");
	if ($db->num_rows()>0) {

		display_menu_item("Actions", "?mode=actions&amp;game=$game");

	}

	display_menu_item("Weapons", "?mode=weapons&amp;game=$game");

	display_menu_item("Maps", "?mode=maps&amp;game=$game");

	$result = $db->query("SELECT game from hlstats_Roles WHERE game='$game' AND hidden = '0'");
	$numitems = $db->num_rows($result);
	if ($numitems > 0) {

		display_menu_item("Roles", "?mode=roles&amp;game=$game");
	}

	if ($g_options['nav_cheaters'] == 1) {

		display_menu_item("Bans", "?mode=bans&amp;game=$game");
		
	} 

	if (isset($_SESSION['loggedin'])) {

		display_menu_item("Admin Panel", "?mode=admin");

		display_menu_item("Logout", "?hlstats.php?logout=1");

	} else {

		display_menu_item("Admin Login", "?mode=admin");

	}

	echo "		</ul>\r\n";

} ?>
        </div>
      </aside>
      <!-- Mobile sidebar -->
      <!-- Backdrop -->
      <div
        x-show="isSideMenuOpen"
        x-transition:enter="transition ease-in-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
      ></div>
      <aside
        class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
        x-show="isSideMenuOpen"
        x-transition:enter="transition ease-in-out duration-150"
        x-transition:enter-start="opacity-0 transform -translate-x-20"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 transform -translate-x-20"
        @click.away="closeSideMenu"
        @keydown.escape="closeSideMenu"
      >
        <div class="py-4 text-gray-500 dark:text-gray-400">
          <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="/"><?php echo $g_options['sitename']; ?></a>

<?php		  
if ($game != '') { 
	
	echo "		<ul class=\"mt-6\">\r\n";
	
	display_menu_item("Servers", "?game=$game");
	
	if ($g_options['nav_globalchat']==1) {
		display_menu_item("Chat", "?mode=chat&amp;game=$game");
	}

	display_menu_item("Players", "?mode=players&amp;game=$game");

	display_menu_item("Clans", "?mode=clans&amp;game=$game");

	if ($g_options["countrydata"]==1) {
		display_menu_item("Countries", "?mode=countryclans&amp;game=$game&amp;sort=nummembers");
	}

	display_menu_item("Awards", "?mode=awards&amp;game=$game");

	// look for actions
	$db->query("SELECT game FROM hlstats_Actions WHERE game='".$game."' LIMIT 1");
	if ($db->num_rows()>0) {

		display_menu_item("Actions", "?mode=actions&amp;game=$game");

	}

	display_menu_item("Weapons", "?mode=weapons&amp;game=$game");

	display_menu_item("Maps", "?mode=maps&amp;game=$game");

	$result = $db->query("SELECT game from hlstats_Roles WHERE game='$game' AND hidden = '0'");
	$numitems = $db->num_rows($result);
	if ($numitems > 0) {

		display_menu_item("Roles", "?mode=roles&amp;game=$game");
	}

	if ($g_options['nav_cheaters'] == 1) {

		display_menu_item("Bans", "?mode=bans&amp;game=$game");
		
	} 

	if (isset($_SESSION['loggedin'])) {

		display_menu_item("Admin Panel", "?mode=admin");

		display_menu_item("Logout", "?hlstats.php?logout=1");

	} else {

		display_menu_item("Admin Login", "?mode=admin");

	}

	echo "		</ul>\r\n";

} ?>
		</div>
      </aside>
      <div class="flex flex-col flex-1 w-full">
        <header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">
          <div
            class="container flex items-center justify-between h-full px-6 mx-auto text-purple-600 dark:text-purple-300"
          >
            <!-- Mobile hamburger -->
            <button
              class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"
              @click="toggleSideMenu"
              aria-label="Menu"
            >
              <svg
                class="w-6 h-6"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
            <!-- Search input -->
            <div class="flex justify-center flex-1 lg:mr-32">
              <div
                class="relative w-full max-w-xl mr-6 focus-within:text-purple-500"
              >
                <div class="absolute inset-y-0 flex items-center pl-2">
                  <svg
                    class="w-4 h-4"
                    aria-hidden="true"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                      clip-rule="evenodd"
                    ></path>
                  </svg>
                </div>
                <input
                  class="w-full pl-8 pr-2 text-sm text-gray-700 placeholder-gray-600 bg-gray-100 border-0 rounded-md dark:placeholder-gray-500 dark:focus:shadow-outline-gray dark:focus:placeholder-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:placeholder-gray-500 focus:bg-white focus:border-purple-300 focus:outline-none focus:shadow-outline-purple form-input"
                  type="text"
                  placeholder="Player Search..."
                  aria-label="Search"
                />
              </div>
            </div>
            <ul class="flex items-center flex-shrink-0 space-x-6">
              <!-- Theme toggler -->
              <li class="flex">
                <button
                  class="rounded-md focus:outline-none focus:shadow-outline-purple"
                  @click="toggleTheme"
                  aria-label="Toggle color mode"
                >
                  <template x-if="!dark">
                    <svg
                      class="w-5 h-5"
                      aria-hidden="true"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                      ></path>
                    </svg>
                  </template>
                  <template x-if="dark">
                    <svg
                      class="w-5 h-5"
                      aria-hidden="true"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                  </template>
                </button>
              </li>
              <!-- Links menu -->
              <li class="relative">
                <button
                  class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple"
                  @click="toggleNotificationsMenu"
                  @keydown.escape="closeNotificationsMenu"
                  aria-label="Notifications"
                  aria-haspopup="true"
                >
                  Links
                </button>
                <template x-if="isNotificationsMenuOpen">
                  <ul
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click.away="closeNotificationsMenu"
                    @keydown.escape="closeNotificationsMenu"
                    class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:text-gray-300 dark:border-gray-700 dark:bg-gray-700"
                    aria-label="submenu"
                  >


				  <li class="flex">
                      <a
                        class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        href="<?php echo $g_options['scripturl'] ?>?mode=search"
                      >
                        <span>Search</span>
                      </a>
                    </li>


<?php
	if ($g_options['sourcebans_address']) {
?>
				  <li class="flex">
                      <a
                        class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        href="<?php echo $g_options['sourcebans_address'] ?>"
                      >
                        <span>SourceBans</span>
                      </a>
                    </li>
<?php
}
                
	if ($g_options['forum_address']) {
?>
				<li class="flex">
                      <a
                        class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        href="<?php echo $g_options['forum_address'] ?>"
                      >
                        <span>Forum</span>
                      </a>
                    </li>
<?php
}
?> 
				<li class="flex">
                      <a
                        class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        href="<?php echo $g_options['scripturl'] ?>?mode=help"
                      >
                        <span>Help</span>
                      </a>
                    </li>
                  </ul>
                </template>
              </li>
            </ul>
          </div>
        </header>
<!-- end header.php -->