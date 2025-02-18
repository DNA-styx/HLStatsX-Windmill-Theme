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
	
	// Control what pages are indexed by search engines to clean up search results and reduce load 
	// on the web server. Let's just allow servers, maps & playerinfo pages to be indexed. DNA.styx  

	if ($mode == 'contents') {
		//allow the contents and game pages
		$robot_meta_tag = "index, follow";
	}else if ($mode == 'players'){
		// No index but follow the player page to get to the playerinfo pages
		$robot_meta_tag = "noindex, follow";
	}else if ($mode == 'playerinfo'){
		// Index the playerinfo pages but do not follow any deeper 
		$robot_meta_tag = "index, nofollow";
	}else if ($mode == 'servers'){
		// index and but don't follow links from the servers page
		$robot_meta_tag = "index, nofollow";
	}else if ($mode == 'maps'){
		// index and but don't follow the map page (so map makers can track servers using their maps)
		$robot_meta_tag = "noindex, follow";
	}else if ($mode == 'mapinfo'){
		// index and but don't follow the map page (so map makers can track servers using their maps)
		$robot_meta_tag = "index, nofollow";
	}else{
		// Otherwise don't don't index or follow this page
		$robot_meta_tag = "noindex, nofollow";
	}

// Only allow windmill css files to be applied
if (substr($g_options['style'], 0, 8) == 'windmill') {
	$windmill_style = $g_options['style'];
}
 else {
	$windmill_style = 'windmill-purple.css';
}

// include custom windmill functions 
include_once INCLUDE_PATH . '/inc_windmill_functions.php';

?>
<!-- start header.php -->
<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="en">
<head>

	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<meta name="robots" content="<?php echo $robot_meta_tag ?>">

	<link rel="SHORTCUT ICON" href="favicon.ico">
<!--
	<link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet">
-->

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">


	<link rel="stylesheet" href="./assets/css/tailwind.output.css">

	<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"></script>
  	<script src="./assets/js/fontawesome-all.min.js"></script>
	<script src="./assets/js/init-alpine.js"></script>

<?php if ($_GET['mode'] == 'teamspeak'){ ?>
	<!-- Used by TeamSpeak Channel viewer -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/themes/light.css" integrity="sha384-VNjXcWTFgkXi1VXtuRlXBLNB44AvISAfti9WbOPCBCiNPnUpJ6dHx1Y3XmTii9pB" crossorigin="anonymous">
	<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/tree/tree.js" integrity="sha384-JIwkkGwePk3SK+b3aarqNrVOfYyJoOtV7HbRRvguVCMVI3siUbex6bjwJZM7ArWo" crossorigin="anonymous"></script>
	<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/tree-item/tree-item.js" integrity="sha384-2IgN/uS/tHYfnw9Jy+Dmd8hizrIRFj0jDx1LrQ+ulriotjQBAWrC9NHxTcFR3CO+" crossorigin="anonymous"></script>
<?php } ?>

	<!-- JQuery used for server list accordion --> 
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

	<link rel="stylesheet" type="text/css" href="./assets/css/windmill.css">
	<link rel="stylesheet" type="text/css" href="./styles/<?php echo $windmill_style; ?>">

	<title><?php
	echo $g_options['sitename']; 
	foreach ($title as $t)
	{
		echo " - $t";
	}
?></title>
</head>
<body> 
<!-- start Windmill Header -->
<!-- this div closes in the footer -->
<div
      class="flex h-screen bg-gray-50 dark:bg-gray-900"
      :class="{ 'overflow-hidden': isSideMenuOpen}">
      <!-- Desktop sidebar -->
      <aside
        class="z-20 flex-shrink-0 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block">
        <div class="py-4 text-gray-500 dark:text-gray-400">
          <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="/"><?php echo $g_options['sitename']; ?></a>

<?php		  

echo "		<ul class=\"mt-6\">\r\n";

// Are we viewing the content page?
if ($game != '') { 

	// Display Server related links
	display_menu_item("Servers", "?game=$game", "server");

	if ($g_options['contact']){
		display_menu_item("Rules", $g_options['contact'],"gavel");
	}

	if ($g_options['nav_globalchat']==1) {
		display_menu_item("Chat", "?mode=chat&amp;game=$game","comments");
	}

	display_menu_item("Players", "?mode=players&amp;game=$game","user");

	display_menu_item("Clans", "?mode=clans&amp;game=$game","users");

	if ($g_options["countrydata"]==1) {
		display_menu_item("Countries", "?mode=countryclans&amp;game=$game&amp;sort=nummembers","globe");
	}

	display_menu_item("Awards", "?mode=awards&amp;game=$game","trophy");

	// look for actions
	$db->query("SELECT game FROM hlstats_Actions WHERE game='" . valid_request($game, false) . "' LIMIT 1");
	if ($db->num_rows()>0) {

		display_menu_item("Actions", "?mode=actions&amp;game=$game","bolt");

	}

	display_menu_item("Weapons", "?mode=weapons&amp;game=$game","crosshairs");

	display_menu_item("Maps", "?mode=maps&amp;game=$game","route");

	$result = $db->query("SELECT game from hlstats_Roles WHERE game='" . valid_request($game, false) . "' AND hidden = '0'");
	$numitems = $db->num_rows($result);
	if ($numitems > 0) {

		display_menu_item("Roles", "?mode=roles&amp;game=$game","user-tag");
	}

	if ($g_options['nav_cheaters'] == 1) {

		display_menu_item("Bans", "?mode=bans&amp;game=$game","ban");
		
	} 

// Always display log in/log out
if (isset($_SESSION['loggedin'])) {

	display_menu_item("Admin Panel", $g_options['scripturl'] . "?mode=admin","cog");

	display_menu_item("Logout", "hlstats.php?logout=1","sign-out-alt");

} else {

	display_menu_item("Admin Login", $g_options['scripturl'] . "?mode=admin","sign-in-alt");

}


} else {

	// display_menu_item("Games", $g_options['scripturl'],"caret-down");

}

/* Removing the Games side menu for now */ 

// display_menu_item("Games", $g_options['scripturl'],"caret-down");
// $linkFormat= 'sidemenu'; include PAGE_PATH .'/gameslist.php';

echo "		</ul>\r\n";


?>
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

	display_menu_item("Servers", "?game=$game","server");
	
	if ($g_options['nav_globalchat']==1) {
		display_menu_item("Chat", "?mode=chat&amp;game=$game","comments");
	}

	display_menu_item("Players", "?mode=players&amp;game=$game","user");

	display_menu_item("Clans", "?mode=clans&amp;game=$game","users");

	if ($g_options["countrydata"]==1) {
		display_menu_item("Countries", "?mode=countryclans&amp;game=$game&amp;sort=nummembers","globe");
	}

	display_menu_item("Awards", "?mode=awards&amp;game=$game","trophy");

	// look for actions
	$db->query("SELECT game FROM hlstats_Actions WHERE game='".valid_request($game, false)."' LIMIT 1");
	if ($db->num_rows()>0) {

		display_menu_item("Actions", "?mode=actions&amp;game=$game","bolt");

	}

	display_menu_item("Weapons", "?mode=weapons&amp;game=$game","crosshairs");

	display_menu_item("Maps", "?mode=maps&amp;game=$game","route");

	$result = $db->query("SELECT game from hlstats_Roles WHERE game='".valid_request($game, false)."' AND hidden = '0'");
	$numitems = $db->num_rows($result);
	if ($numitems > 0) {

		display_menu_item("Roles", "?mode=roles&amp;game=$game","user-tag");
	}

	if ($g_options['nav_cheaters'] == 1) {

		display_menu_item("Bans", "?mode=bans&amp;game=$game","ban");
		
	} 

	if (isset($_SESSION['loggedin'])) {

		display_menu_item("Admin Panel", $g_options['scripturl'] . "?mode=admin","cog");

		display_menu_item("Logout", "hlstats.php?logout=1","sign-out-alt");

	} else {

		display_menu_item("Admin Login", $g_options['scripturl'] . "?mode=admin","sign-in-alt");

	}

	echo "		</ul>\r\n";

} ?>
		</div>
      </aside>
      <!-- this div closes in the footer -->
	  <div class="flex flex-col flex-1 w-full">
        <header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">
          <div
            class="container flex items-center justify-between h-full px-6 mx-auto text-gray-800 dark:text-gray-200"
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
            <!-- Start Game Title -->
            <div class="flex justify-center flex-1 lg:mr-32">
<?php

$db->query("
SELECT
	hlstats_Games.name
FROM
	hlstats_Games
WHERE
	hlstats_Games.code = '". valid_request($game, false) ."'
");

if ($db->num_rows() < 1) {
	$gamename = "All";
} else {
	list($gamename) = $db->fetch_row();
	echo "				<div class=\"hidden md:block\">\n";
	echo "					<a href=\"./hlstats.php?game=$game\">Viewing: " . $gamename . "</a>\n";
	echo "				</div>\n";
}

?>
            </div>
			<!-- End Game Title -->

			<!-- start header drop downs -->
            <ul class="flex items-center flex-shrink-0 space-x-6">
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
		
		// Display the games list
		if ($num_games > 1) {
		
		// Only display icons if set in admin 
		if ($g_options['display_gamelist'] == 1) {

//		echo "<ul>";
		$linkFormat= 'iconsonly'; include PAGE_PATH .'/gameslist.php';
//		echo "</ul>";

		// otherwise display full drop down
		} else {
?>
              <!-- Start Games menu -->
              <li class="relative">
                <button
                  class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none"
                  @click="toggleProfileMenu"
                  @keydown.escape="closeProfileMenu"
                  aria-label="Account"
                  aria-haspopup="true"
                >
                  Games
                </button>
                <template x-if="isProfileMenuOpen">
                  <ul
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click.away="closeProfileMenu"
                    @keydown.escape="closeProfileMenu"
                    class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
                    aria-label="submenu"
                  >
<?php $linkFormat= 'dropdown'; include PAGE_PATH .'/gameslist.php'; ?>
			</ul>
			</template>
			</li>
<?php	
	}	
	}
?>
            <!-- End Games menu -->

			  <!-- Start Links menu -->
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
<?php

						if ($g_options['contact']){
							display_links("Rules", $g_options['contact'],"gavel");
						}
						display_links("Help", $g_options['scripturl'] . "?mode=help","question-circle");
						display_links("Search", $g_options['scripturl'] . "?mode=search","search");
						if ($g_options['sourcebans_address']) {
							display_links("SourceBans", $g_options['sourcebans_address'],"ban");
						}
						if ($g_options['forum_address']) {
							display_links("Forum",$g_options['forum_address'],"comments");
						}
						if (isset($_SESSION['loggedin'])) {
							display_links("Admin Panel", $g_options['scripturl'] . "?mode=admin","cog");
							display_links("Admin Logout", "hlstats.php?logout=1","sign-out-alt");
						} else {
							display_links("Admin Login", $g_options['scripturl'] . "?mode=admin","sign-in-alt");
						}
?> 
                  </ul>
                </template>
              </li>
			  <!-- end Links menu -->

              <!-- Start Theme toggler -->
              <li class="flex">
                <button class="rounded-md focus:outline-none focus:shadow-outline-purple" @click="toggleTheme" aria-label="Toggle color mode">
                  <template x-if="!dark">
                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                  </template>
                  <template x-if="dark">
                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        fill-rule="evenodd"
                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                  </template>
                </button>
              </li>
              <!-- end Theme toggler -->

			</ul>
			<!-- end header drop downs -->
          </div>
        </header>

<!-- start body divs -->
<!-- these get closed in footer.php -->
		<main class="h-full pb-8 overflow-y-auto">
		<div class="container grid px-6 mx-auto">

<?php
	global $mode;
	if ($g_options['bannerdisplay'] != 0 && ($mode == 'contents' || $g_options['bannerdisplay']==1)) {
?>    
	<div class="mt-8 flex justify-center items-center">
		<img src="<?php echo ((strncmp($g_options['bannerfile'], 'http:/', 6) == 0)?$g_options['bannerfile']:IMAGE_PATH.'/'.$g_options['bannerfile']); ?>" alt="Banner">
	</div>
<?php
	}
?>       

<!-- end header.php -->