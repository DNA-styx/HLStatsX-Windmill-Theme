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

    // Player Rankings
	$db->query("
		SELECT
			hlstats_Games.name
		FROM
			hlstats_Games
		WHERE
			hlstats_Games.code = '$game'
	");

	if ($db->num_rows() < 1) {
        error("No such game '$game'.");
	}

    list($gamename) = $db->fetch_row();

    $db->free_result();

    if (isset($_GET['minkills'])) {
		$minkills = valid_request($_GET['minkills'], true);
	} else {
		$minkills = 1;
	}

	pageHeader
	(
		array ($gamename, 'Player Rankings'),
		array ($gamename=>"%s?game=$game", 'Player Rankings'=>'')
	);

	$rank_type = 0;
	if (isset($_GET['rank_type'])) {
		$rank_type = valid_request(strval($_GET['rank_type']), false);
	}

// Autocomplete function below implemented by KingJ. Heavy modified to use HTML request instead of JSON.
?>
<!-- start players.php -->
<?php display_page_title('Players'); ?>

<div class="flex items-center justify-between p-4 mb-8 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800">

	<div class="flex items-center">
<?php 
/*
		<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/Observer.js"></script>
		<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/Autocompleter.js"></script>
		<script type="text/javascript" src="<?php echo INCLUDE_PATH; ?>/js/Autocompleter.Request.js"></script>
		<script type="text/javascript">
			document.addEvent('domready', function() {
				new Autocompleter.Request.HTML('playersearch', 'autocomplete.php?game=<?php echo $game; ?>', {
					'indicatorClass': 'autocompleter-loading'
				});
			});
		</script>
*/
?>
		<form method="get" action="<?php echo $g_options['scripturl']; ?>">
			<input type="hidden" name="mode" value="search">
			<input type="hidden" name="game" value="<?php echo $game; ?>">
			<input type="hidden" name="st" value="player">
			<span class="font-semibold text-center text-gray-700 dark:text-gray-400">Find a player: </span>
			<input type="text" name="q" size="20" maxlength="64" class="mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" id="playersearch">
			<input type="submit" value="Search" class="windmill-button px-4 py-2 mb-2 text-sm font-medium leading-5 text-center border border-transparent rounded-lg btn">
		</form>
	</div>
	<div class="flex items-center">
		<form method="get" action="<?php echo $g_options['scripturl']; ?>" style="margin:0px;padding:0px;">
			<input type="hidden" name="mode" value="players">
			<input type="hidden" name="game" value="<?php echo $game; ?>">
			<span class="font-semibold text-center text-gray-700 dark:text-gray-400">Ranking View: </span>
			<?php
				$result = $db->query
				("
					SELECT
						hlstats_Players_History.eventTime
					FROM
						hlstats_Players_History
					GROUP BY
						hlstats_Players_History.eventTime
					ORDER BY
						hlstats_Players_History.eventTime DESC
					LIMIT
						0,
						50
				");
				echo "	<select name=\"rank_type\" class=\"mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray\">\n";
				echo "					<option value=\"0\">Total Ranking</option>\n";
				echo "					<option value=\"-1\">Last Week</option>\n";
				echo "					<option value=\"-2\">Last Month</option>\n";
				$i = 1;
				$dates = array ();
				while ($rowdata = $db->fetch_array())
				{
					$dates[] = $rowdata; 
					if ($rank_type == $i) 
						echo "					<option value=\"".$i."\" selected>".$rowdata['eventTime']."</option>\n";
					else
						echo "					<option value=\"".$i."\">".$rowdata['eventTime']."</option>\n";
					$i++;
				}
				
				echo "				</select>\n";
			?>
			<input type="submit" value="View" class="windmill-button px-4 py-2 mb-2 text-sm font-medium leading-5 text-center border border-transparent rounded-lg btn">
		</form>
	</div>
</div>
<?php

if ($g_options["show_google_map"] == 1) {
	echo " <!-- start Demo Map -->\n";
		display_amcharts_map($game,"top");
	echo " <!-- End Top Players -->\n";
}
?>
<!-- Start Table -->
<?php
		if ($g_options['rankingtype']!='kills')
		{
			$table = new Table
			(
				array
				(
					new TableColumn
					(
						'lastName',
						'Player',
						'width=26&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
					),
                                        new TableColumn
                                        (
                                                'mmrank',
                                                'Rank',
                                                'width=4&type=elorank'
                                        ),
					new TableColumn
					(
						'skill',
						'Points',
						'width=7&align=right&skill_change=1'
					),
					new TableColumn
					(
						'activity',
						'Activity',
						'width=10&sort=no&type=bargraph'
					),
					new TableColumn
					(
						'connection_time',
						'Connection Time',
						'width=10&align=right&type=timestamp'
					),
					new TableColumn
					(
						'kills',
						'Kills',
						'width=7&align=right'
					),
					new TableColumn
					(
						'deaths',
						'Deaths',
						'width=7&align=right'
					),
					new TableColumn
					(
						'kpd',
						'K:D',
						'width=6&align=right'
					),
					new TableColumn
					(
						'headshots',
						'Headshots',
						'width=6&align=right'
					),
					new TableColumn
					(
						'hpk',
						'HS:K',
						'width=6&align=right'
					),
					new TableColumn
					(
						'acc',
						'Accuracy',
						'width=6&align=right&append=' . urlencode('%')
					)
				),
				'playerId',
				$g_options['rankingtype'],
				'kpd',
				true
			);
		}
		else
		{
			$table = new Table
			(
				array
				(
					new TableColumn
					(
						'lastName',
						'Player',
						'width=30&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
					),
					new TableColumn
					(
						'activity',
						'Activity',
						'width=10&sort=no&type=bargraph'
						),
					new TableColumn
					(
						'kills',
						'Kills',
						'width=7&align=right'
					),
					new TableColumn
					(
						'deaths',
						'Deaths',
						'width=7&align=right'
					),
					new TableColumn
					(
						'kpd',
						'K:D',
						'width=6&align=right'
					),
					new TableColumn
					(
						'headshots',
						'Headshots',
						'width=6&align=right'
					),
					new TableColumn
					(
						'hpk',
						'HS:K',
						'width=6&align=right'
					),
					new TableColumn
					(
						'acc',
						'Accuracy',
						'width=6&align=right&append=' . urlencode('%')
					),
					new TableColumn
					(
						'skill',
						'Points',
						'width=7&align=right&skill_change=1'
					),
					new TableColumn
					(
						'connection_time',
						'Connection Time',
						'width=10&align=right&type=timestamp'
					)
				),
			'playerId',
			$g_options['rankingtype'],
			'kpd',
			true
			);
		}
		if ($rank_type == "0")
		{
			$result = $db->query
			("
				SELECT
					SQL_CALC_FOUND_ROWS
					hlstats_Players.playerId,
					hlstats_Players.connection_time,
                                        unhex(replace(hex(hlstats_Players.lastName), 'E280AE', '')) as lastName,
					hlstats_Players.flag,
					hlstats_Players.country,
					hlstats_Players.skill,
					hlstats_Players.mmrank,
					hlstats_Players.kills,
					hlstats_Players.deaths,
					hlstats_Players.last_skill_change,
					ROUND(hlstats_Players.kills/(IF(hlstats_Players.deaths=0, 1, hlstats_Players.deaths)), 2) AS kpd,
					hlstats_Players.headshots,
					ROUND(hlstats_Players.headshots/(IF(hlstats_Players.kills=0, 1, hlstats_Players.kills)), 2) AS hpk,
					IFNULL(ROUND((hlstats_Players.hits / hlstats_Players.shots * 100), 1), 0) AS acc,
					activity
				FROM
					hlstats_Players
				WHERE
					hlstats_Players.game = '$game'
					AND hlstats_Players.hideranking = 0
					AND hlstats_Players.kills >= $minkills
				ORDER BY
					$table->sort $table->sortorder,
					$table->sort2 $table->sortorder,
					hlstats_Players.lastName ASC
				LIMIT
					$table->startitem,
					$table->numperpage
			");
			
			$resultCount = $db->query("SELECT FOUND_ROWS()");
			list($numitems) = $db->fetch_row($resultCount);
		}
		else
		{
			if ($rank_type == "-1")
			{
				$maxEvent = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
				$minEvent = $maxEvent - (86400 * 7);
			}
			if ($rank_type == "-2")
			{
				$maxEvent = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
				$minEvent = $maxEvent - (86400 * 30);
			}
			if (!isset($minEvent))
			{
				$minEvent = explode("-", $dates[$rank_type-1]['eventTime']);
				$minEvent = mktime(0, 0, 0, $minEvent[1], $minEvent[2], $minEvent[0]);
				$maxEvent = $minEvent + 86400;
			}
			$result = $db->query
			("
				SELECT
					SQL_CALC_FOUND_ROWS
					hlstats_Players_History.playerId,
					hlstats_Players.lastName,
					hlstats_Players.flag,
					hlstats_Players.country,
					hlstats_Players.mmrank,
					SUM(hlstats_Players_History.connection_time) AS connection_time,
					SUM(hlstats_Players_History.skill_change) AS skill,
					SUM(hlstats_Players_History.skill_change) AS skill_change,
					SUM(hlstats_Players_History.skill_change) AS last_skill_change,
					SUM(hlstats_Players_History.kills) AS kills,
					SUM(hlstats_Players_History.deaths) AS deaths,
					ROUND(SUM(hlstats_Players_History.kills) / IF(SUM(hlstats_Players_History.deaths) = 0, 1, SUM(hlstats_Players_History.deaths)), 2) AS kpd,
					SUM(hlstats_Players_History.headshots) AS headshots,
					ROUND(SUM(hlstats_Players_History.headshots) / SUM(hlstats_Players_History.kills), 2) AS hpk,
					IFNULL(ROUND((SUM(hlstats_Players_History.hits) / SUM(hlstats_Players_History.shots) * 100), 1), 0) AS acc,
					activity
				FROM
					hlstats_Players_History
				INNER JOIN
					hlstats_Players
				ON
					hlstats_Players_History.playerId = hlstats_Players.playerId
				WHERE
					hlstats_Players_History.game = '$game'
					AND hlstats_Players.hideranking = 0
					AND activity > 0
					AND UNIX_TIMESTAMP(hlstats_Players_History.eventTime) >= $minEvent
					AND UNIX_TIMESTAMP(hlstats_Players_History.eventTime) <= $maxEvent
				GROUP BY
					hlstats_Players_History.playerId
				HAVING
					SUM(hlstats_Players_History.kills) >= $minkills
				ORDER BY
					$table->sort $table->sortorder,
					$table->sort2 $table->sortorder,
					hlstats_Players.lastName ASC
				LIMIT
					$table->startitem,
					$table->numperpage
			");
			$resultCount = $db->query("SELECT FOUND_ROWS()");
			list($numitems) = $db->fetch_row($resultCount);
		}
		$table->draw($result, $numitems, 95);
	?>
<!-- end table -->
<!-- end players.php -->