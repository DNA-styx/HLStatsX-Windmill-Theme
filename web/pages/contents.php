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
	
	// Contents
	
	$resultGames = $db->query("
		SELECT
			code,
			name
		FROM
			hlstats_Games
		WHERE
			hidden='0'
		ORDER BY
			realgame, name ASC
	");
	
	// SQL errors if I don't create a new
	// query after moving the cards
	// to the top of the page 
	$resultGamesCards = $db->query("
		SELECT
			code,
			name
		FROM
			hlstats_Games
		WHERE
			hidden='0'
		ORDER BY
			realgame, name ASC
	");

	$num_games = $db->num_rows($resultGames);
	$redirect_to_game = 0;  
	$game = (!empty($_GET['game'])) ? valid_request($_GET['game'], false) : null;

	if ($num_games == 1 || !empty($game)) {
		$redirect_to_game++;
		if ($num_games == 1) {
			list($game) = $db->fetch_row($resultGames);
		}
		
		include(PAGE_PATH . '/game.php');
	} else {
		unset($_SESSION['game']);
		
		pageHeader(array('Contents'), array('Contents' => ''));
		display_page_title($g_options['sitename'] . ' Player Stats');
?>
<!-- start contents.php -->

<?php

$nonhiddengamestring = "(";
while ($gamedata = $db->fetch_row($resultGamesCards))
{
	$nonhiddengamestring .= "'$gamedata[0]',";
}

$nonhiddengamestring = preg_replace('/,$/', ')', $nonhiddengamestring);
		
$result = $db->query("SELECT COUNT(playerId) FROM hlstats_Players WHERE game IN $nonhiddengamestring");
list($num_players) = $db->fetch_row($result);
$num_players = number_format($num_players);

$result = $db->query("SELECT COUNT(clanId) FROM hlstats_Clans WHERE game IN $nonhiddengamestring");
list($num_clans) = $db->fetch_row($result);
$num_clans = number_format($num_clans);

$result = $db->query("SELECT COUNT(serverId) FROM hlstats_Servers WHERE game IN $nonhiddengamestring");
list($num_servers) = $db->fetch_row($result);
$num_servers = number_format($num_servers);

$result = $db->query("SELECT SUM(kills) FROM hlstats_Servers WHERE game IN $nonhiddengamestring");
list($num_kills) = $db->fetch_row($result);
$num_kills = number_format($num_kills);

$result = $db->query("
	SELECT 
		eventTime
	FROM
		hlstats_Events_Frags
	ORDER BY
		id DESC
	LIMIT 1
");
list($lastevent) = $db->fetch_row($result);

?>

<!-- Start Card Section -->
			<?php echo display_page_subtitle("General Statistics"); ?>
			<div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
						&nbsp;<i class="fas fa-user"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Players</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo $num_players ?></p>
					</div>
				</div>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
						&nbsp;<i class="fas fa-users"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Clans</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo $num_clans ?></p>
					</div>
				</div>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
						&nbsp;<i class="fas fa-book"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Games</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo $num_games ?></p>
					</div>
				</div>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500">
						&nbsp;<i class="fas fa-server"></i>&nbsp;                
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Servers</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo $num_servers ?></p>
					</div>
				</div>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
						&nbsp;<i class="fas fa-skull-crossbones"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Kills</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo $num_kills ?></p>
					</div>
				</div>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500">
						&nbsp;<i class="fas fa-history"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Player Data Expires</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo $g_options['DeleteDays']; ?> Days</p>
					</div>
				</div>
<?php
		if ($lastevent)	{
?>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
						&nbsp;&nbsp;<i class="fas fa-bolt"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Last Kill Logged</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo date('H:i, D. d M.', strtotime($lastevent)) ?></p>
					</div>
				</div>
<?php
			}
?>
				<!-- Card -->
				<div class="flex items-center p-4 bg-gray-50 rounded-lg shadow-xs dark:bg-gray-800">
					<div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
						&nbsp;<i class="fas fa-stopwatch"></i>&nbsp;
					</div>
					<div>
						<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Real Time Stats</p>
						<p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Enabled</p>
					</div>
				</div>
			</div>
<!-- end Card Section -->

<?php echo display_page_subtitle("Game Servers"); ?>

<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">

	<div class="w-full overflow-x-auto">

		<table class="w-full" style="white-space:nowrap;">
			<thead>
				<tr class="text-sm font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="text-align:left; padding:10px;">&nbsp;</td>
					<td style="text-align:center; padding:10px;">Current Players</td>
					<td style="text-align:center; padding:10px;">Top Player</td>
					<td style="text-align:center; padding:10px;">Top Clan</td>
				</tr>
			</thead>
			<tbody class="bg-gray-50 divide-y dark:divide-gray-700 dark:bg-gray-800">
<?php 
		// $nonhiddengamestring = "(";
		while ($gamedata = $db->fetch_row($resultGames))
		{
			//$nonhiddengamestring .= "'$gamedata[0]',";
			$result = $db->query("
				SELECT
					playerId,
					lastName,
					activity
				FROM
					hlstats_Players
				WHERE
					game='$gamedata[0]'
					AND hideranking=0
				ORDER BY
					".$g_options['rankingtype']." DESC,
					(kills/IF(deaths=0,1,deaths)) DESC
				LIMIT 1
			");
		
			if ($db->num_rows($result) == 1)
			{
				$topplayer = $db->fetch_row($result);
			}
			else
			{
				$topplayer = false;
			}
					
			$result = $db->query("
				SELECT
					hlstats_Clans.clanId,
					hlstats_Clans.name,
					AVG(hlstats_Players.skill) AS skill,
					AVG(hlstats_Players.kills) AS kills,
					COUNT(hlstats_Players.playerId) AS numplayers
				FROM
					hlstats_Clans
				LEFT JOIN
					hlstats_Players
				ON
					hlstats_Players.clan = hlstats_Clans.clanId
				WHERE
					hlstats_Clans.game='$gamedata[0]'
					AND hlstats_Clans.hidden = 0
					AND hlstats_Players.hideranking=0
				GROUP BY
					hlstats_Clans.clanId
				HAVING
					".$g_options['rankingtype']." IS NOT NULL
					AND numplayers >= 3
				ORDER BY
					".$g_options['rankingtype']." DESC
				LIMIT 1
			");

			if ($db->num_rows($result) == 1)
			{
				$topclan = $db->fetch_row($result);
			}
			else
			{
				$topclan = false;
			}

			$result= $db->query("
				SELECT
					SUM(act_players) AS `act_players`,                                
					SUM(max_players) AS `max_players`
				FROM
					hlstats_Servers
				WHERE
					hlstats_Servers.game='$gamedata[0]'
			");
							
			$numplayers = $db->fetch_array($result);
			if ($numplayers['act_players'] == 0 and $numplayers['max_players'] == 0)
				$numplayers = false;
			else
				$player_string = $numplayers['act_players'].'/'.$numplayers['max_players'];
?>				
<tr class="text-sm text-gray-700 dark:text-gray-400">
    <td style="padding:10px;">
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
				<a href="<?php echo $g_options['scripturl'] . "?game=$gamedata[0]"; ?>" style="flex-shrink:0;">
					<img src="<?php
				$image = getImage("/games/$gamedata[0]/game");
				if ($image)
					echo $image['url'];
				else
					echo IMAGE_PATH . '/game.gif';
					?>" style="height:32px; width:32px; object-fit:contain; vertical-align:middle;" alt="Game">
				</a>
                <a href="<?php echo $g_options['scripturl'] . "?game=$gamedata[0]"; ?>" style="font-weight:600; min-width:0; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; flex:1;" title="<?php echo htmlspecialchars($gamedata[1]); ?>"><?php echo htmlspecialchars($gamedata[1]); ?></a>
            </div>
            <a href="<?php echo $g_options['scripturl'] . "?mode=players&amp;game=$gamedata[0]"; ?>" style="text-align:center; text-decoration:none;">
                <div><i class="fas fa-user fa-lg"></i></div>
                <div style="font-size:0.7rem;">Players</div>
            </a>
            <a href="<?php echo $g_options['scripturl'] . "?mode=clans&amp;game=$gamedata[0]"; ?>" style="text-align:center; text-decoration:none;">
                <div><i class="fas fa-users fa-lg"></i></div>
                <div style="font-size:0.7rem;">Clans</div>
            </a>
        </div>
    </td>
    <td style="text-align:center; padding:10px;"><?php 
        if ($numplayers) { echo $player_string; } else { echo '-'; }
    ?></td>
    <td style="text-align:center; padding:10px;"><?php
        if ($topplayer) {
            echo '<a href="' . $g_options['scripturl'] . '?mode=playerinfo&amp;player='
                . $topplayer[0] . '">'.htmlspecialchars($topplayer[1], ENT_COMPAT).'</a>';
        } else { echo '-'; }
    ?></td>
    <td style="text-align:center; padding:10px;"><?php
        if ($topclan) {
            echo '<a href="' . $g_options['scripturl'] . '?mode=claninfo&amp;clan='
                . $topclan[0] . '">'.htmlspecialchars($topclan[1], ENT_COMPAT).'</a>';
        } else { echo '-'; }
    ?></td>
</tr>
<?php
		}
?>	
			</tbody>
		</table>
	</div>
		
<?php // Empty footer		
		echo "	<div class=\"rounded-b-lg border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800\">&nbsp;</div>\n";
		echo "</div>\n";

		include(PAGE_PATH . '/voicecomm_serverlist.php');
	}
?>
<!-- end contents.php -->