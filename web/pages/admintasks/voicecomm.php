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

	if ( !defined('IN_HLSTATS') ) { die('Do not access this file directly.'); }
	if ($auth->userdata['acclevel'] < 80) die ('Access denied!');

	$edlist = new EditList('serverId', 'hlstats_Servers_VoiceComm', '');
	$edlist->columns[] = new EditListColumn('name', 'Server Name', 45, true, 'text', '', 64);
	$edlist->columns[] = new EditListColumn('addr', 'Address (see above)', 20, true, 'text', '', 64);
	$edlist->columns[] = new EditListColumn('password', 'Password', 20, false, 'text', '', 64);
	$edlist->columns[] = new EditListColumn('UDPPort', 'TS3: TCP Port<br>Otherwise: 1', 6, false, 'text', '1', 64);
	$edlist->columns[] = new EditListColumn('queryPort', 'TS3: Query Port<br>Otherwise: 1', 6, true, 'text', '1', 64);
	$edlist->columns[] = new EditListColumn('descr', 'Public Notes', 40, false, 'text', '', 64);
	$edlist->columns[] = new EditListColumn('serverType', 'Server Type', 20, true, 'select', '0/Teamspeak;1/Ventrilo;2/Teamspeak3;3/Discord;4/Mumble;5/Steam Group');
	
	if ($_POST)
	{
		if ($edlist->update())
			message('success', 'Operation successful.');
		else
			message('warning', $edlist->error());
	}
	
?>
<!-- start voice comms page -->
<div class="ml-6 mb-6">
<p class="text-l text-gray-600 dark:text-gray-400">
Address:<br><b>TS3</b>: Server IP or Hostname<br><b>Discord</b>: Invite Code<br><b>Mumble</b>: Link to JSON CVP<br><b>Steam Group</b>: Group's groupID64<br>
<br>
<a href="https://github.com/DNA-styx/HLStatsX-Windmill-Theme/wiki">See Wiki for further details</a>
</p>
</div>
<?php
	
	$result = $db->query("
		SELECT
			serverId,
			name,
			addr,
			password,
			UDPPort,
			queryPort,
			descr,
			serverType
		FROM
			hlstats_Servers_VoiceComm
		ORDER BY
			serverType,
			name
	");
	
	$edlist->draw($result);
?>

<input type="submit" value="  Apply  " class="<?php echo windmill_button_class(); ?>">
<!-- end voice comms page -->