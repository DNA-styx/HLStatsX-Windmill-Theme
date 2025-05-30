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
	$db->query
	("
		SELECT
			hlstats_Games.name
		FROM
			hlstats_Games
		WHERE
			hlstats_Games.code = '" . valid_request($game, false) . "'
	");

    if ($db->num_rows() < 1) {
        error("No such game '$game'.");
	}

    list($gamename) = $db->fetch_row();

    $db->free_result();

	if (isset($_GET['minkills'])) {
		$minkills = valid_request(intval($_GET['minkills']),true);
	} else {
		$minkills = 0;
	}

	pageHeader
	(
		array ($gamename, 'Cheaters &amp; Banned Players'),
		array ($gamename=>"%s?game=$game", 'Cheaters &amp; Banned Players'=>'')
	);

	$table = new Table
	(
		array(
			new TableColumn
			(
				'lastName',
				'Player',
				'width=26&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
			),
			new TableColumn
			(
				'ban_date',
				'Ban Date',
				'width=15&align=right'
			),
			new TableColumn
			(
				'skill',
				'Points',
				'width=6&align=right'
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
				'width=5&align=right'
			),
			new TableColumn
			(
				'deaths',
				'Deaths',
				'width=5&align=right'
			),
			new TableColumn
			(
				'headshots',
				'Headshots',
				'width=7&align=right'
			),
			new TableColumn
			(
				'kpd',
				'K:D',
				'width=10&align=right'
			),
			new TableColumn
			(
				'hpk',
				'HS:K',
				'width=5&align=right'
			),
			new TableColumn
			(
				'acc',
				'Accuracy',
				'width=6&align=right&append=' . urlencode('%')
			)
		),
		'playerId',
		'last_event',
		'skill',
		true
	);

	$day_interval = 28;

	$resultCount = $db->query
	("
		SELECT
			COUNT(*)
		FROM
			hlstats_Players
		WHERE
			hlstats_Players.game = '" . valid_request($game, false) . "'
			AND hlstats_Players.hideranking = 2
			AND hlstats_Players.kills >= $minkills
	");

	list($numitems) = $db->fetch_row($resultCount);

	$result = $db->query
	("
		SELECT
			hlstats_Players.playerId,
			FROM_UNIXTIME(last_event,'%Y.%m.%d %T') as ban_date,
			hlstats_Players.flag,
                        unhex(replace(hex(hlstats_Players.lastName), 'E280AE', '')) as lastName,
			hlstats_Players.skill,
			hlstats_Players.kills,
			hlstats_Players.deaths,
			IFNULL(ROUND(hlstats_Players.kills / IF(hlstats_Players.deaths = 0, 1, hlstats_Players.deaths), 2), '-') AS kpd,
			hlstats_Players.headshots,
			IFNULL(ROUND(hlstats_Players.headshots / hlstats_Players.kills, 2), '-') AS hpk,
			IFNULL(ROUND((hlstats_Players.hits / hlstats_Players.shots * 100), 0), 0) AS acc,
			activity
		FROM
			hlstats_Players
		WHERE
			hlstats_Players.game = '" . valid_request($game, false) . "'
			AND hlstats_Players.hideranking = 2
			AND hlstats_Players.kills >= $minkills
		ORDER BY
			$table->sort $table->sortorder,
			$table->sort2 $table->sortorder,
			hlstats_Players.lastName ASC
		LIMIT
			$table->startitem,
			$table->numperpage
	");
?>

<!-- start bans.php -->
            
<?php display_page_title('Cheaters &amp; Banned Players (' . $numitems . ')'); ?>

<div class="flex items-center justify-between p-4 mb-8 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800">

	<div class="flex items-center">
		<form method="get" action="<?php echo $g_options['scripturl']; ?>">
			<input type="hidden" name="mode" value="search">
			<input type="hidden" name="game" value="<?php echo $game; ?>">
			<input type="hidden" name="st" value="player">
			<span class="font-semibold text-center text-gray-700 dark:text-gray-400">Find a player: </span>
			<input type="text" name="q" size="20" maxlength="64" class="mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
			<input type="submit" value="Search" class="windmill-button px-4 py-2 mb-2 text-sm font-medium leading-5 text-center border border-transparent rounded-lg btn">
		</form>
	</div>
</div>

<?php $table->draw($result, $numitems, 95); ?>

<!-- end bans.php -->