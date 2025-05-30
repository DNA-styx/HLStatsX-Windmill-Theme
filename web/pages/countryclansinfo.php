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

	// Country Details
	
	$flag = valid_request($_GET['flag'], false) or error('No country ID specified.');

	$SQL = "
		SELECT
			hlstats_Countries.flag,
			hlstats_Countries.name,
			COUNT(hlstats_Players.playerId) AS nummembers,
			SUM(hlstats_Players.kills) AS kills,
			SUM(hlstats_Players.deaths) AS deaths,
			SUM(hlstats_Players.connection_time) AS connection_time,
			ROUND(AVG(hlstats_Players.skill)) AS avgskill,
			IFNULL(SUM(hlstats_Players.kills) / SUM(hlstats_Players.deaths), '-') AS kpd,
			TRUNCATE(AVG(activity), 2) as activity
		FROM
			hlstats_Countries 
		INNER JOIN
			hlstats_Players
		ON (
			hlstats_Players.flag=hlstats_Countries.flag
			)
		WHERE
			hlstats_Players.game='$game'
			AND hlstats_Players.flag='$flag'
			AND hlstats_Players.hideranking = 0
			AND activity >= 0
		GROUP BY
			hlstats_Countries.flag
	";
	
	$db->query($SQL);
	if ($db->num_rows() != 1)
		error("No such countryclan '$flag'.");
	
	$clandata = $db->fetch_array();
	$db->free_result();
	
	
	$cl_name = str_replace(' ', '&nbsp;', htmlspecialchars($clandata['name']));
	$cl_tag  = str_replace(' ', '&nbsp;', htmlspecialchars($clandata['tag']));
	$cl_full = "$cl_tag $cl_name";
	
	$db->query("SELECT name FROM hlstats_Games WHERE code='$game'");
	if ($db->num_rows() != 1)
	{
		$gamename = ucfirst($game);
	}
	else
	{
		list($gamename) = $db->fetch_row();
	}	
	
	pageHeader(
		array($gamename, 'Country Details', $cl_full),
		array(
			$gamename=>$g_options['scripturl'] . "?game=$game",
			'Country Rankings'=>$g_options['scripturl'] . "?mode=countryclans&game=$game",
			'Country Details'=>''
		),
		$clandata['name']
	);
?>

<?php display_page_title('Country Information: ' . $clandata['name']); ?>

<?php display_page_subtitle('Statistics Summary'); ?>

<div
	class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800"
>
	<p class="text-sm text-gray-600 dark:text-gray-400">

		<div style="float:left;width:48.5%;">
			<table class="data-table">
				<tr class="bg2">
					<td style="width:45%;" class="text-gray-700 dark:text-gray-400"><?php
						echo 'Activity:';
					?></td>
					<td width="40%">
		                                <meter min="0" max="100" low="25" high="50" optimum="75" value="<?php
                                        echo $clandata['activity'] ?>"></meter>
					</td>
					<td style="width:15%;" class="text-gray-700 dark:text-gray-400"><?php
						echo sprintf('%0.2f', $clandata['activity']).'%';
					?></td>
				</tr>
				<tr class="bg1">
					<td class="text-gray-700 dark:text-gray-400">Members:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400">
						<strong><?php echo $clandata['nummembers']; ?></strong>
						active members
					</td>
				</tr>
	
				<tr class="bg2">
					<td class="text-gray-700 dark:text-gray-400">Total Kills:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400"><?php
						echo number_format($clandata['kills']);
					?></td>
				</tr>
				
				<tr class="bg1">
					<td class="text-gray-700 dark:text-gray-400">Total Deaths:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400"><?php
						echo number_format($clandata['deaths']);
					?></td>
				</tr>
            
				<tr class="bg2">
					<td class="text-gray-700 dark:text-gray-400">Avg. Kills:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400"><?php
						echo number_format($clandata['kills'] / ($clandata['nummembers']));
					?></td>
				</tr>
				
				<tr class="bg1">
					<td class="text-gray-700 dark:text-gray-400">Kills per Death:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400"><strong><?php
						if ($clandata['deaths'] != 0)
						{
							printf('%0.2f', $clandata['kills'] / $clandata['deaths']);
						}
						else
						{
							echo '-';
						}
					?></strong></td>
				</tr>
        
				<tr class="bg2">
					<td style="width:45%;" class="text-gray-700 dark:text-gray-400">Kills per Minute:</td>
					<td colspan="2" style="width:55%;" class="text-gray-700 dark:text-gray-400"><?php
						if ($clandata['connection_time'] > 0) {
							echo sprintf('%.2f', ($clandata['kills'] / ($clandata['connection_time'] / 60)));
						} else {
							echo '-'; 
						}
					?></td>
				</tr>

				<tr class="bg1">
					<td class="text-gray-700 dark:text-gray-400">Avg. Member Points:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400"><?php
						echo '<strong>' . number_format($clandata['avgskill']) . '</strong>';
					?></td>
				</tr>

				<tr class="bg2">
					<td class="text-gray-700 dark:text-gray-400">Avg. Connection Time:</td>
					<td  colspan="2" class="text-gray-700 dark:text-gray-400"><?php
						if ($clandata['connection_time'] > 0) {
							echo timestamp_to_str($clandata['connection_time'] / ($clandata['nummembers']));
					} else {
						echo '-'; 
					}
					?></td>
				</tr>
                    
				<tr class="bg1">
					<td class="text-gray-700 dark:text-gray-400">Total Connection Time:</td>
					<td colspan="2" class="text-gray-700 dark:text-gray-400"><?php
						echo timestamp_to_str($clandata['connection_time']);
					?></td>
				</tr>
			</table>
		</div>
		<div style="float:right;width:48.5%;text-align:center;padding-top:50px;">
<?php
			if (file_exists(IMAGE_PATH.'/flags/'.strtolower($flag).'_large.png')) {
				echo '<img src="'.IMAGE_PATH.'/flags/'.strtolower($flag).'_large.png" style="border:0px;" alt="'.$flag.'">';
			} else {
				echo '<img src="'.IMAGE_PATH.'/countryclanlogos/NA.png" style="border:0px;" alt="">';
			}
?>
		</div>
	</p>
</div>

<?php
	flush();
	
	$tblMembers = new Table(
		array(
			new TableColumn(
				'lastName',
				'Name',
				'width=28&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
			),
                        new TableColumn(
                                'mmrank',
                                'Rank',
                                'width=4&type=elorank'
                        ),
			new TableColumn(
				'skill',
				'Points',
				'width=6&align=right'
			),
			new TableColumn(
				'activity',
				'Activity',
				'width=10&sort=no&type=bargraph'
			),
			new TableColumn(
				'connection_time',
				'Time',
				'width=13&align=right&type=timestamp'
			),
			new TableColumn(
				'kills',
				'Kills',
				'width=6&align=right'
			),
			new TableColumn(
				'percent',
				'Clan Kills',
				'width=10&sort=no&type=bargraph'
			),
			new TableColumn(
				'percent',
				'%',
				'width=6&sort=no&align=right&append=' . urlencode('%')
			),
			new TableColumn(
				'deaths',
				'Deaths',
				'width=6&align=right'
			),
			new TableColumn(
				'kpd',
				'Kpd',
				'width=6&align=right'
			),
		),
		'playerId',
		'skill',
		'kpd',
		true,
		20,
		'members_page',
		'members_sort',
		'members_sortorder',
		'members'
	);

	$result = $db->query("
		SELECT
			hlstats_Players.playerId,
			hlstats_Players.lastName,
			hlstats_Players.country,
			hlstats_Players.flag,
			hlstats_Players.skill,
			hlstats_Players.mmrank,
			hlstats_Players.connection_time,
			hlstats_Players.kills,
			hlstats_Players.deaths,
			ROUND(hlstats_Players.kills / IF(hlstats_Players.deaths = 0, 1, hlstats_Players.deaths), 2) AS kpd,
			ROUND(hlstats_Players.kills / IF(" . $clandata['kills'] . " = 0, 1, " . $clandata['kills'] . ") * 100, 2) AS percent,
			IF(".$g_options['MinActivity']." > (UNIX_TIMESTAMP() - last_event), ((100/".$g_options['MinActivity'].") * (".$g_options['MinActivity']." - (UNIX_TIMESTAMP() - last_event))), -1) as activity
		FROM
			hlstats_Players
		WHERE
			flag='$flag'
			AND hlstats_Players.hideranking = 0
			AND hlstats_Players.game='$game'      
		GROUP BY
			hlstats_Players.playerId
		HAVING
			activity >= 0
		ORDER BY
			$tblMembers->sort $tblMembers->sortorder,
			$tblMembers->sort2 $tblMembers->sortorder,
			lastName ASC
		LIMIT $tblMembers->startitem,$tblMembers->numperpage
	");
	
	$resultCount = $db->query("
		SELECT
			playerId,
			IF(".$g_options['MinActivity']." > (UNIX_TIMESTAMP() - last_event), ((100/".$g_options['MinActivity'].") * (".$g_options['MinActivity']." - (UNIX_TIMESTAMP() - last_event))), -1) as activity
		FROM
			hlstats_Players
		WHERE
			flag='$flag'
			AND hlstats_Players.hideranking = 0
			AND hlstats_Players.game='$game'      
		GROUP BY
			hlstats_Players.playerId
		HAVING
			activity >= 0
	");
	
	$numitems = $db->num_rows($resultCount);
?>
<div class="block" style="padding-top:10px;">
<?php
	display_page_subtitle('Members');
	$tblMembers->draw($result, $numitems, 95);
?></div>
