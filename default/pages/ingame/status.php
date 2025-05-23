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

	require(PAGE_PATH.'/livestats.php');

	$server_id = 1;
	if (isset($_GET['server_id']) && is_numeric($_GET['server_id'])) {
		$server_id = valid_request($_GET['server_id'], true);
	}

    $query= "
			SELECT
				count(*)
			FROM
				hlstats_Players
			WHERE 
				game='$game'
	";

	$result = $db->query($query);
	list($total_players) = $db->fetch_row($result);

    $query= "
			SELECT
				SUM(kills),
				SUM(headshots),
				count(serverId)		
			FROM
				hlstats_Servers
			WHERE 
				game='$game'
	";

	$result = $db->query($query);
	list($total_kills, $total_headshots, $total_servers) = $db->fetch_row($result);

    $query= "
			SELECT
				serverId,
				name,
				IF(publicaddress != '',
					publicaddress,
					concat(address, ':', port)
				) AS addr,
				".//"statusurl,"
				"kills,
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
				serverId='".$server_id."'
			ORDER BY
				name ASC,
				addr ASC
		";
		$db->query($query);
		$servers = array();

		while ($rowdata = $db->fetch_array()) {
			$servers[] = $rowdata;
		}

		$i=0;
		for ($i=0; $i<count($servers); $i++) {
			$rowdata = $servers[$i]; 		
			$server_id = $rowdata['serverId'];		
			$c = ($i % 2) + 1;
			$addr = $rowdata["addr"];
			$kills     = $rowdata['kills'];
			$headshots = $rowdata['headshots'];
			$player_string = $rowdata['act_players']."/".$rowdata['max_players'];
			$map_teama_wins = $rowdata['map_ct_wins'];
			$map_teamb_wins = $rowdata['map_ts_wins'];
?>

	<table class="data-table">
		<tr class="data-table-head">
			<td style="width:37%;" class="fSmall">&nbsp;Server</td>
			<td style="width:23%;" class="fSmall">&nbsp;Address</td>
			<td style="width:6%;text-align:center;" class="fSmall">&nbsp;Map</td>
			<td style="width:6%;text-align:center;" class="fSmall">&nbsp;Played</td>
			<td style="width:10%;text-align:center;" class="fSmall">&nbsp;Players</td>
			<td style="width:6%;text-align:center;" class="fSmall">&nbsp;Kills</td>
			<td style="width:6%;text-align:center;" class="fSmall">&nbsp;Headshots</td>
			<td style="width:6%;text-align:center;" class="fSmall">&nbsp;Hpk</td>
		</tr>
		<tr class="bg1" valign="middle">
			<td class="fSmall"><?php
				echo '<strong>'.$rowdata['name'].'</strong>';
			?></td>
			<td class="fSmall"><?php
				echo $addr;
			?></td>
			<td style="text-align:center;" class="fSmall"><?php
				echo $rowdata['act_map'];
			?></td>
			<td style="text-align:center;" class="fSmall"><?php
				$stamp = time()-$rowdata['map_started'];
				$hours = sprintf('%02d', floor($stamp / 3600));
				$min   = sprintf('%02d', floor(($stamp % 3600) / 60));
				$sec   = sprintf('%02d', floor($stamp % 60)); 
				echo "$hours:$min:$sec";
			?></td>
			<td style="text-align:center;" class="fSmall"><?php
				echo $player_string;
			?></td>
			<td style="text-align:center;" class="fSmall"><?php
				echo number_format($kills);
			?></td>
			<td style="text-align:center;" class="fSmall"><?php
				echo number_format($headshots);
			?></td>
			<td style="text-align:center;" class="fSmall"><?php
				if ($kills>0)
					echo sprintf('%.4f', ($headshots/$kills));
				else  
				  echo sprintf('%.4f', 0);
			?></td>
		</tr>
	</table>        

<?php
	printserverstats($server_id);

	}  // for servers
?>		
