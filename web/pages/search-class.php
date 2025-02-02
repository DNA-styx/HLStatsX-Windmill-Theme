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

// Search Class
	class Search
	{
		var $query;
		var $type;
		var $game;
		var $uniqueid_string = 'Unique ID';
		var $uniqueid_string_plural = 'Unique IDs';

		function __construct($query, $type, $game)
		{
			global $g_options;

			$this->query = trim($query);
			$this->type = $type;
			$this->game = $game;

			if ($g_options['Mode'] == 'LAN')
			{
				$this->uniqueid_string = 'IP Address';
				$this->uniqueid_string_plural = 'IP Addresses';
			}
		}

		function drawForm ($getvars = array(), $searchtypes = -1)
		{
			global $g_options, $db;

			if (!is_array($searchtypes))
			{
				$searchtypes = array(
					'player' => 'Player Names',
					'uniqueid' => 'Player' . $this->uniqueid_string_plural
				);
				if ($g_options['Mode'] != 'LAN' && isset($_SESSION['loggedin']) && $_SESSION['acclevel'] >= 80) {
					$searchtypes['ip'] = 'Player IP Addresses';
				}
				$searchtypes['clan'] = 'Clan Names';
			}
?>

<?php 
	// Display short title on admin edit details page
	if ($_GET["mode"] == 'admin'){
		echo display_admin_page_subtitle_second_level_expanded('Find a Player or Clan'); 
	} else {
		echo display_page_title('Find a Player or Clan'); 
	}
?>

<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
        <div class="w-full">

<form method="get" action="<?php echo $g_options['scripturl']; ?>">
			<?php
				foreach ($getvars as $var=>$value)
				{
					echo '<input type="hidden" name="'.htmlspecialchars($var, ENT_QUOTES).'" value="'.htmlspecialchars($value, ENT_QUOTES)."\">\n";
				}
			?>
					<table class="w-full whitespace-no-wrap">
						<tr class="text-l font-semibold tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
							<td>Search For:&nbsp;</td>
							<td>
								<input type="text" name="q" size="20" maxlength="128" value="<?php echo htmlspecialchars($this->query, ENT_QUOTES); ?>" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
							</td>
						</tr>
						<tr class="text-l font-semibold tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
							<td>In:&nbsp;</td>
							<td>
								<?php
									echo getWindmillSelect('st', $searchtypes, $this->type);
								?>
							</td>
						</tr>
						<tr class="text-l font-semibold tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
							<td>Game:</td>
							<td>
								<?php
									$games = array ();
									$games[''] = '(All)';
									$result = $db->query("
										SELECT
											hlstats_Games.code,
											hlstats_Games.name
										FROM
											hlstats_Games
										WHERE
											hlstats_Games.hidden = '0'
										ORDER BY
											hlstats_Games.name
									");
									while ($rowdata = $db->fetch_row($result))
									{
										$games[$rowdata[0]] = $rowdata[1];
									}
									echo getWindmillSelect('game', $games, $this->game);
								?>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="submit" value=" Find Now " class="windmill-button block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none">
							</td> 
						</tr>
					</table>
		</form>

        </div>
    </div>
</div>

<?php
		}
		function drawResults ($link_player=-1, $link_clan=-1)
		{
			global $g_options, $db;
			if ($link_player == -1) $link_player = "mode=playerinfo&amp;player=%k";
			if ($link_clan == -1) $link_clan = "mode=claninfo&amp;clan=%k";
?>

<?php
			$sr_query = preg_replace('/^STEAM_\d+?\:/i','',$this->query);
			$sr_query = $db->escape($sr_query);
			$sr_query = preg_replace('/\s/', '%', $sr_query);
			if ($this->type == 'player')
			{
				$table = new Table
				(
					array
					(
						new TableColumn
						(
							'player_id',
							'ID',
							'width=5&align=right'
						),
						new TableColumn
						(
							'name',
							'Player',
							'width=65&flag=1&link=' . urlencode($link_player)
						),
						new TableColumn
						(
							'gamename',
							'Game',
							'width=30'
						)
					),
					'player_id',
					'name',
					'player_id',
					false,
					50,
					'page',
					'sort',
					'sortorder',
					'results',
					'asc'
				);
				if ($this->game)
					$andgame = "AND hlstats_Players.game='" . $this->game . "'";
				else
					$andgame = '';
				$result = $db->query
				("
					SELECT
						hlstats_PlayerNames.playerId AS player_id,
						hlstats_PlayerNames.name,
						hlstats_Players.flag,
						hlstats_Players.country,
						hlstats_Games.name AS gamename
					FROM
						hlstats_PlayerNames
					LEFT JOIN
						hlstats_Players
					ON
						hlstats_Players.playerId = hlstats_PlayerNames.playerId
					LEFT JOIN
						hlstats_Games
					ON
						hlstats_Games.code = hlstats_Players.game
					WHERE
						hlstats_Games.hidden = '0'
						AND hlstats_PlayerNames.name LIKE '%$sr_query%'
						$andgame
					ORDER BY
						$table->sort $table->sortorder,
						$table->sort2 $table->sortorder
					LIMIT
						$table->startitem,
						$table->numperpage
				");
				$resultCount = $db->query
				("
					SELECT
						COUNT(*)
					FROM
						hlstats_PlayerNames
					LEFT JOIN
						hlstats_Players
					ON
						hlstats_Players.playerId = hlstats_PlayerNames.playerId
					LEFT JOIN
						hlstats_Games
					ON
						hlstats_Games.code = hlstats_Players.game
					WHERE
						hlstats_Games.hidden = '0'
						AND hlstats_PlayerNames.name LIKE '%$sr_query%'
						$andgame
				");
				list($numitems) = $db->fetch_row($resultCount);
				echo display_page_subtitle('Player Search: ' . $numitems . ' Results');
				$table->draw($result, $numitems, 95);
			}
			elseif ($this->type == 'uniqueid')
			{
				$table = new Table
				(
					array
					(
						new TableColumn
						(
							'uniqueId',
							$this->uniqueid_string,
							'width=15'
						),
						new TableColumn
						(
							'lastName',
							'Player',
							'width=50&flag=1&link=' . urlencode($link_player)
						),
						new TableColumn
						(
							'gamename',
							'Game',
							'width=30'
						),
						new TableColumn
						(
							'playerId',
							'ID',
							'width=5&align=right'
						)
					),
					'playerId',
					'lastName',
					'uniqueId',
					false,
					50,
					'page',
					'sort',
					'sortorder',
					'results',
					'asc'
				);
				if ($this->game)
					$andgame = "AND hlstats_PlayerUniqueIds.game='" . $this->game . "'";
				else
					$andgame = '';
				$result = $db->query
				("
					SELECT
						hlstats_PlayerUniqueIds.uniqueId,
						hlstats_PlayerUniqueIds.playerId,
						hlstats_Players.lastName,
						hlstats_Players.flag,
						hlstats_Players.country,
						hlstats_Games.name AS gamename
					FROM
						hlstats_PlayerUniqueIds
					LEFT JOIN
						hlstats_Players
					ON
						hlstats_Players.playerId = hlstats_PlayerUniqueIds.playerId
					LEFT JOIN
						hlstats_Games
					ON
						hlstats_Games.code = hlstats_PlayerUniqueIds.game
					WHERE
						hlstats_Games.hidden = '0' AND
						hlstats_PlayerUniqueIds.uniqueId LIKE '%$sr_query%'
						$andgame
					ORDER BY
						$table->sort $table->sortorder,
						$table->sort2 $table->sortorder
					LIMIT
						$table->startitem,
						$table->numperpage
				");
				$resultCount = $db->query
				("
					SELECT
						COUNT(*)
					FROM
						hlstats_PlayerUniqueIds
					LEFT JOIN
						hlstats_Players
					ON
						hlstats_Players.playerId = hlstats_PlayerUniqueIds.playerId
					WHERE
						hlstats_PlayerUniqueIds.uniqueId LIKE '%$sr_query%'
						$andgame
				");
				list($numitems) = $db->fetch_row($resultCount);
				echo display_page_subtitle('Unique ID Search: ' . $numitems . ' Results');
				$table->draw($result, $numitems, 95);
			}
			elseif ($this->type == 'ip')
			{
				if (!isset($_SESSION['loggedin']) || $_SESSION['acclevel'] < 80) {
					die ("Access denied!");
				}
				$table = new Table
				(
					array
					(
						new TableColumn
						(
							'player_id',
							'ID',
							'width=5&align=right'
						),
						new TableColumn
						(
							'name',
							'Player',
							'width=65&flag=1&link=' . urlencode($link_player)
						),
						new TableColumn
						(
							'gamename',
							'Game',
							'width=30'
						)
					),
					'player_id',
					'name',
					'player_id',
					false,
					50,
					'page',
					'sort',
					'sortorder',
					'results',
					'asc'
				);
				if ($this->game)
					$andgame = "AND hlstats_Players.game='" . $this->game . "'";
				else
					$andgame = '';
				$result = $db->query
				("
					SELECT
						connects.playerId AS player_id,
						hlstats_Players.lastname AS name,
						hlstats_Players.flag,
						hlstats_Players.country,
						hlstats_Games.name AS gamename
					FROM
						(
							SELECT
								playerId,
								ipAddress
							FROM
								`hlstats_Events_Connects`
							GROUP BY
								playerId,
								ipAddress
						) AS connects
					LEFT JOIN
						hlstats_Players
					ON
						hlstats_Players.playerId = connects.playerId
					LEFT JOIN
						hlstats_Games
					ON
						hlstats_Games.code = hlstats_Players.game
					WHERE
						hlstats_Games.hidden = '0'
						AND connects.ipAddress LIKE '$sr_query%'
						$andgame
					ORDER BY
						$table->sort $table->sortorder,
						$table->sort2 $table->sortorder
					LIMIT
						$table->startitem,
						$table->numperpage
				");
				$resultCount = $db->query
				("
					SELECT
						COUNT(*)
					FROM
						(
							SELECT
								playerId,
								ipAddress
							FROM
								`hlstats_Events_Connects`
							GROUP BY
								playerId,
								ipAddress
						) AS connects
					LEFT JOIN
						hlstats_Players
					ON
						hlstats_Players.playerId = connects.playerId
					LEFT JOIN
						hlstats_Games
					ON
						hlstats_Games.code = hlstats_Players.game
					WHERE
						hlstats_Games.hidden = '0'
						AND connects.ipAddress LIKE '$sr_query%'
						$andgame
				");
				list($numitems) = $db->fetch_row($resultCount);
				echo display_page_subtitle('IP Address Search: ' . $numitems . ' Results');
				$table->draw($result, $numitems, 95);
			}
			elseif ($this->type == 'clan')
			{
				$table = new Table
				(
					array
					(
						new TableColumn
						(
							'tag',
							'Tag',
							'width=15'
						),
						new TableColumn
						(
							'name',
							'Name',
							'width=50&icon=clan&link=' . urlencode($link_clan)
						),
						new TableColumn
						(
							'gamename',
							'Game',
							'width=30'
						),
						new TableColumn
						(
							'clanId',
							'ID',
							'width=5&align=right'
						)
					),
					'clanId',
					'name',
					'tag',
					false,
					50,
					'page',
					'sort',
					'sortorder',
					'results',
					'asc'
				);
				if ($this->game)
					$andgame = "AND hlstats_Clans.game='" . $this->game . "'";
				else
					$andgame = "";
				$result = $db->query
				("
					SELECT
						hlstats_Clans.clanId,
						hlstats_Clans.tag,
						hlstats_Clans.name,
						hlstats_Games.name AS gamename
					FROM
						hlstats_Clans
					LEFT JOIN hlstats_Games ON
						hlstats_Games.code = hlstats_Clans.game
					WHERE
						hlstats_Games.hidden = '0'
						AND (
							hlstats_Clans.tag LIKE '%$sr_query%'
							OR hlstats_Clans.name LIKE '%$sr_query%'
						)
						$andgame
					ORDER BY
						$table->sort $table->sortorder,
						$table->sort2 $table->sortorder
					LIMIT
						$table->startitem,
						$table->numperpage
				");
				$resultCount = $db->query
				("
					SELECT
						COUNT(*)
					FROM
						hlstats_Clans
					WHERE
						hlstats_Clans.tag LIKE '%$sr_query%'
						OR hlstats_Clans.name LIKE '%$sr_query%'
						$andgame
				");
				list($numitems) = $db->fetch_row($resultCount);

				echo display_page_subtitle('Clan Search: ' . $numitems . ' Results');
				$table->draw($result, $numitems, 95);
			}
		}
	}
?>
