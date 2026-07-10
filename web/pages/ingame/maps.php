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
	
	$player = valid_request(intval($_GET['player']), true);
	$uniqueid  = valid_request(strval($_GET['uniqueid']), false);
	$game = valid_request(strval($_GET['game']), false);
	
	if (!$player && $uniqueid) {
		if (!$game) {
			header('Location: ' . $g_options['scripturl'] . "&mode=search&st=uniqueid&q=$uniqueid");
			exit;
		}
		
		$db->query("
			SELECT
				playerId
			FROM
				hlstats_PlayerUniqueIds
			WHERE
				uniqueId='$uniqueid'
				AND game='$game'
		");
		
		if ($db->num_rows() > 1) {
			header('Location: ' . $g_options['scripturl'] . "&mode=search&st=uniqueid&q=$uniqueid&game=$game");
			exit;
		} elseif ($db->num_rows() < 1) {
			error("No players found matching uniqueId '$uniqueid'");
		} else {
			list($player) = $db->fetch_row();
			$player = intval($player);
		}
	}

	if ($player) {

		// Per-player map statistics

		$db->query("
			SELECT
				hlstats_Players.playerId,
				hlstats_Players.lastName,
				hlstats_Players.game
			FROM
				hlstats_Players
			WHERE
				playerId='$player'
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
		$db->query("SELECT name FROM hlstats_Games WHERE code='$game'");

		if ($db->num_rows() != 1) {
			$gamename = ucfirst($game);
		} else {
			list($gamename) = $db->fetch_row();
		}

		$tblMaps = new Table(
			array(
				new TableColumn(
					'map',
					'Map Name',
					'width=18&align=left&link=' . urlencode("mode=mapinfo&map=%k&game=$game&player=$player")
				),
				new TableColumn(
					'kills',
					'Kills',
					'width=7&align=right'
				),
				new TableColumn(
					'kpercent',
					'Perc. Kills',
					'width=10&sort=no&type=bargraph'
				),
				new TableColumn(
					'kpercent',
					'%',
					'width=6&sort=no&align=right&append=' . urlencode('%')
				),
				new TableColumn(
					'deaths',
					'Deaths',
					'width=7&align=right'
				),
				new TableColumn(
					'kpd',
					'Kpd',
					'width=13&align=right'
				),
				new TableColumn(
					'headshots',
					'Headshots',
					'width=10&align=right'
				),
				new TableColumn(
					'hpercent',
					'Perc. Headshots',
					'width=12&sort=no&type=bargraph'
				),
				new TableColumn(
					'hpercent',
					'%',
					'width=6&sort=no&align=right&append=' . urlencode('%')
				),
				new TableColumn(
					'hpk',
					'Hpk',
					'width=6&align=right'
				)
				
			),
			'map',
			'kpd',
			'kills',
			true,
			9999,
			'maps_page',
			'maps_sort',
			'maps_sortorder',
			'maps'
		);
	    
		$db->query("
			SELECT
				COUNT(*)
			FROM
				hlstats_Events_Frags
			LEFT JOIN hlstats_Servers ON
				hlstats_Servers.serverId=hlstats_Events_Frags.serverId
			WHERE
				hlstats_Servers.game='$game' AND killerId='$player'
		");
		list($realkills) = $db->fetch_row();

		$db->query("
			SELECT
				COUNT(*)
			FROM
				hlstats_Events_Frags
			LEFT JOIN hlstats_Servers ON
				hlstats_Servers.serverId=hlstats_Events_Frags.serverId
			WHERE
				hlstats_Servers.game='$game' AND killerId='$player'
				AND headshot=1      
		");
		list($realheadshots) = $db->fetch_row();

		$result = $db->query("
			SELECT
				IF(map='', '(Unaccounted)', map) AS map,
				SUM(killerId=$player) AS kills,
				SUM(victimId=$player) AS deaths,
				IFNULL(SUM(killerId=$player) / SUM(victimId=$player), '-') AS kpd,
				ROUND(CONCAT(SUM(killerId=$player)) / $realkills * 100, 2) AS kpercent,
				SUM(killerID=$player AND headshot=1) as headshots,
				IFNULL(SUM(killerID=$player AND headshot=1) / SUM(killerId=$player), '-') AS hpk,
				ROUND(CONCAT(SUM(killerId=$player AND headshot=1)) / $realheadshots * 100, 2) AS hpercent
			FROM
				hlstats_Events_Frags
			LEFT JOIN hlstats_Servers ON
				hlstats_Servers.serverId=hlstats_Events_Frags.serverId
			WHERE
				hlstats_Servers.game='$game' AND killerId='$player'
				OR victimId='$player'
			GROUP BY
				map
			ORDER BY
				$tblMaps->sort $tblMaps->sortorder,
				$tblMaps->sort2 $tblMaps->sortorder
		");

		display_page_title('Maps Played');
		display_ingame_menu();
		$tblMaps->draw($result, $db->num_rows($result), 100);

	} elseif ($game) {

		// Server-wide map statistics (no player context)

		$db->query("SELECT name FROM hlstats_Games WHERE code='$game'");
		if ($db->num_rows() != 1) {
			$gamename = ucfirst($game);
		} else {
			list($gamename) = $db->fetch_row();
		}

		$tblMaps = new Table(
			array(
				new TableColumn(
					'map',
					'Map Name',
					'width=20&align=left&link=' . urlencode("mode=mapinfo&map=%k&game=$game")
				),
				new TableColumn(
					'kills',
					'Kills',
					'width=8&align=right'
				),
				new TableColumn(
					'kpercent',
					'%',
					'width=7&sort=no&align=right&append=' . urlencode('%')
				),
				new TableColumn(
					'kpercent',
					'Ratio',
					'width=16&sort=no&type=bargraph'
				),
				new TableColumn(
					'headshots',
					'Headshots',
					'width=8&align=right'
				),
				new TableColumn(
					'hpercent',
					'%',
					'width=7&sort=no&align=right&append=' . urlencode('%')
				),
				new TableColumn(
					'hpercent',
					'Ratio',
					'width=16&sort=no&type=bargraph'
				),
				new TableColumn(
					'hpk',
					'HS:K',
					'width=9&align=right'
				),
				new TableColumn(
					'map',
					'HeatMap',
					'width=4&type=heatmap'
				)
			),
			'map',
			'kills',
			'map',
			true,
			9999,
			'maps_page',
			'maps_sort',
			'maps_sortorder',
			'maps'
		);

		$db->query("
		 	SELECT
				SUM(hlstats_Maps_Counts.kills),
				SUM(hlstats_Maps_Counts.headshots)
			FROM
				hlstats_Maps_Counts
			WHERE
				hlstats_Maps_Counts.game = '$game'
		");
		list($realkills, $realheadshots) = $db->fetch_row();

		$result = $db->query("
			SELECT
				IF(hlstats_Maps_Counts.map = '', '(Unaccounted)', hlstats_Maps_Counts.map) AS map,
				hlstats_Maps_Counts.kills,
				ROUND(kills / " . (($realkills==0)?1:$realkills) . " * 100, 2) AS kpercent,
				hlstats_Maps_Counts.headshots,
				ROUND(hlstats_Maps_Counts.headshots / IF(hlstats_Maps_Counts.kills = 0, 1, hlstats_Maps_Counts.kills), 2) AS hpk,
				ROUND(hlstats_Maps_Counts.headshots / " . (($realheadshots==0)?1:$realheadshots) . " * 100, 2) AS hpercent
			FROM
				hlstats_Maps_Counts
			WHERE
				hlstats_Maps_Counts.game = '$game'
			ORDER BY
				$tblMaps->sort $tblMaps->sortorder,
				$tblMaps->sort2 $tblMaps->sortorder
		");

		display_page_title('Map Statistics');
		display_ingame_menu();
		$tblMaps->draw($result, $db->num_rows($result), 100);

	} else {
		error('No player ID or game specified.');
	}
?>