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

    $server_id = 1;
	if (isset($_GET['server_id']) && is_numeric($_GET['server_id'])) {
		$server_id = valid_request($_GET['server_id'], true);
	}

	display_page_title('Help');
	display_ingame_menu();

?>
<?php display_page_subtitle('Commands display the results ingame'); ?>
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
	<table class="w-full" style="white-space:nowrap;">
		<tbody class="bg-gray-50 divide-y dark:divide-gray-700 dark:bg-gray-800">
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>rank [skill, points, place (to all)]</td>
			<td>=</td>
			<td>Current position</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>kpd [kdratio, kdeath (to all)]</td>
			<td>=</td>
			<td>Total player statistics</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>session [session_data (to all)]</td>
			<td>=</td>
			<td>Current session statistics</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>next</td>
			<td>=</td>
			<td>Players ahead in the ranking.</td>
		</tr>
		</tbody>
	</table>
	</div>
	<div class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">&nbsp;</div>
</div>

<?php display_page_subtitle('Commands display the results in window'); ?>
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
	<table class="w-full" style="white-space:nowrap;">
		<tbody class="bg-gray-50 divide-y dark:divide-gray-700 dark:bg-gray-800">
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>load</td>
			<td>=</td>
			<td>Statistics from all servers</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>status</td>
			<td>=</td>
			<td>Current server status</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>servers</td>
			<td>=</td>
			<td>List of all participating servers</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>top20 [top5, top10]</td>
			<td>=</td>
			<td>Top-Players</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>clans</td>
			<td>=</td>
			<td>Clan ranking</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>cheaters</td>
			<td>=</td>
			<td>Banned players</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>statsme</td>
			<td>=</td>
			<td>Statistic summary</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>weapons [weapon]</td>
			<td>=</td>
			<td>Weapons usage</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>accuracy</td>
			<td>=</td>
			<td>Weapons accuracy</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>targets [target]</td>
			<td>=</td>
			<td>Targets hit positions</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>kills [kill, player_kills]</td>
			<td>=</td>
			<td>Kill statistics (5 or more kills)</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>actions [action]</td>
			<td>=</td>
			<td>Server actions summary</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>help [cmd, cmds, commands]</td>
			<td>=</td>
			<td>Help screen</td>
		</tr>
		</tbody>
	</table>
	</div>
	<div class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">&nbsp;</div>
</div>

<?php display_page_subtitle('Commands to set your user options'); ?>
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
	<table class="w-full" style="white-space:nowrap;">
		<tbody class="bg-gray-50 divide-y dark:divide-gray-700 dark:bg-gray-800">
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>hlx_auto clear|start|end|kill command</td>
			<td>=</td>
			<td>Auto-Command on specific event (on death, roundstart, roundend)</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>hlx_display 0|1</td>
			<td>=</td>
			<td>Enable or disable displaying console events.</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>hlx_chat 0|1</td>
			<td>=</td>
			<td>Enable or disable the displaying of global chat events(if enabled).</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>/hlx_set realname|email|homepage [value]</td>
			<td>=</td>
			<td>(Type in chat, not console) Sets your player info.</td>
		</tr>
		<tr class="text-xs text-gray-700 dark:text-gray-400">
			<td>/hlx_hideranking</td>
			<td>=</td>
			<td>(Type in chat, not console) Makes you invisible on player rankings, unranked.</td>
		</tr>
		</tbody>
	</table>
	</div>
	<div class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">&nbsp;</div>
</div>
<br>