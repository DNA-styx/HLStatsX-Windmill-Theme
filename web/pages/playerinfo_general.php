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

	if ($g_options['playerinfo_tabs'] == '1') { 
		// include custom windmill functions 
		include_once INCLUDE_PATH . '/inc_windmill_functions.php';
	}

?>
<!-- start playerinfo_general.php -->
<!-- Start large cards -->
<div class="grid gap-6 mb-8 md:grid-cols-2">
<!-- Start left card -->
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
    	<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Player Profile</h4>
    	<p class="text-gray-600 dark:text-gray-400">

			<table class="data-table">
			<tr>
					<td style="vertical-align:top;"> </td>
					<td style="text-align:center; vertical-align:middle;" rowspan="7" id="player_avatar">
						<?php
							$db->query
							("
								SELECT
									hlstats_PlayerUniqueIds.uniqueId,
									CAST(LEFT(hlstats_PlayerUniqueIds.uniqueId,1) AS unsigned) + CAST('76561197960265728' AS unsigned) + CAST(MID(hlstats_PlayerUniqueIds.uniqueId, 3,10)*2 AS unsigned) AS communityId
								FROM
									hlstats_PlayerUniqueIds
								WHERE
									hlstats_PlayerUniqueIds.playerId = '$player'
							");
							list($uqid, $coid) = $db->fetch_row();
						
							$status = 'Unknown';
							$avatar_full = IMAGE_PATH."/unknown.jpg";
						
							if ($coid !== '76561197960265728') {

								$profileUrl = "https://steamcommunity.com/profiles/$coid?xml=1";
		
								$curl = curl_init();
								curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
								curl_setopt($curl, CURLOPT_URL, $profileUrl);

								$xml = curl_exec($curl);
							}
							
							$xmlDoc = null;
							if ($xml) {
								$xmlDoc = simplexml_load_string($xml);
							}
						
							if ($xmlDoc) {
								$status = ucwords($xmlDoc->onlineState);
								$avatar_full = $xmlDoc->avatarFull;
							}
						
							echo("<img src=\"$avatar_full\" style=\"height:158px;width:158px;\" alt=\"Steam Community Avatar\">");
						?>
					</td>
				</tr>
				<tr class="text-sm font-semibold tracking-wide text-left text-gray-500 uppercase bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>
						<?php
							echo '<span class="flex items-center"><img src="'.getFlag($playerdata['flag']).'" alt="'.$playerdata['country'].'" title="'.$playerdata['country'].'">&nbsp;';
							echo '<strong>' . htmlspecialchars($playerdata['lastName'], ENT_COMPAT) . ' </strong></span>';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>
						<?php
							if ($playerdata['country'])
							{
								echo 'Location: ';
								if ($playerdata['city']) {
									echo htmlspecialchars($playerdata['city'], ENT_COMPAT) . ', ';
								}
								echo '<a href="'.$g_options['scripturl'].'?mode=countryclansinfo&amp;flag='.$playerdata['flag']."&amp;game=$game\">" . $playerdata['country'] . '</a>';
							}
							else
							{
								echo 'Location: (Unknown)';
							}
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>
						<?php 
							$prefix = ((!preg_match('/^BOT/i',$uqid)) && $g_options['Mode'] == 'Normal') ? 'STEAM_0:' : '';
							echo "Steam: <a href=\"http://steamcommunity.com/profiles/$coid\" target=\"_blank\">$prefix" . "$uqid</a>";
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Status: <strong><?php echo $status; ?></strong></td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>
						<a href="steam://friends/add/<?php echo($coid); ?>" target="_blank">Click here to add as friend</a>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td><?php echo "Karma: $statusmsg"; ?></td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:50%;">Member of Clan:</td>
					<td style="width:50%;">
						<?php
							if ($playerdata['clan'])
							{
								echo '&nbsp;<a href="' . $g_options['scripturl'] . '?mode=claninfo&amp;clan=' . $playerdata['clan'] . '">' . htmlspecialchars($playerdata['clan_name'], ENT_COMPAT) . '</a>';
							}
							else
								echo '(None)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Real Name:</td>
					<td>
						<?php
							if ($playerdata['fullName'])
							{
								echo '<b>' . htmlspecialchars($playerdata['fullName'], ENT_COMPAT) . '</b>';
							}
							else
								echo "(<a href=\"" . $g_options['scripturl'] . '?mode=help#set"><em>Not Specified</em></a>)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>E-mail Address:</td>
					<td>
						<?php
							if ($email = getEmailLink($playerdata['email']))
							{
								echo $email;
							}
							else
								echo "(<a href=\"" . $g_options['scripturl'] . '?mode=help#set"><em>Not Specified</em></a>)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Home Page:</td>
					<td>
						<?php
							if ($playerdata['homepage'])
							{
								echo getLink($playerdata['homepage']);
							}
							else
								echo "(<a href=\"" . $g_options['scripturl'] . '?mode=help#set"><em>Not Specified</em></a>)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <td>MM Rank:</td>
                            <td><?php
												if ($playerdata['mmrank'])
												{
														echo '<img src=hlstatsimg/mmranks/' . $playerdata['mmrank'] . '.png alt="rank" style="height:20px;width:50px;">' . "\n";
												}
												else
						echo '<img src=hlstatsimg/mmranks/0.png alt="rank" style="height:20px;width:50px;">';
										?></td>
				</tr>
				<tr  class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Last Connect:*</td>
					<td>
						<?php
							$db->query
							("
								SELECT
									DATE_FORMAT(eventTime, '%a. %b. %D, %Y @ %T')
								FROM
									hlstats_Events_Connects
								WHERE
									hlstats_Events_Connects.playerId = '$player'
								ORDER BY
									id desc
								LIMIT
									1
							");
							list($lastevent) = $db->fetch_row();
							if ($lastevent)
								echo $lastevent;
							else
								echo '(Unknown)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Total Connection Time:</td>
					<td>
						<?php echo timestamp_to_str($playerdata['connection_time']); ?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Average Ping:*</td>
					<td>
						<?php
							$db->query
							("
								SELECT
									ROUND(SUM(hlstats_Events_Latency.ping) / COUNT(hlstats_Events_Latency.ping), 0) AS av_ping,
									ROUND(ROUND(SUM(hlstats_Events_Latency.ping) / COUNT(ping), 0) / 2, 0) AS av_latency
								FROM
									hlstats_Events_Latency
								WHERE 
									hlstats_Events_Latency.playerId = '$player'
							");
							list($av_ping, $av_latency) = $db->fetch_row();
							if ($av_ping)
								echo $av_ping." ms (Latency: $av_latency ms)";
							else
								echo '-';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Favorite Server:*</td>
					<td>
						<?php
							// leave this one
							$db->query
							("
								SELECT
									hlstats_Events_Entries.serverId,
									hlstats_Servers.name,
									COUNT(hlstats_Events_Entries.serverId) AS cnt
								FROM
									hlstats_Events_Entries
								INNER JOIN
									hlstats_Servers
								ON
									hlstats_Servers.serverId = hlstats_Events_Entries.serverId
								WHERE 
									hlstats_Events_Entries.playerId = '$player'
								GROUP BY
									hlstats_Events_Entries.serverId
								ORDER BY
									cnt DESC
								LIMIT
									1
							");
							list($favServerId, $favServerName) = $db->fetch_row();
							echo "<a href='hlstats.php?game=$game&amp;mode=servers&amp;server_id=$favServerId'> $favServerName </a>";
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Favorite Map:*</td>
					<td>
						<?php
							$db->query
							("
								SELECT
									hlstats_Events_Entries.map,
									COUNT(map) AS cnt
								FROM
									hlstats_Events_Entries
								WHERE
									hlstats_Events_Entries.playerId = '$player'
								GROUP BY
									hlstats_Events_Entries.map
								ORDER BY
									cnt DESC
								LIMIT
									1
							");
							list($favMap) = $db->fetch_row();
							echo "<a href=\"hlstats.php?game=$game&amp;mode=mapinfo&amp;map=$favMap\"> $favMap </a>";
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Favorite Weapon:*</td>
						<?php
							$result = $db->query("
								SELECT
									hlstats_Events_Frags.weapon,
									hlstats_Weapons.name,
									COUNT(hlstats_Events_Frags.weapon) AS kills,
									SUM(hlstats_Events_Frags.headshot=1) as headshots
								FROM
									hlstats_Events_Frags
								LEFT JOIN
									hlstats_Weapons
								ON
									hlstats_Weapons.code = hlstats_Events_Frags.weapon
								WHERE
									hlstats_Events_Frags.killerId=$player
								GROUP BY
									hlstats_Events_Frags.weapon,
									hlstats_Weapons.name
								ORDER BY
									kills desc, headshots desc
								LIMIT
									1
							");

						    $fav_weapon = '';
						    $weap_name = '';

							while ($rowdata = $db->fetch_row($result)) {
								$fav_weapon = $rowdata[0];
								$weap_name = htmlspecialchars($rowdata[1]);
							}

							if ($fav_weapon == '') {
								$fav_weapon = 'Unknown';
								$weap_name = 'Unknown';
                            }

							$image = getImage("/games/$game/weapons/$fav_weapon");
						// Check if image exists
							$weaponlink = "<a href=\"hlstats.php?mode=weaponinfo&amp;weapon=$fav_weapon&amp;game=$game\">";
							if ($image)
							{
								$cellbody = "\t\t\t\t\t<td style=\"text-align: center\">$weaponlink<img src=\"" . $image['url'] . "\" alt=\"$weap_name\" title=\"$weap_name\">";
							}
							else
							{
								$cellbody = "\t\t\t\t\t<td>$weaponlink<strong>$weap_name</strong>";
							}
							$cellbody .= "</a>";
							echo $cellbody;
						?>
					</td>
				</tr>
			</table>
		</p>
	</div>
<!-- end left card -->

<!-- start right card -->
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800" >
	<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Statistics Summary</h4>
            <p class="text-gray-600 dark:text-gray-400">

			<table>
				<tr>
					<td style="vertical-align:top;" colspan="3"> </td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:50%;">Activity:</td>
					<td style="width:35%;">
	                    <meter min="0" max="100" low="25" high="50" optimum="75" value="<?php
                                        echo $playerdata['activity'] ?>"></meter>
					</td>
					<td style="width:15%;"><?php echo $playerdata['activity'].'%'; ?></td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td>Points:</td>
					<td style="width:55%;" colspan="2">
						<?php
							echo '<b>' . number_format($playerdata['skill']) . '</b>';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Rank:</td>
					<td style="width:55%;" colspan="2">
						<?php
							if (($playerdata['activity'] > 0) && ($playerdata['hideranking'] == 0))
							{
								$rank = get_player_rank($playerdata);
							}
							else
							{
								if ($playerdata['hideranking'] == 1)
								{
									$rank = "Hidden";
								}
								elseif ($playerdata['hideranking'] == 2)
								{
									$rank = "<span style=\"color:red;\">Banned</span>";
								}
								else
								{
									$rank = 'Not active';
								}
							} 
							if (is_numeric($rank))
							{
								echo '<b>' . number_format($rank) . '</b>';
							}
							else
							{
								echo "<b> $rank</b>";
							}
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Kills per Minute:</td>
					<td style="width:55%;" colspan="2">
						<?php
							if ($playerdata['connection_time'] > 0)
							{
								echo sprintf('%.2f', ($playerdata['kills'] / ($playerdata['connection_time'] / 60)));
							}
							else
							{
								echo '-'; 
							} 
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Kills per Death:</td>
					<td style="width:55%;" colspan="2">
						<?php
							$db->query
							("
								SELECT
									IFNULL(ROUND(SUM(hlstats_Events_Frags.killerId = '$player') / IF(SUM(hlstats_Events_Frags.victimId = '$player') = 0, 1, SUM(hlstats_Events_Frags.victimId = '$player')), 2), '-')
								FROM
									hlstats_Events_Frags
								WHERE
									(
										hlstats_Events_Frags.killerId = '$player'
										OR hlstats_Events_Frags.victimId = '$player'
									)
							");
							list($realkpd) = $db->fetch_row();
							echo $playerdata['kpd'];
							echo " ($realkpd*)";
							echo display_killsperdeath_icon($playerdata['kpd']);
?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Headshots per Kill:</td>
					<td style="width:55%;" colspan="2">
						<?php
							$db->query
							("
								SELECT
									IFNULL(SUM(hlstats_Events_Frags.headshot=1) / COUNT(*), '-')
								FROM
									hlstats_Events_Frags
								WHERE
									hlstats_Events_Frags.killerId = '$player'
							");
							list($realhpk) = $db->fetch_row();
							echo $playerdata['hpk'];
							echo " ($realhpk*)";
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Shots per Kill:</td>
					<td style="width:55%;" colspan="2">
						<?php
							$db->query
							("
								SELECT
									IFNULL(ROUND((SUM(hlstats_Events_Statsme.hits) / SUM(hlstats_Events_Statsme.shots) * 100), 2), 0.0) AS accuracy,
									SUM(hlstats_Events_Statsme.shots) AS shots,
									SUM(hlstats_Events_Statsme.hits) AS hits,
									SUM(hlstats_Events_Statsme.kills) AS kills
								FROM
									hlstats_Events_Statsme
								WHERE
									hlstats_Events_Statsme.playerId='$player'
							");
							list($playerdata['accuracy'], $sm_shots, $sm_hits, $sm_kills) = $db->fetch_row();
							if ($sm_kills > 0)
							{
								echo sprintf('%.2f', ($sm_shots / $sm_kills));
							}
							else
							{
								echo '-';
							}
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Weapon Accuracy:</td>
					<td style="width:55%;" colspan="2">
						<?php
							echo $playerdata['acc'] . '%';
							echo " (".sprintf('%.0f', $playerdata['accuracy']).'%*)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Headshots:</td>
					<td style="width:55%;" colspan="2">
						<?php
							if ($playerdata['headshots']==0) 
								echo number_format($realheadshots);
							else
								echo number_format($playerdata['headshots']);
								echo ' ('.number_format($realheadshots).'*)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Kills:</td>
					<td style="width:55%;" colspan="2">
						<?php
							echo number_format($playerdata['kills']);
							echo ' ('.number_format($realkills).'*)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Deaths:</td>
					<td style="width:55%;" colspan="2">
						<?php
							echo number_format($playerdata['deaths']);
							echo ' ('.number_format($realdeaths).'*)';
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Longest Kill Streak:</td>
					<td style="width:55%;" colspan="2">
						<?php
							$db->query
							("
								SELECT
									hlstats_Players.kill_streak
								FROM
									hlstats_Players
								WHERE
									hlstats_Players.playerId = '$player'
							");
							list($kill_streak) = $db->fetch_row();
							echo number_format($kill_streak);
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Longest Death Streak:</td>
					<td style="width:55%;" colspan="2">
						<?php
							$db->query
							("
								SELECT
									hlstats_Players.death_streak
								FROM
									hlstats_Players
								WHERE
									hlstats_Players.playerId = '$player'
							");
							list($death_streak) = $db->fetch_row();
							echo number_format($death_streak);
						?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Suicides:</td>
					<td style="width:55%;" colspan="2">
						<?php echo number_format($playerdata['suicides']); ?>
					</td>
				</tr>
				<tr class="text-sm tracking-wide text-left text-gray-500 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td style="width:45%;">Teammate Kills:</td>
					<td style="width:55%;" colspan="2">
						<?php
							echo number_format($playerdata['teamkills']);
							echo ' ('.number_format($realteamkills).'*)';
						?>
					</td>
				</tr>
			</table>
		</p>
	</div>
<!-- end right card -->
</div>		
<!-- end large cards -->			

<!--
			<?php
				echo '&nbsp;&nbsp;<img src="' . IMAGE_PATH . '/history.gif" style="padding-left:3px;padding-right:3px;" alt="History" />&nbsp;<b>'
					. htmlspecialchars($playerdata['lastName'], ENT_COMPAT) . '</b>\'s History:<br />';
				echo '&nbsp;&nbsp;<a href="' . $g_options['scripturl'] . "?mode=playerhistory&amp;player=$player\">Events</a>&nbsp;|&nbsp;";
				echo '<a href="' . $g_options['scripturl'] . "?mode=playersessions&amp;player=$player\">Sessions</a>&nbsp;|&nbsp;";
				$resultCount = $db->query
				("
					SELECT
						COUNT(*)
					FROM
						hlstats_Players_Awards
					WHERE
						hlstats_Players_Awards.playerId = $player
				");
				list($numawards) = $db->fetch_row($resultCount);
				echo "<a href=\"" . $g_options['scripturl'] . "?mode=playerawards&amp;player=$player\">Awards&nbsp;($numawards)</a>&nbsp;|&nbsp;";
				if ($g_options["nav_globalchat"] == 1)
				{
					echo "<a href=\"" . $g_options['scripturl'] . "?mode=chathistory&amp;player=$player\">Chat</a>";
				}
			?>
			<br />&nbsp;&nbsp;<a href="<?php echo $g_options['scripturl']; ?>?mode=search&amp;st=player&amp;q=<?php echo $pl_urlname; ?>"><img src="<?php echo IMAGE_PATH; ?>/search.gif" style="margin-left:3px;margin-right:3px;" alt="Search" />&nbsp;Find other players with the same name</a>
		</div>
	</div>
-->

<?php
// Current rank & rank history
	$db->query
	("
		SELECT
			hlstats_Ranks.rankName,
			hlstats_Ranks.image,
			hlstats_Ranks.minKills
		FROM
			hlstats_Ranks
		WHERE
			hlstats_Ranks.minKills <= ".$playerdata['kills']."
			AND hlstats_Ranks.game = '$game'
		ORDER BY
			hlstats_Ranks.minKills DESC
		LIMIT
			1
	");
	$result = $db->fetch_array();
	$rankimage = getImage('/ranks/'.$result['image']);
	$rankName = $result['rankName'];
	$rankCurMinKills = $result['minKills']; 
	$db->query
	("
		SELECT
			hlstats_Ranks.rankName,
			hlstats_Ranks.minKills
		FROM
			hlstats_Ranks
		WHERE
			hlstats_Ranks.minKills > ".$playerdata['kills']."
			AND hlstats_Ranks.game = '$game'
		ORDER BY
			hlstats_Ranks.minKills
		LIMIT
			1
	");
	if ($db->num_rows() == 0)
	{
		$rankKillsNeeded = 0;
		$rankPercent = 0;
	}
	else
	{
		$result = $db->fetch_array();
		$rankKillsNeeded = $result['minKills'] - $playerdata['kills'];
		$rankPercent = ($playerdata['kills'] - $rankCurMinKills) * 100 / ($result['minKills'] - $rankCurMinKills);
	}
	$db->query
	("
		SELECT
			hlstats_Ranks.rankName,
			hlstats_Ranks.image
		FROM
			hlstats_Ranks
		WHERE
			hlstats_Ranks.minKills <= ".$playerdata['kills']."
			AND hlstats_Ranks.game = '$game'
		ORDER BY
			hlstats_Ranks.minKills
	");

    $rankHistory = "";
    $db_num_rows = $db->num_rows();

	for ($i = 1; $i < $db_num_rows; $i++) {
		$result = $db->fetch_array();

		$histimage = getImage('/ranks/' . $result['image'] . '_small');
		$rankHistory .= '<img src="' . $histimage['url'] . '" title="' . $result['rankName'] . '" alt="' . $result['rankName'] . '"> ';
	} 
?>

	<div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">	

		<div class="flex items-start p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
			<div class="p-3 mr-4 rounded-full">
			<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Player Rank</h4>
			<?php echo '<img src="'.$rankimage['url']."\" alt=\"$rankName\" title=\"$rankName\">"; ?>
				<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
					Current rank: <b><?php echo htmlspecialchars($rankName, ENT_COMPAT); ?></b><br>
					<meter min="0" max="100" low="25" high="50" optimum="75" value="<?php echo $rankPercent ?>"></meter><br>
					Next rank: <?php echo "$rankKillsNeeded kills (".number_format($rankPercent, 0, '.', '');?>%)
				</p>
			</div>
		</div>

		<div class="flex items-start p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
			<div class="p-3 mr-4 rounded-full">
				<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Player History</h4>
				<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
					<?php echo '<a href="' . $g_options['scripturl'] . "?mode=playerhistory&amp;player=$player\">Events</a>"; ?>
				</p>
				<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
					<?php echo '<a href="' . $g_options['scripturl'] . "?mode=playersessions&amp;player=$player\">Sessions</a>"; ?>
				</p>
<?php				if ($g_options["nav_globalchat"] == 1)
				{
					echo "<p class=\"mb-2 text-sm font-medium text-gray-600 dark:text-gray-400\">";
					echo "<a href=\"" . $g_options['scripturl'] . "?mode=chathistory&amp;player=$player\">Chat</a>";
					echo "				</p>";
				}
?>
				<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
					<a href="<?php echo $g_options['scripturl']; ?>?mode=search&amp;st=player&amp;q=<?php echo $pl_urlname; ?>">Find other players with the same name</a>
				</p>
			</div>
		</div>

		<div class="flex items-start p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
			<div class="p-3 mr-4 rounded-full">
				<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Player Trend</h4>
				<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
				<?php echo "<a href=\"trend_graph.php?bgcolor=".$g_options['graphbg_trend'].'&amp;color='.$g_options['graphtxt_trend']."&amp;player=$player\" alt=\"Player Trend Graph\">"; ?>
				<?php echo "	<img src=\"trend_graph.php?bgcolor=".$g_options['graphbg_trend'].'&amp;color='.$g_options['graphtxt_trend']."&amp;player=$player\" alt=\"Player Trend Graph\">"; ?>
				<?php echo "</a>"; ?>
				</p>
			</div>
		</div>

<?php
if ($g_options['sigbackground']){		
?>
		<div class="flex items-start p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
			<div class="p-3 mr-4 rounded-full">
				<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">Forum Signature</h4>
				<p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">

				<?php
					if ($g_options['modrewrite'] == 0)
					{
						$imglink = $siteurlneo.'sig.php?player_id='.$player.'&amp;background='.$g_options['sigbackground'];
						$jimglink = $siteurlneo.'sig.php?player_id='.$player.'&background='.$g_options['sigbackground'];
					}
					else
					{
						$imglink = $siteurlneo.'sig-'.$player.'-'.$g_options['sigbackground'].'.png';
						$jimglink = $imglink;
					}
					
					echo "<a href=\"$imglink\">";					
					echo "	<img src=\"$imglink\" title=\"Copy &amp; Paste the whole URL below in your forum signature\" alt=\"forum sig image\"/>";
					echo "</a>";

					$script_path = (isset($_SERVER['SSL']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")) ? 'https://' : 'http://';
					$script_path .= $_SERVER['HTTP_HOST'];
					$script_path .= str_replace('\\','/',dirname($_SERVER['PHP_SELF']));
					$script_path = preg_replace('/\/$/','',$script_path);
				?>
				<br />
				<script type="text/javascript">
					/* <![CDATA[ */
					function setForumText(val)
					{
						var txtArea = document.getElementById('siglink');
						switch(val)
						{
							case 0:
								<?php echo "txtArea.value = '$jimglink'\n"; ?>
								break;	
							case 1:
								<?php echo "txtArea.value = '[url=$script_path/hlstats.php?mode=playerinfo&player=$player"."][img]$jimglink"."[/img][/url]'\n"; ?>
								break;
							case 2:
								<?php echo "txtArea.value = '[url=\"$script_path/hlstats.php?mode=playerinfo&player=$player\"][img]$jimglink"."[/img][/url]'\n"; ?>
								break;
							case 3:
								<?php echo "txtArea.value = '[![". htmlspecialchars($playerdata['lastName'], ENT_COMPAT) . "`s Stats]($jimglink"." \"". htmlspecialchars($playerdata['lastName'], ENT_COMPAT) . "`s Stats\")]($script_path/hlstats.php?mode=playerinfo&player=$player)'\n"; ?>
							
							
							
							
							
								break;
						}
					}
					/* ]]> */
				</script>
				<span class="text-xs text-gray-600 dark:text-gray-400">
				<a href="" onclick="setForumText(1);return false">
					bbCode 1 (phpBB, SMF)</a>&nbsp;|&nbsp;<a href="" onclick="setForumText(2);return false">bbCode 2 (IPB)</a>&nbsp;|&nbsp;<a href="" onclick="setForumText(0);return false">Direct Image</a>&nbsp;|&nbsp;<a href="" onclick="setForumText(3);return false">Markdown</a>
				
				</span>
				<?php echo '<textarea class="block w-full mt-1 text-xs dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" style="width: 95%; height: 100px;" rows="2" cols="70" id="siglink" readonly="readonly" onclick="document.getElementById(\'siglink\').select();">[url='."$script_path/hlstats.php?mode=playerinfo&amp;player=$player"."][img]$imglink".'[/img][/url]</textarea>'; ?>

			</p>
			</div>
		</div>

<?php
}
?>


	</div>



<?php
// Awards
	$numawards = $db->query
	("
		SELECT
			hlstats_Ribbons.awardCode,
			hlstats_Ribbons.image
		FROM
			hlstats_Ribbons
		WHERE
			hlstats_Ribbons.game = '$game'
			AND
			(
				hlstats_Ribbons.special = 0
				OR hlstats_Ribbons.special = 2
			)
		GROUP BY
			hlstats_Ribbons.awardCode,
			hlstats_Ribbons.image
	");
	$res = $db->query
	("
		SELECT
			hlstats_Ribbons.awardCode AS ribbonCode,
			hlstats_Ribbons.ribbonName AS ribbonName,
			IF(ISNULL(hlstats_Players_Ribbons.playerId), 'noaward.png', hlstats_Ribbons.image) AS image,
			hlstats_Ribbons.special,
			hlstats_Ribbons.image AS imagefile,
			hlstats_Ribbons.awardCount
		FROM
			hlstats_Ribbons
		LEFT JOIN
		(
			SELECT
				hlstats_Players_Ribbons.playerId,
				hlstats_Ribbons.awardCode,
				hlstats_Players_Ribbons.ribbonId
			FROM
				hlstats_Players_Ribbons
			INNER JOIN
				hlstats_Ribbons 
			ON
				hlstats_Ribbons.ribbonId = hlstats_Players_Ribbons.ribbonId
				AND hlstats_Ribbons.game = hlstats_Players_Ribbons.game 
			WHERE
				hlstats_Players_Ribbons.playerId = ".$playerdata['playerId']."
				AND hlstats_Players_Ribbons.game = '$game'
			ORDER BY
				hlstats_Ribbons.awardCount DESC
		) AS hlstats_Players_Ribbons
		ON
			hlstats_Players_Ribbons.ribbonId = hlstats_Ribbons.ribbonId
		WHERE
			hlstats_Ribbons.game = '$game'
			AND
			(
				ISNULL(hlstats_Players_Ribbons.playerId)
				OR hlstats_Players_Ribbons.playerId = ".$playerdata['playerId']."
			)
		ORDER BY
			hlstats_Ribbons.awardCode,
			hlstats_Players_Ribbons.playerId DESC,
			hlstats_Ribbons.special,
			hlstats_Ribbons.awardCount DESC
	");
	$ribbonList = '';
	$lastImage = '';
	$awards_done = array ();
	while ($result = $db->fetch_array($res))
	{
		$ribbonCode=$result['ribbonCode'];
		$ribbonName=$result['ribbonName'];
		if(!isset($awards_done[$ribbonCode]))
		{
			if (file_exists(IMAGE_PATH."/games/$game/ribbons/".$result['image']))
			{
				$image = IMAGE_PATH."/games/$game/ribbons/".$result['image'];
			}
			elseif (file_exists(IMAGE_PATH."/games/$realgame/ribbons/".$result['image']))
			{
				$image = IMAGE_PATH."/games/$realgame/ribbons/".$result['image'];
			}
			else
			{
				$image = IMAGE_PATH."/award.png";
			}		
			$ribbonList .= '<img src="'.$image.'" style="border:0px;" alt="'.$result['ribbonName'].'" title="'.$result["ribbonName"].'"> ';
			$awards_done[$ribbonCode]=$ribbonCode;
		}
	}
	$awards = array ();
	$res = $db->query
	("
		SELECT
			hlstats_Awards.awardType,
			hlstats_Awards.code,
			hlstats_Awards.name
		FROM
			hlstats_Awards
		WHERE
			hlstats_Awards.game = '$game'
			AND hlstats_Awards.g_winner_id = $player
		ORDER BY
			hlstats_Awards.name;
	");

	while ($r1 = $db->fetch_array()) {
		unset($tmp_arr);
		$tmp_arr = new StdClass;

		$tmp_arr->aType = $r1['awardType'];
		$tmp_arr->code = $r1['code'];
		$tmp_arr->ribbonName = $r1['name'];

		// Unused code, undefined variable $id
		/*if ($id == 0)
		{
			$tmp_arr->playerName = $r1['lastname'];
			$tmp_arr->flag = $r1['flag'];
			$tmp_arr->playerId = $r1['g_winner_id'];
			$tmp_arr->kills = $r1['g_winner_count'];
			$tmp_arr->verb = $r1['verb'];
		}*/

		array_push($awards, $tmp_arr);
	}

	$GlobalAwardsList = '';
	foreach ($awards as $a)
	{
		if ($image = getImage("/games/$game/gawards/".strtolower($a->aType."_$a->code")))
		{
			$image = $image['url'];
		}
		elseif ($image = getImage("/games/$realgame/gawards/".strtolower($a->aType."_$a->code")))
		{
			$image = $image['url'];
		}
		else
		{
			$image = IMAGE_PATH."/award.png";
		}		
		$GlobalAwardsList .= "<img src=\"$image\" alt=\"$a->ribbonName\" title=\"$a->ribbonName\"> ";
	}
	if ($ribbonList != '' || $GlobalAwardsList != '')
	{
?>

<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Ribbons</h4>
<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
	<p class="flex flex-wrap px-4 py-3 items-center text-sm text-gray-600 dark:text-gray-400">
		<?php echo $ribbonList; ?>
	</p>
</div>

<h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Global Awards</h4>
<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
	<p class="flex flex-wrap px-4 py-3 items-center text-sm text-gray-600 dark:text-gray-400">
		<?php echo $GlobalAwardsList; ?>
	</p>
</div>

<?php
	}
?> 
<!-- end playerinfo_general.php -->