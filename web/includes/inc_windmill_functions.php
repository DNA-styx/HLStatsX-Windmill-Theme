<?php

// Issue 9 fix: added global $g_options to all functions that reference it
// Issue 15 fix: normalised Echo -> echo throughout
// Issue 16 fix: windmill_button_class() changed from echo to return

function display_menu_item($name, $link, $icon) {
	global $g_options;

	// Only prepend scripturl for relative links
	$href = (strncmp($link, 'http', 4) === 0 || strncmp($link, '//', 2) === 0)
		? $link
		: $g_options['scripturl'] . $link;

	echo "    <li class=\"relative px-6 py-3\">\r\n";
	echo "        <a\r\n";
	echo "        class=\"inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200\"\r\n";
	echo "        href=\"" . $href . "\">\r\n";
	echo "            <span class=\"ml-4\"><i class=\"fas fa-" . $icon . "\"></i>&nbsp;" . $name . "</span>\r\n";
	echo "        </a>\r\n";
	echo "    </li>\r\n";
}

function display_menu_item_games($name, $link, $image) {
	global $g_options;

	// Only prepend scripturl for relative links
	$href = (strncmp($link, 'http', 4) === 0 || strncmp($link, '//', 2) === 0)
		? $link
		: $g_options['scripturl'] . $link;

	echo "    <li class=\"relative px-6 py-3\">\r\n";
	echo "        <a\r\n";
	echo "        class=\"inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200\"\r\n";
	echo "        href=\"" . $href . "\">\r\n";
	echo "            <img src=\"" . $image . "\">&nbsp;" . $name . "\r\n";
	echo "        </a>\r\n";
	echo "    </li>\r\n";
}

function display_links($name, $link, $icon) {

	echo "                      <li class=\"flex\">\r\n";
	echo "                          <a\r\n";
	echo "                              class=\"inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200\"\r\n";
	echo "                              href=\"" . $link . "\">\r\n";
	echo "                                  <span><i class=\"fas fa-" . $icon . "\"></i>&nbsp;" . $name . "</span>\r\n";
	echo "                          </a>\r\n";
	echo "                      </li>\r\n";
}

function display_page_title($title) {

	echo "              <div\r\n";
	echo "                class=\"windmill-title-bar flex items-center justify-between p-4 mt-8 mb-8 text-l font-semibold rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple\"\r\n";
	echo "                >\r\n";
	echo "                  <span>" . $title . "</span>\r\n";
	if (defined('PAGE') && PAGE == 'INGAME') {
		$ingame_dark = isset($_COOKIE['ingame_dark']) ? $_COOKIE['ingame_dark'] : '1';
		$icon = ($ingame_dark == '1') ? 'sun' : 'moon';
		echo "                  <button onclick=\"toggleIngameTheme()\" aria-label=\"Toggle color mode\" style=\"background:none;border:none;cursor:pointer;color:inherit;\"><i id=\"ingameThemeIcon\" class=\"fas fa-$icon\"></i></button>\r\n";
	}
	echo "              </div>\r\n";
}

// Issue 13 fix: display_page_subtitle() already echoes internally.
// All callers should use it without wrapping in echo.
function display_page_subtitle($title) {

	echo "<h4 class=\"mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300\">" . $title . "</h4>\r\n";
}

// Admin Tools Pages

function display_admin_page_subtitle_collapsed($title) {

	echo "<div class=\"ml-6\">";
	echo "		<span class=\"mb-4 font-semibold text-gray-600 dark:text-gray-300\"><i class=\"fas fa-angle-right\"></i>&nbsp;" . $title . "</span>\r\n";
	echo "</div>";
}

function display_admin_page_subtitle_expanded($title) {

	echo "<div class=\"ml-6 mb-6\">\r\n";
	echo "	<span class=\"mb-4 font-semibold text-gray-600 dark:text-gray-300\">\r\n";
	echo "		<i class=\"fas fa-angle-down\"></i>&nbsp;" . $title . "\r\n";
	echo "	</span>\r\n";
	echo "</div>\r\n";
}

function display_admin_page_subtitle_second_level_expanded($title) {

	echo "<div class=\"ml-6 mb-6\">\r\n";
	echo "	<div class=\"ml-6\">\r\n";
	echo "	<span class=\"mb-4 font-semibold text-gray-600 dark:text-gray-300\">\r\n";
	echo "		<i class=\"fas fa-angle-down\"></i>&nbsp;" . $title . "\r\n";
	echo "	</span>\r\n";
	echo "	</div>\r\n";
	echo "</div>\r\n";
}

// Admin Games Pages

function display_admin_page_game_subtitle_collapsed($game_name, $game_code) {

	echo "<div class=\"ml-6 mb-6\">";
	echo "	<div class=\"ml-6\">\r\n";
	echo "	<span class=\"mb-4 font-semibold text-gray-600 dark:text-gray-300\">";
	echo "		<i class=\"fas fa-angle-right\"></i>&nbsp;" . $game_name . " (" . $game_code . ")";
	echo "	</span>\r\n";
	echo "	</div>\r\n";
	echo "</div>";
}

function display_admin_page_game_subtitle_expanded($game_name, $game_code) {

	echo "<span class=\"mb-4 font-semibold text-gray-600 dark:text-gray-300\"><i class=\"fas fa-angle-down\"></i>&nbsp;" . $game_name . " (" . $game_code . ")</span>\r\n";
}

// Issue 16 fix: changed from echo to return so callers can use the value in string context
function windmill_button_class() {

	return "windmill-button block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none";
}

function windmill_class_text() {

	return "windmill-text block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input";
}

function windmill_class_dropdown() {

	return "windmill-dropdown block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray";
}

function windmill_class_checkbox() {

	return "windmill-checkbox text-purple-600 form-checkbox focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray";
}

function windmill_class_textarea() {

	return "windmill-textarea block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray";
}

function display_table_filter($page) {
	global $g_options;

	switch ($page) {
		case "clans":
			$form_query    = "minmembers";
			$form_quantity = valid_request(intval($_GET['minmembers']), true);
			$form_text1    = "clans";
			$form_text2    = "members";
			break;
		case "countryclans":
			$form_query    = "minmembers";
			$form_quantity = valid_request(intval($_GET['minmembers']), true);
			$form_text1    = "countries";
			$form_text2    = "players";
			break;
		case "bans":
			$form_query    = "minkills";
			$form_quantity = valid_request(intval($_GET['minkills']), true);
			$form_text1    = "banned players";
			$form_text2    = "kills";
			break;
		case "players":
			$form_query    = "minkills";
			$form_quantity = valid_request(intval($_GET['minkills']), true);
			$form_text1    = "players";
			$form_text2    = "kills";
			break;
		/* Otherwise return nothing */
		default:
			echo "&nbsp;";
			return;
	}

	echo "		<form method=\"get\" action=\"" . $g_options['scripturl'] . "\">\n";
	foreach ($_GET as $k => $v) {
		$v = valid_request($v, false);

		if ($k != $form_quantity) {
			echo "		<input type=\"hidden\" name=\"" . htmlspecialchars($k) . "\" value=\"" . htmlspecialchars($v) . "\">\n";
		}
	}
	echo "		&nbsp;&nbsp;&nbsp;&nbsp;Show " . $form_text1 . " with ";
	echo "<input type=\"text\" name=\"" . $form_query . "\" size=\"4\" maxlength=\"2\" value=\"" . $form_quantity . "\" class=\"textbox\"> or more " . $form_text2 . "\n";
	echo "		<input type=\"submit\" value=\"Apply\" class=\"windmill-button px-2 text-xs text-center border border-transparent rounded-lg\">\n";
	echo "		</form>\n";
}

/**
 * getWindmillSortArrow()
 *
 * @param mixed $sort
 * @param mixed $sortorder
 * @param mixed $name
 * @param mixed $longname
 * @param string $var_sort
 * @param string $var_sortorder
 * @param string $sorthash
 * @return string Returns the code for a sort arrow <IMG> tag.
 */
function getWindmillSortArrow($sort, $sortorder, $name, $longname, $var_sort = 'sort', $var_sortorder = 'sortorder', $sorthash = '', $ajax = false)
{
	global $g_options;

	if ($sortorder == 'asc') {
		$sortimg       = 'fas fa-sort-amount-down';
		$othersortorder = 'desc';
	} else {
		$sortimg       = 'fas fa-sort-amount-up';
		$othersortorder = 'asc';
	}

	$arrowstring = '<a href="' . $g_options['scripturl'] . '?' . makeQueryString($var_sort, $name, array($var_sortorder));

	if ($sort == $name) {
		$arrowstring .= "&amp;$var_sortorder=$othersortorder";
		$jsarrow = "'" . $var_sortorder . "': '" . $othersortorder . "'";
	} else {
		$arrowstring .= "&amp;$var_sortorder=$sortorder";
		$jsarrow = "'" . $var_sortorder . "': '" . $sortorder . "'";
	}

	if ($sorthash) {
		$arrowstring .= "#$sorthash";
	}

	$arrowstring .= '" class="head"';

	if ($ajax) {
		$arrowstring .= " onclick=\"Tabs.refreshTab({'$var_sort': '$name', $jsarrow}); return false;\"";
	}

	$arrowstring .= ' title="Change sorting order">' . "$longname</a>";

	if ($sort == $name) {
		$arrowstring .= '&nbsp;<i class="' . $sortimg . '"></i>&nbsp;';
	}

	// Disable table sorting
	$arrowstring = $longname;

	return $arrowstring;
}

/**
 * getWindmillSelect()
 * Returns the HTML for a SELECT box, generated using the 'values' array.
 * Each key in the array should be a OPTION VALUE, while each value in the
 * array should be a corresponding descriptive name for the OPTION.
 *
 * @param mixed $name
 * @param mixed $values
 * @param string $currentvalue
 * @return string The 'currentvalue' will be given the SELECTED attribute.
 */
function getWindmillSelect($name, $values, $currentvalue = '')
{
	$select = "<select name=\"$name\" class=\"mt-1 w-full text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray\">\n";

	$gotcval = false;

	foreach ($values as $k => $v) {
		$select .= "\t<option value=\"$k\"";

		if ($k == $currentvalue) {
			$select .= ' selected="selected"';
			$gotcval = true;
		}

		$select .= ">$v</option>\n";
	}

	if ($currentvalue && !$gotcval) {
		$select .= "\t<option value=\"$currentvalue\" selected=\"selected\">$currentvalue</option>\n";
	}

	$select .= '</select>';

	return $select;
}

function display_ingame_menu() {
	global $g_options, $game;

	// Hide Menu if using these pages elsewhere
	if (!isset($_GET['hide'])) {

		if (isset($_GET['player'])) {
			$player = valid_request(intval($_GET['player']), true);
		}

		if (isset($_GET['game'])) {
			$game = valid_request(strval($_GET['game']), false);
		}

		echo "<div class=\"flex items-center justify-between p-4 mb-8 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800 text-gray-600 dark:text-gray-400\">\n";
		echo "    <div class=\"flex items-center\">\n";
		echo "        <span>\n";

		if (!empty($game) && isset($_GET['player']) && !empty($_GET['player'])) {
			echo "			<b>Your Stats: </b>\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=statsme&player=" . $player . "\">StatsMe</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=maps&player=" . $player . "\">Maps</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=kills&player=" . $player . "\">Kills</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=accuracy&player=" . $player . "\">Accuracy</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=weapons&player=" . $player . "\">Weapons</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=targets&player=" . $player . "\">Targets</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=actions&player=" . $player . "\">Actions</a>\n";
		}
		echo "        </span>\n";
		echo "    </div>\n";

		echo "    <div class=\"flex items-center\">\n";
		echo "		<span align=\"right\">\n";
		if (!empty($game)) {
			echo "			<b>Server Stats:</b>\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=servers\">Servers</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=players\">Players</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=weapons\">Weapons</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=maps\">Maps</a> |\n";
			echo "			<a href=\"ingame.php?game=" . $game . "&mode=actions\">Actions</a>\n";
		}
		echo "		</span>\n";
		echo "    </div>\n";
		echo "</div>\n";
	}
}

function display_amcharts_map($mapgame, $maptype) {

	switch ($maptype) {
		case "top":
			$title = "Top 50 Players";
			break;
		case "active":
			$title = "Active Players";
			break;
		case "recent":
			$title = "Recent Players";
			break;
		case "random":
		default:
			$title = "Players";
			break;
	}

	echo "<div class=\"hidden md:block\">\r\n";
	display_page_subtitle($title);
	echo "	<div class=\"mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800\">\r\n";
	echo "		<iframe src=\"" . INCLUDE_PATH . "/amcharts/display_map.php?mapgame=" . $mapgame . "&maptype=" . $maptype . "\" height=\"400\" width=\"100%\" allowtransparency=\"true\" style=\"background:transparent;\"></iframe>\r\n";
	echo "	</div>\r\n";
	echo "</div>\r\n";
}

function display_killsperdeath_icon($player_kpd) {

	if ($player_kpd > 2) {
		return "&nbsp;<span class=\"text-green-600\"><i class=\"fas fa-arrow-up\"></i></span>&nbsp;";
	} else if ($player_kpd < 1) {
		return "&nbsp;<span class=\"text-red-600\"><i class=\"fas fa-arrow-down\"></i></span>&nbsp;&nbsp;";
	} else {
		return "&nbsp;<i class=\"fas fa-circle\"></i>&nbsp;";
	}
}

/* https://stackoverflow.com/questions/27613432/file-get-contents-not-working#:~:text=https%3A//stackoverflow.com/a/59751342 */
function url_get_contents($Url) {
	if (!function_exists('curl_init')) {
		die('CURL is not installed!');
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $Url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	return $output;
}