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

	// Player Details
	$player = valid_request(intval($_GET['player'] ?? ''), true);
	$uniqueid = valid_request(strval($_GET['uniqueid'] ?? ''), false);
	$game = valid_request(strval($_GET['game'] ?? ''), false);

	if (!$player && $uniqueid) {
		if (!$game) {
			header("Location: " . $g_options['scripturl'] . "&mode=search&st=uniqueid&q=$uniqueid");
			exit;
		}

		$uniqueid = preg_replace('/^STEAM_\d+?\:/i','',$uniqueid);

        $db->query("
			SELECT
				hlstats_PlayerUniqueIds.playerId
			FROM
				hlstats_PlayerUniqueIds
			WHERE
				hlstats_PlayerUniqueIds.uniqueId = '$uniqueid'
		");

		if ($db->num_rows() > 1) {
			header("Location: " . $g_options['scripturl'] . "&mode=search&st=uniqueid&q=$uniqueid&game=$game");
			exit;
		} elseif ($db->num_rows() < 1) {
			error("No players found matching uniqueId '$uniqueid'");
		} else {
			list($player) = $db->fetch_row();
			$player = intval($player);
		}
	} elseif (!$player && !$uniqueid) {
		error("No player ID specified.");
	}

	$db->query("
		SELECT
			hlstats_Players.playerId,
			hlstats_Players.connection_time,
			unhex(replace(hex(hlstats_Players.lastName), 'E280AE', '')) as lastName,
			hlstats_Players.country,
			hlstats_Players.city,
			hlstats_Players.flag,
			hlstats_Players.clan,
			hlstats_Players.fullName,
			hlstats_Players.email,
			hlstats_Players.homepage,
			hlstats_Players.icq,
			hlstats_Players.mmrank,
			hlstats_Players.game,
			hlstats_Players.hideranking,
			hlstats_Players.blockavatar,
			hlstats_Players.skill,
			hlstats_Players.kills,
			hlstats_Players.deaths,
			IFNULL(kills / deaths, '-') AS kpd,
			hlstats_Players.suicides,
			hlstats_Players.headshots,
			IFNULL(headshots / kills, '-') AS hpk,
			hlstats_Players.shots,
			hlstats_Players.hits,
			hlstats_Players.teamkills,
			IFNULL(ROUND((hits / shots * 100), 1), 0) AS acc,
			CONCAT(hlstats_Clans.name) AS clan_name,
			activity
		FROM
			hlstats_Players
		LEFT JOIN
			hlstats_Clans
		ON
			hlstats_Clans.clanId = hlstats_Players.clan
		WHERE
			hlstats_Players.playerId = '$player'
		LIMIT
			1
	");

	if ($db->num_rows() != 1) {
		error("No such player '$player'.");
	}

	$playerdata = $db->fetch_array();
	$db->free_result();
	$pl_name = $playerdata['lastName'];

    if (strlen($pl_name) > 10) {
		$pl_shortname = substr($pl_name, 0, 8) . '...';
	} else {
		$pl_shortname = $pl_name;
	}

	$pl_name = htmlspecialchars($pl_name, ENT_COMPAT);
	$pl_shortname = htmlspecialchars($pl_shortname, ENT_COMPAT);
	$pl_urlname = urlencode($playerdata['lastName']);
	$game = $playerdata['game'];

    $db->query("
		SELECT
			hlstats_Games.name
		FROM
			hlstats_Games
		WHERE
			hlstats_Games.code = '$game'
	");

	if ($db->num_rows() != 1) {
		$gamename = ucfirst($game);
	} else {
		list($gamename) = $db->fetch_row();
	}

	$hideranking = $playerdata['hideranking'];

    if ($hideranking == 2) {
		$statusmsg = '<span style="color:red;font-weight:bold;">Banned</span>';
	} else {
		$statusmsg = '<span style="color:green;font-weight:bold;">In good standing</span>';
	}
// Required on a few pages, just decided to add it here
// May get moved in the future

$db->query("
		SELECT
			COUNT(hlstats_Events_Frags.killerId)
		FROM
			hlstats_Events_Frags
		WHERE
			hlstats_Events_Frags.killerId = '$player'
			AND hlstats_Events_Frags.headshot = 1
	");

	list($realheadshots) = $db->fetch_row();

    $db->query("
		SELECT
			COUNT(hlstats_Events_Frags.killerId)
		FROM
			hlstats_Events_Frags
		WHERE
			hlstats_Events_Frags.killerId = '$player'
	");

	list($realkills) = $db->fetch_row();

    $db->query("
		SELECT
			COUNT(hlstats_Events_Frags.victimId)
		FROM
			hlstats_Events_Frags
		WHERE
			hlstats_Events_Frags.victimId = '$player'
	");

	list($realdeaths) = $db->fetch_row();

    $db->query("
		SELECT
			COUNT(hlstats_Events_Teamkills.killerId)
		FROM
			hlstats_Events_Teamkills
		WHERE
			hlstats_Events_Teamkills.killerId = '$player'
	");

	list($realteamkills) = $db->fetch_row();

	if (!isset($_GET['killLimit'])) {
		$killLimit = 5;
	} else {
		$killLimit = valid_request($_GET['killLimit'], true);
	}

	if (isset($_GET['type']) && $_GET['type'] == 'ajax') {
		$tabs = explode('_', preg_replace('[^a-z]', '', $_GET['tab']));

		foreach ($tabs as $tab) {
			if (file_exists(PAGE_PATH . "/playerinfo_$tab.php")) {
				@include(PAGE_PATH . "/playerinfo_$tab.php");
			}
		}

		exit;
	}

	pageHeader
	(
		array ($gamename, 'Player Details', $pl_name),
		array
		(
			$gamename=>$g_options['scripturl'] . "?game=$game",
			'Player Rankings'=>$g_options['scripturl'] . "?mode=players&game=$game",
			'Player Details'=>""
		),
		$pl_name
	);
?>
<!-- start playerinfo.php -->
<?php 

display_page_title('Player Information for ' . htmlspecialchars($playerdata['lastName'], ENT_COMPAT));

if ($g_options['playerinfo_tabs'] == '1') { 
?>
<script>
	$(document).ready(function(){
		var cache = {};

		// Load default page
		$('#loading').fadeIn();
		$('#content').hide().load('<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_general_aliases&player=<?php echo $player ?>', function(response, status, xhr) {
			if (status == "success") {
				cache['<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_general_aliases&player=<?php echo $player ?>'] = response;
				}
			$('#loading').fadeOut(function() {
				$('#content').fadeIn();
			});
		});

		// Load specific page when button is clicked
		$('.load-button').click(function(){
			var page = $(this).data('page');

			if (cache[page]) {
				$('#loading').fadeOut();
				$('#content').html(cache[page]).fadeIn();
			} else {
				$('#content').hide();
				$('#loading').fadeIn();
				$('#content').load(page, function(response, status, xhr) {
					if (status == "success") {
						cache[page] = response;
					}
					$('#loading').fadeOut(function() {
						$('#content').fadeIn();
					});
				});
			}
		});
	});
</script>
<div class="flexbox content-center mb-6">
	<center>
		<button class="windmill-button load-button px-3 py-1 text-sm font-medium leading-5 transition-colors duration-150 border border-transparent rounded-md focus:outline-none" data-page="<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_general_aliases&player=<?php echo $player ?>">General</button>&nbsp;
		<button class="windmill-button load-button px-3 py-1 text-sm font-medium leading-5 transition-colors duration-150 border border-transparent rounded-md focus:outline-none" data-page="<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_playeractions_teams&player=<?php echo $player ?>">Player Actions & Teams</button>&nbsp; 
		<button class="windmill-button load-button px-3 py-1 text-sm font-medium leading-5 transition-colors duration-150 border border-transparent rounded-md focus:outline-none" data-page="<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_weapons&player=<?php echo $player ?>">Weapons</button>&nbsp; 
		<button class="windmill-button load-button px-3 py-1 text-sm font-medium leading-5 transition-colors duration-150 border border-transparent rounded-md focus:outline-none" data-page="<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_mapperformance_servers&player=<?php echo $player ?>">Map Performance & Servers</button>&nbsp;
		<button class="windmill-button load-button px-3 py-1 text-sm font-medium leading-5 transition-colors duration-150 border border-transparent rounded-md focus:outline-none" data-page="<?php echo $g_options['scripturl'] ?>?mode=playerinfo&type=ajax&game=<?php echo $game ?>&tab=tab_killstats&player=<?php echo $player ?>">Kill Stats</button>
	</center>
</div>
<div id="loading" class="mb-6">
	<center>
		<img src="hlstatsimg/ajax.gif">
	</center>
</div>
<div id="content" class="mb-6"></div>
<?php	
} else {
	// If tabs are disabled show everything on one page
	require_once PAGE_PATH.'/playerinfo_general.php';
	require_once PAGE_PATH.'/playerinfo_aliases.php';
	require_once PAGE_PATH.'/playerinfo_playeractions.php';
	require_once PAGE_PATH.'/playerinfo_teams.php';
	require_once PAGE_PATH.'/playerinfo_weapons.php';
	require_once PAGE_PATH.'/playerinfo_mapperformance.php';
	require_once PAGE_PATH.'/playerinfo_servers.php';
	require_once PAGE_PATH.'/playerinfo_killstats.php';
}

if (isset($_SESSION['loggedin'])) {
	echo "<div class=\"text-xs text-red-600 dark:text-red-400\">\n";
	echo '	Admin Options: <a href="'.$g_options['scripturl']."?mode=admin&amp;task=tools_editdetails_player&amp;id=$player\">Edit Player Details</a>\n";
	echo "</div>\n";
}
?>
<!-- end playerinfo.php -->