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

	require (PAGE_PATH . '/livestats.php');
	$db->query("SELECT name FROM hlstats_Games WHERE code='" . valid_request($game, false) . "'");
	if ($db->num_rows() < 1) {
		error("No such game '$game'.");
	}

	list($gamename) = $db->fetch_row();
	$db->free_result();

	pageHeader(array($gamename), array($gamename => ''));

	$query = "
			SELECT
				count(*)
			FROM
				hlstats_Players
			WHERE 
				game='" . valid_request($game, false) . "'
	";
	$result = $db->query($query);
	list($total_players) = $db->fetch_row($result);

	$query = "
			SELECT 
				players 
			FROM 
				hlstats_Trend 
			WHERE       
				game='$game'
				AND timestamp<=" . (time() - 86400) . "
			ORDER BY 
				timestamp DESC LIMIT 0,1
	";
	$result = $db->query($query);
	list($total_players_24h) = $db->fetch_row($result);
	$players_last_day = -1;
	if ($total_players_24h > 0) {
		$players_last_day = $total_players - $total_players_24h;
	}

	$query = "
			SELECT
				SUM(kills),
				SUM(headshots),
				count(serverId)		
			FROM
				hlstats_Servers
			WHERE 
				game='" . valid_request($game, false) . "'
	";
	$result = $db->query($query);
	list($total_kills, $total_headshots, $total_servers) = $db->fetch_row($result);

	$query = "
			SELECT 
				kills 
			FROM 
				hlstats_Trend 
			WHERE       
				game='" . valid_request($game, false) . "'
				AND timestamp<=" . (time() - 86400) . "
			ORDER BY 
				timestamp DESC LIMIT 0,1
	";
	$result = $db->query($query);
	list($total_kills_24h) = $db->fetch_row($result);
	$db->free_result();

	$kills_last_day = -1;
	if ($total_kills_24h > 0) {
		$kills_last_day = $total_kills - $total_kills_24h;
	}

	$query = "
			SELECT
                serverId,
                name,
                IF(publicaddress != '',
                    publicaddress,
                    concat(address, ':', port)
                ) AS addr,
                kills,
                headshots,              
                act_players,                                
                max_players,
                act_map,
                map_started,
                map_ct_wins,
                map_ts_wins                 
            FROM
                hlstats_Servers
            WHERE
                game='" . valid_request($game, false) . "'
            ORDER BY
                sortorder, name, serverId
	";
	$db->query($query);
	$servers = $db->fetch_row_set();
	$db->free_result();
?>
<!-- start game.php -->
<?php display_page_title('Servers'); ?>
<?php
if ($total_kills > 0)
			$hpk = sprintf("%.2f", ($total_headshots / $total_kills) * 100);
		else
			$hpk = sprintf("%.2f", 0);
?>
<!-- start game.php -->
<!-- start card section -->
<div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
	<!-- Card -->
	<div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
	<div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
	&nbsp;<i class="fas fa-server"></i>&nbsp;
	</div>
	<div>
		<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Servers</p>
		<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo number_format($total_servers) ?></p>
	</div>
	</div>
	<!-- Card -->
	<div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
	<div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
		&nbsp;<i class="fas fa-user"></i>&nbsp;
	</div>
	<div>
		<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Players</p>
		<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo number_format($total_players) ?></p>
	</div>
	</div>
	<!-- Card -->
	<div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
	<div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
		&nbsp;<i class="fas fa-skull-crossbones"></i>&nbsp;
	</div>
	<div>
		<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Kills</p>
		<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo number_format($total_kills) ?></p>
	</div>
	</div>
	<!-- Card -->
	<div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
	<div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full dark:text-teal-100 dark:bg-teal-500">
	&nbsp;<i class="fas fa-crosshairs"></i>&nbsp;                
	</div>
	<div>
		<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Headshots</p>
		<p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?php echo number_format($total_headshots) . " ($hpk%)" ?></p>
	</div>
	</div>
</div>
<!-- end Card Section -->

<?php
	if ($g_options["show_google_map"] == 1) {
		echo " <!-- start Active Players Map -->\n";
			display_amcharts_map($game,"active");
		echo " <!-- start Active Players Map -->\n";
	}
?>

<?php echo display_page_subtitle("Game Servers"); ?>

<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">

	<div class="w-full overflow-x-auto" style="overflow-y:hidden;">

	<?php
		/* Disable the accordion if only one server */
		if (count($servers) == 1) { 
			$accordion_enabled = "";
		} else {
			$accordion_enabled = "servers";
		}
	?>
		<table id="<?php echo $accordion_enabled; ?>" class="w-full" style="white-space:nowrap;">
			<thead>
				<tr class="text-sm font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td colspan="5" style="width:37%;">&nbsp;Server Name</td>
					<td style="width:7%;text-align:center;">&nbsp;Map</td>
					<td style="width:7%;text-align:center;">&nbsp;Played</td>
					<td style="width:10%;text-align:center;">&nbsp;Players</td>
					<td style="width:7%;text-align:center;">&nbsp;Kills</td>
					<td style="width:7%;text-align:center;">&nbsp;Headshots</td>
					<td style="width:6%;text-align:center;">&nbsp;HS:K</td>
				</tr>
			</thead>
			<tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">

<?php

		$i = 0;
		for ($i = 0; $i < count($servers); $i++)
		{
			$rowdata = $servers[$i];
			$server_id = $rowdata['serverId'];
			$c = ($i % 2) + 1;

			$addr = $rowdata['addr'];
			$kills = $rowdata['kills'];
			$headshots = $rowdata['headshots'];
			$player_string = $rowdata['act_players'] . '/' . $rowdata['max_players'];
			$map_teama_wins = $rowdata['map_ct_wins'];
			$map_teamb_wins = $rowdata['map_ts_wins'];

?>
			<tr style="cursor: pointer;" class="handle text-sm font-semibold text-gray-700 dark:text-gray-400">
			<td colspan="5">&nbsp;&nbsp;<?php
			echo htmlspecialchars($rowdata['name'], ENT_QUOTES) . "\n";
			echo " <span class=\"windmill-text-link\"><a href=\"hlstats.php?game=$game&mode=servers&server_id=$server_id\">(Details)</a></span>\n";
			echo " <span class=\"windmill-text-link\"><a href=\"steam://connect/$addr\">(Join)</a></span>\n";

?></td>
            <td style="text-align:center;"><?php echo $rowdata['act_map']; ?></td>
            <td style="text-align:center;"><?php
			$stamp = $rowdata['map_started']==0?0:time() - $rowdata['map_started'];
			$hours = sprintf("%02d", floor($stamp / 3600));
			$min = sprintf("%02d", floor(($stamp % 3600) / 60));
			$sec = sprintf("%02d", floor($stamp % 60));
			echo $hours . ":" . $min . ":" . $sec;
?></td>
            <td style="text-align:center;"><?php echo $player_string; ?></td>
            <td style="text-align:center;"><?php echo number_format($kills); ?></td>
            <td style="text-align:center;"><?php echo number_format($headshots); ?></td>
            <td style="text-align:center;"><?php
			if ($kills > 0)
				echo sprintf("%.2f", ($headshots / $kills));
			else
				echo sprintf("%.2f", 0);
?></td>
        </tr>
<?php

 printserverstats($server_id);

		}
		?>

	</tbody>
	</table>
	</div>
</div>

<?php include (PAGE_PATH . '/voicecomm_serverlist.php'); ?>


<?php
	if ($g_options['gamehome_show_awards'] == 1) {
		$resultAwards = $db->query("
			SELECT
				hlstats_Awards.awardId,
				hlstats_Awards.name,
				hlstats_Awards.verb,
				hlstats_Awards.d_winner_id,
				hlstats_Awards.d_winner_count,
				hlstats_Players.lastName AS d_winner_name,
				hlstats_Players.flag AS flag,
				hlstats_Players.country AS country
			FROM
				hlstats_Awards
			LEFT JOIN hlstats_Players ON
				hlstats_Players.playerId = hlstats_Awards.d_winner_id
			WHERE
				hlstats_Awards.game='" . valid_request($game, false) . "'
			ORDER BY
				hlstats_Awards.name
		");

		$result = $db->query("
			SELECT
				IFNULL(value, 1)
			FROM
				hlstats_Options
			WHERE
				keyname='awards_numdays'
		");

		if ($db->num_rows($result) == 1)
			list($awards_numdays) = $db->fetch_row($result);
		else
			$awards_numdays = 1;

		$result = $db->query("
			SELECT
				DATE_FORMAT(value, '%W %e %b'),
				DATE_FORMAT( DATE_SUB( value, INTERVAL $awards_numdays DAY ) , '%W %e %b' )
			FROM
				hlstats_Options
			WHERE
				keyname='awards_d_date'
		");
		list($awards_d_date, $awards_s_date) = $db->fetch_row($result);

		if ($db->num_rows($resultAwards) > 0 && $awards_d_date) {
?>
<!-- Start Awards Table -->
<?php echo display_page_subtitle((($awards_numdays == 1) ? '' : "$awards_numdays Day")." Award Winners for $awards_d_date"); ?>

<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
		<table class="w-full" style="white-space:nowrap;">
			<tr class="rounded-b-lg border-b dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
				<td>Award</td>
				<td>Winner</td>
			</tr>
<?php
			$c = 0;
			while ($awarddata = $db->fetch_array($resultAwards))
			{
				if ($awarddata['d_winner_id']) {
					$colour = ($c % 2) + 1;
					$c++;
?>
			<tr class="text-xs tracking-wide text-left text-gray-500 border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
				<td style="width:40%;"><?php
					echo '<a href="'.$g_options['scripturl'].'?mode=dailyawardinfo&amp;award='.$awarddata['awardId']."&amp;game=$game\">".htmlspecialchars($awarddata['name']).'</a>';
?></td>
				<td style="width:60%;" class="flex items-center"><?php
						if ($g_options['countrydata'] == 1) {
							$flag = '0.gif';
							$alt = 'Unknown Country';
							if ($awarddata['flag'] != '') {
								$alt = ucfirst(strtolower($awarddata['country']));
							}
							echo "<img src=\"" . getFlag($awarddata['flag']) . "\" hspace=\"4\" alt=\"$alt\" title=\"$alt\" /><a href=\"{$g_options['scripturl']}?mode=playerinfo&amp;player={$awarddata['d_winner_id']}\"><b>" . htmlspecialchars($awarddata['d_winner_name'], ENT_COMPAT) . "</b></a> ({$awarddata['d_winner_count']} " . htmlspecialchars($awarddata['verb']) . ")";
						} else {
							echo "<img src=\"" . IMAGE_PATH . "/player.gif\" hspace=\"4\" alt=\"Player\" /><a href=\"{$g_options['scripturl']}?mode=playerinfo&amp;player={$awarddata['d_winner_id']}\"><b>" . htmlspecialchars($awarddata['d_winner_name'], ENT_COMPAT) . "</b></a> ({$awarddata['d_winner_count']} ". htmlspecialchars($awarddata['verb']) . ")";
						}
?></td>
			</tr>
<?php
			
					}
			}
?>
			<tr class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
	</div>
</div>
<!-- end Awards Table -->
 <?php
		}
	}
?>


<?php 
if (count($servers) != 1) { 
?>
<script>
	$(document).ready(function(){
		// Hide player rows on initial load
		$(".handle").nextUntil(".handle").children().hide();

		$(".handle").click(function(){
			$(this)
				.toggleClass('open')
					.nextUntil(".handle")
						.children()
						.slideToggle('fast');
		});
	});
</script>
<?php 
}
?>
<!-- end game.php -->
