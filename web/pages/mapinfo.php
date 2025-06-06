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

	// Map Details
	
	$map = valid_request($_GET['map'], false) or error('No map specified.');
	
	$db->query("SELECT name FROM hlstats_Games WHERE code='$game'");
	if ($db->num_rows() != 1) {
		error('Invalid or no game specified.');
	} else {
		list($gamename) = $db->fetch_row();
	}

	pageHeader(
		array($gamename, 'Map Details', $map),
		array(
			$gamename=>$g_options['scripturl'] . "?game=$game",
			'Map Statistics' => $g_options['scripturl'] . "?mode=maps&game=$game",
			'Map Details' => ''
		),
		$map
	);

	$table = new Table(
		array(
			new TableColumn(
				'killerName',
				'Player',
				'width=50&align=left&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k') 
			),
			new TableColumn(
				'frags',
				"Kills on $map",
				'width=25&align=right'
			),
			new TableColumn(
				'headshots',
				'Headshots',
				'width=15&align=right'
			),
			new TableColumn(
				'hpk',
				'Hpk',
				'width=5&align=right'
			),
			
			
		),
		'killerId', // keycol
		'frags', // sort_default
		'killerName', // sort_default2
		true, // showranking
		50 // numperpage
	);
	
	$result = $db->query("
		SELECT
			hlstats_Events_Frags.killerId,
			hlstats_Players.lastName AS killerName,
			hlstats_Players.flag as flag,
			COUNT(hlstats_Events_Frags.map) AS frags,
			SUM(hlstats_Events_Frags.headshot=1) as headshots,
			IFNULL(SUM(hlstats_Events_Frags.headshot=1) / Count(hlstats_Events_Frags.map), '-') AS hpk
		FROM
			hlstats_Events_Frags,
			hlstats_Players		
		WHERE
			hlstats_Players.playerId = hlstats_Events_Frags.killerId
			AND hlstats_Events_Frags.map='$map'
			AND hlstats_Players.game='$game'
			AND hlstats_Players.hideranking<>'1'
		GROUP BY
			hlstats_Events_Frags.killerId
		ORDER BY
			$table->sort $table->sortorder,
			$table->sort2 $table->sortorder
		LIMIT $table->startitem,$table->numperpage
	");
	
	$resultCount = $db->query("
		SELECT
			COUNT(DISTINCT hlstats_Events_Frags.killerId),
			SUM(hlstats_Events_Frags.map='$map')
		FROM
			hlstats_Events_Frags,
		  hlstats_Servers
		WHERE
			hlstats_Servers.serverId = hlstats_Events_Frags.serverId
			AND hlstats_Events_Frags.map='$map'
			AND hlstats_Servers.game='$game'
	");
	
	list($numitems, $totalkills) = $db->fetch_row($resultCount);


	// figure out URL and absolute path of image
	if ($mapimg = getImage("/games/$game/maps/$map"))
	{
		$mapimg = $mapimg['url'];
	}
	elseif ($mapimg = getImage("/games/$realgame/maps/$map"))
	{
		$mapimg = $mapimg['url'];
	}
	else
	{
		$mapimg = IMAGE_PATH."/nomap.png";
		$mapimg_missing_message = 1;
	}

	$heatmap = getImage("/games/$game/heatmaps/$map-kill");
	$heatmapthumb = getImage("/games/$game/heatmaps/$map-kill-thumb");

?>
<!-- start mapinfo.php -->
<?php echo display_page_title('Map: ' . $map); ?>

<div class="grid gap-6 mb-8 md:grid-cols-2">

	<!-- Card -->
	<div class="items-center min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
			<?php echo "<a href=\"$mapimg\"><img style=\"border-radius: 6px;\" src=\"$mapimg\" alt=\"$map\" width=\"300\"></a>"; ?>
<?php		

	if (isset($_SESSION['loggedin']) && $mapimg_missing_message ) {
		echo "<span class=\"text-xs text-red-600 dark:text-red-400\">Upload missing images to " . IMAGE_PATH . "/games/$game/maps</span>";
		}

	if ($g_options['map_dlurl'])
		{
			echo "			<p class=\"text-gray-600 dark:text-gray-400\">";
			$map_dlurl = str_replace("%MAP%", $map, $g_options['map_dlurl']);
			$map_dlurl = str_replace("%GAME%", $game, $map_dlurl);
			$mapdlheader = @get_headers($map_dlurl);
			if (preg_match("|200|", $mapdlheader[0])) {
				echo "<a href=\"$map_dlurl\">Download this map...</a></p>";
			}
		}else{
			echo "&nbsp;</p>";
		}
?>
	</div>

<?php
	if ($heatmap)
	{
?>
	<!-- Card -->
	<div class="flex items-center min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
		<p class="text-gray-600 dark:text-gray-400">
<?php
	echo "<a href=\"" . $heatmap['url'] . "\" rel=\"boxed\" title=\"Heatmap: $map\"><br><img src=\"" . $heatmapthumb['url'] . "\" alt=\"$map\"></a>";
?>
		</p>
	</div>
<?php
}
?>

</div>

<?php $table->draw($result, $numitems, 95, 'center'); ?>

<?php 
/*
?>
<div class="block">
<?php // figure out URL and absolute path of image
	if ($mapimg = getImage("/games/$game/maps/$map"))
	{
		$mapimg = $mapimg['url'];
	}
	elseif ($mapimg = getImage("/games/$realgame/maps/$map"))
	{
		$mapimg = $mapimg['url'];
	}
	else
	{
		$mapimg = IMAGE_PATH."/nomap.png";
	}
	
	$heatmap = getImage("/games/$game/heatmaps/$map-kill");
	$heatmapthumb = getImage("/games/$game/heatmaps/$map-kill-thumb");

	if ($mapimg || $g_options['map_dlurl'] || $heatmap)
	{
?>
	<div class="subblock">
		<div style="float:left;width:75%;">
			<?php $table->draw($result, $numitems, 100, 'center'); ?>
		</div>
		<div style="float:right;">
<?php
			if ($mapimg)
			{
				echo "<img src=\"$mapimg\" alt=\"$map\">";
			}

			if ($g_options['map_dlurl'])
			{
				$map_dlurl = str_replace("%MAP%", $map, $g_options['map_dlurl']);
				$map_dlurl = str_replace("%GAME%", $game, $map_dlurl);
				$mapdlheader = @get_headers($map_dlurl);
				if (preg_match("|200|", $mapdlheader[0])) {
					echo "<p><a href=\"$map_dlurl\">Download this map...</a></p>";
				}
			}

			if ($heatmap)
			{
				echo "<a href=\"" . $heatmap['url'] . "\" rel=\"boxed\" title=\"Heatmap: $map\"><br><img src=\"" . $heatmapthumb['url'] . "\" alt=\"$map\"></a>";
			}
?>
		</div>
	</div>
<?php
	}
	else
	{
		$table->draw($result, $numitems, 95, 'center');
	}
?>

</div>
<?php
*/
?>