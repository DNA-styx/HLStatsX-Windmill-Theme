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

	require('livestats.php');     
    $db->query("SELECT name FROM hlstats_Games WHERE code='$game'");
    if ($db->num_rows() < 1) error("No such game '$game'.");
    
    list($gamename) = $db->fetch_row();
    $db->free_result();

    pageHeader(array($gamename), array($gamename => ''));
    
    $server_id = 1;

    if ((isset($_GET['server_id'])) && (is_numeric($_GET['server_id']))) {
        $server_id = valid_request($_GET['server_id'], true);
    } else {
        error("Invalid server ID provided.", 0);
        pageFooter();
        die();
    }

    $query= "
            SELECT
                SUM(kills),
                SUM(headshots),
                count(serverId)     
            FROM
                hlstats_Servers
			WHERE 
				serverId='$server_id'
	";

	$result = $db->query($query);
	list($total_kills, $total_headshots) = $db->fetch_row($result);
        
	$query= "
        SELECT
            serverId,
            name,
            IF(publicaddress != '',
                IF(publicaddress LIKE '%:%', publicaddress, CONCAT(publicaddress, ':', port)),
                concat(address, ':', port)
            ) AS addr,
            statusurl,
            kills,
            players,
            rounds, suicides, 
            headshots, 
            bombs_planted, 
            bombs_defused, 
            ct_wins, 
            ts_wins, 
            ct_shots, 
            ct_hits, 
            ts_shots, 
            ts_hits,      
            act_players,                                
            max_players,
            act_map,
            map_started,
            map_ct_wins,
            map_ts_wins,
            game                 
        FROM
            hlstats_Servers
        WHERE
            serverId='$server_id'
	";

	$db->query($query);
	$servers   = array();
	$servers[] = $db->fetch_array();
        
	
	display_page_title('Server Live View');
	$i=0;
	for ($i=0; $i<count($servers); $i++)
	{
		$rowdata = $servers[$i]; 
	
		$server_id = $rowdata['serverId'];
		$game = $rowdata['game'];
	
		$addr = $rowdata['addr'];          
		$kills     = $rowdata['kills'];
		$headshots = $rowdata['headshots'];
		$player_string = $rowdata['act_players']."/".$rowdata['max_players'];
		$map_teama_wins = $rowdata['map_ct_wins'];
		$map_teamb_wins = $rowdata['map_ts_wins'];
?>
<!-- start servers.php -->
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
		<table class="w-full whitespace-no-wrap">
			<thead>
				<tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
					<td rowspan="2" class="px-4 py-3">&nbsp;Name</td>
					<td class="px-4 py-3" style="text-align:center;">&nbsp;Map</td>
					<td class="px-4 py-3" style="text-align:center;">&nbsp;Played</td>
					<td class="px-4 py-3" style="text-align:center;">&nbsp;Players</td>
					<td class="px-4 py-3" style="text-align:center;">&nbsp;Kills</td>
					<td class="px-4 py-3" style="text-align:center;">&nbsp;Headshots</td>
					<td class="px-4 py-3" style="text-align:center;">&nbsp;Hpk</td>
				</tr>
			</thead>
			<tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
				<tr class="text-gray-700 dark:text-gray-400">
					<td rowspan="2" class="flex px-4 py-3 items-center">
						<?php
				$image = getImage("/games/$game/game");
				echo '<img src="';
				if ($image)
					echo $image['url'];
				else
					echo IMAGE_PATH . '/game.gif';
				echo "\" alt=\"$game\">\n				";
				echo htmlspecialchars($rowdata['name']) . " <span class=\"windmill-text-link\"><a href=\"steam://connect/$addr\">(Join)</a></span>\n";
								?>
					</td>
					<td class="px-4 py-3" style="text-align:center;"><?php echo $rowdata['act_map']; ?></td>
					<td class="px-4 py-3" style="text-align:center;"><?php
				$stamp = $rowdata['map_started']==0?0:time() - $rowdata['map_started'];
				$hours = sprintf("%02d", floor($stamp / 3600));
				$min   = sprintf("%02d", floor(($stamp % 3600) / 60));
				$sec   = sprintf("%02d", floor($stamp % 60)); 
				echo $hours.":".$min.":".$sec;
							?></td>
					<td class="px-4 py-3" style="text-align:center;"><?php echo $player_string; ?></td>
					<td class="px-4 py-3" style="text-align:center;"><?php echo number_format($kills); ?></td>
					<td class="px-4 py-3" style="text-align:center;"><?php echo number_format($headshots); ?></td>
					<td class="px-4 py-3" style="text-align:center;"><?php
				if ($kills>0)
					echo sprintf("%.4f", ($headshots/$kills));
				else  
					echo sprintf("%.4f", 0);
							?></td>
				</tr>
			</tbody>
		</table>       

		<table class="w-full whitespace-no-wrap">       
<?php
		printserverstats($server_id);
	}  //for servers
?>
		</table>       
 	</div>
</div>

<?php display_page_subtitle('Server Load History'); ?>

<div class="grid gap-6 mb-8 md:grid-cols-2">
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
		<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">24 Hours</h4>
		<a href="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=1">
			<img src="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=1" alt="24h View">
		</a>
	</div>
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">7 Days</h4>
		<a href="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=2">
	        <img src="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=2" alt="Last Week">
		</a>
    </div>
</div>

<div class="grid gap-6 mb-8 md:grid-cols-2">
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
	<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">30 Days</h4>
		<a href="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=3">
			<img src="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=3" alt="Last Month">
		</a>
	</div>
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
        <h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">12 Months</h4>
		<a href="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=4">
	        <img src="show_graph.php?type=0&amp;game=<?php echo $game; ?>&amp;width=870&amp;height=200&amp;server_id=<?php echo $server_id ?>&amp;bgcolor=<?php echo $g_options['graphbg_load']; ?>&amp;color=<?php echo $g_options['graphtxt_load']; ?>&amp;range=4" alt="Last Year">
		</a>
	</div>
</div>
<!-- end servers.php -->