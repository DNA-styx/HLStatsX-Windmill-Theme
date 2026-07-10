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

	if ($auth->userdata['acclevel'] < 80) {
        die ('Access denied!');
	}

?>
	
	<div style="width:60%;height:50px;border:0;padding:0;margin:auto;background-color:#F00;text-align:center;color:#FFF;font-size:medium;font-weight:bold;vertical-align:middle;">
		Options with an asterisk (*) beside them require a restart of the <a href="hlstats.php?mode=admin&task=tools_perlcontrol">perl daemon</a> to fully take effect.
	</div>

<?php

	class OptionGroup
	{
		var $title = '';
		var $options = array();

		function __construct($title)
		{
			$this->title = $title;
		}

		function draw ()
		{
?>
	<h4 class="mb-4 mt-4 text-lg font-semibold text-gray-600 dark:text-gray-300"><?php echo $this->title; ?></h4>
	<table class="data-table" style="width:75%">
		<?php
			foreach ($this->options as $opt)
			{
				$opt->draw();
			}
?>
	</table>
<?php
		}
		
		function update ()
		{
			global $db;
			
			foreach ($this->options as $opt)
			{
				if (($this->title == 'Fonts') || ($this->title == 'General')) {
					$optval = $_POST[$opt->name];
					$search_pattern  = array('/script/i', '/;/', '/%/');
					$replace_pattern = array('', '', '');
					$optval = preg_replace($search_pattern, $replace_pattern, $optval);
				} else {
					$optval = valid_request($_POST[$opt->name], false);
 	 			}
				
				$result = $db->query("
					SELECT
						value
					FROM
						hlstats_Options
					WHERE
						keyname='$opt->name'
				");
				
				if ($db->num_rows($result) == 1)
				{
					$result = $db->query("
						UPDATE
							hlstats_Options
						SET
							value='$optval'
						WHERE
							keyname='$opt->name'
					");
				}
				else
				{
					$result = $db->query("
						INSERT INTO
							hlstats_Options
							(
								keyname,
								value
							)
						VALUES
						(
							'$opt->name',
							'$optval'
						)
					");
				}
			}
		}
	}

	class Option
	{
		var $name;
		var $title;
		var $type;

		function __construct($name, $title, $type)
		{
			$this->name = $name;
			$this->title = $title;
			$this->type = $type;
		}

		function draw()
		{
			global $g_options, $optiondata, $db;
			
?>
					<tr class="bg1" style="vertical-align:middle";>
						<td class="fNormal" style="width:45%;"><span class="text-gray-700 dark:text-gray-400"><?php
			echo $this->title . ":";
						?></span></td>
						<td style="width:55%;"><?php
			switch ($this->type)
			{
				case 'textarea':
					echo "<textarea name=\"$this->name\" cols=\"35\" rows=\"4\" wrap=\"virtual\">";
					echo html_entity_decode($optiondata[$this->name]);
					echo '</textarea>';
					break;
					
				case 'styles':
					echo "<select name=\"$this->name\" style=\"width: 300px\" class=\"block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray\">";
					$d = dir('styles');
					while (false !== ($e = $d->read()))  {
						if (is_file("styles/$e") && ($e != '.') && ($e != '..')) {
							$ename = ucwords(strtolower(str_replace(array('_','.css'), array(' ',''), $e)));
							$sel = '';
							if ($e==$g_options['style'])
								$sel = 'selected="selected"';
							echo "<option value=\"$e\"$sel>$ename</option>";
						} 
					}
					$d->close();
					echo '</select>';
					break;
				
				case 'select':
					echo "<select name=\"$this->name\" style=\"width: 300px\" class=\"block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray\">";
					$result = $db->query("SELECT `value`,`text` FROM hlstats_Options_Choices WHERE keyname='$this->name' ORDER BY isDefault desc");
					while ($rowdata = $db->fetch_array($result)) {
						if ($rowdata['value'] == $optiondata[$this->name]) {
							echo '<option value="'.$rowdata['value'].'" selected="selected">'.$rowdata['text'];
						} else {
							echo '<option value="'.$rowdata['value'].'">'.$rowdata['text'];
						}
					}
					echo '</select>';
					break;

				case 'select-disabled':
					echo "<select disabled name=\"$this->name\" style=\"width: 300px\" class=\"cursor-not-allowed block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray\">";
					$result = $db->query("SELECT `value`,`text` FROM hlstats_Options_Choices WHERE keyname='$this->name' ORDER BY isDefault desc");
					while ($rowdata = $db->fetch_array($result)) {
						if ($rowdata['value'] == $optiondata[$this->name]) {
							echo '<option value="'.$rowdata['value'].'" selected="selected">'.$rowdata['text'];
						} else {
							echo '<option value="'.$rowdata['value'].'">'.$rowdata['text'];
						}
					}
					echo '</select>';
					break;

				default:
					echo "<input type=\"text\" name=\"$this->name\" style=\"width: 300px\" size=\"35\" value=\"";
					echo html_entity_decode($optiondata[$this->name]);
					echo '" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" maxlength="255" />';
			}
						?></td>
					</tr>
<?php
		}
	}

	$optiongroups = array();

	$optiongroups[0] = new OptionGroup('Header');
	$optiongroups[0]->options[] = new Option('sitename', 'Site Name</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Appears top left on every page', 'text');
	$optiongroups[0]->options[] = new Option('siteurl', 'Site URL</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Site Name link', 'text');
	$optiongroups[0]->options[] = new Option('contact', 'Contact/Rules URL</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">No mail address or URLs with query-strings', 'text');
	$optiongroups[0]->options[] = new Option('bannerdisplay', 'Show Banner', 'select');
	$optiongroups[0]->options[] = new Option('bannerfile', 'Banner file name</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Save file in in hlstatsimg folder or use full URL to file', 'text');
	// $optiongroups[0]->options[] = new Option('slider', '<s>Enable AJAX gliding server list (accordion effect) on homepage of each game (only affects games with more than one server)</s> ** Not used', 'select-disabled');
	$optiongroups[0]->options[] = new Option('display_gamelist', 'Display games as icons?</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Only affects sites with more than one game', 'select');

	$optiongroups[1] = new OptionGroup('Footer');
	$optiongroups[1]->options[] = new Option('showqueries', 'Show Database Stats?</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">"Executed X queries, generated this page in Y Seconds."?', 'select');

	$optiongroups[2] = new OptionGroup('Left Menu');
	$optiongroups[2]->options[] = new Option('nav_globalchat', 'Player Chat Link', 'select');
	$optiongroups[2]->options[] = new Option('nav_cheaters', 'Banned Players Link', 'select');

	$optiongroups[3] = new OptionGroup('Links Drop Down');
	$optiongroups[3]->options[] = new Option('sourcebans_address', 'Ban Website URL</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Enter the relative or full path to your Bans web site, if you have one. Ex: http://www.yoursite.com/bans/ or /bans/', 'text');
	$optiongroups[3]->options[] = new Option('forum_address', 'Forum/Discord URL</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Enter the relative or full path to your forum/message board, if you have one. Ex: http://www.yoursite.com/forum/ or /forum/', 'text');
	// $optiongroups[3]->options[] = new Option('show_weapon_target_flash', '<s>Show hitbox flash animation instead of plain html table for games with accuracy tracking (on supported games)</s> ** Not used', 'select-disabled');
	$optiongroups[3]->options[] = new Option('show_server_load_image', 'Load summaries from all monitored servers **Not used?', 'select');
	
	$optiongroups[4] = new OptionGroup('GeoIP Data & Map');
	$optiongroups[4]->options[] = new Option('countrydata', 'Features requiring GeoIP data', 'select');
	$optiongroups[4]->options[] = new Option('show_google_map', 'Player Maps', 'select');
	$optiongroups[4]->options[] = new Option('google_map_region', 'Maps Region', 'select');
	// $optiongroups[4]->options[] = new Option('google_map_type', '<s>Google Maps Type</s> ** Not used', 'select-disabled');
	$optiongroups[4]->options[] = new Option('UseGeoIPBinary', '<span class="text-red-600 dark:text-red-400">*</span> Choose whether to use GeoCityLite data loaded into mysql database or from binary file. (If binary, GeoLiteCity.dat goes in perl/GeoLiteCity and Geo::IP::PurePerl module is required', 'select');

	$optiongroups[7] = new OptionGroup('Awards');
	$optiongroups[7]->options[] = new Option('gamehome_show_awards', 'Daily award winners on Game Frontpage', 'select');
	$optiongroups[7]->options[] = new Option('awarddailycols', 'Daily Awards: columns count ** Not used?', 'text');
	$optiongroups[7]->options[] = new Option('awardglobalcols', 'Global Awards: columns count ** Not used?', 'text');
	$optiongroups[7]->options[] = new Option('awardrankscols', 'Player Ranks: columns count ** Not used?', 'text');
	$optiongroups[7]->options[] = new Option('awardribbonscols', 'Ribbons: columns count ** Not used?', 'text');

	// $optiongroups[10] = new OptionGroup('<s>Hit counter settings</s>');
	// $optiongroups[10]->options[] = new Option('counter_visit_timeout', '<s>Visit cookie timeout in minutes</s> ** Not used', 'text');
	// $optiongroups[10]->options[] = new Option('counter_visits', '<s>Current Visits</s> ** Not used', 'text');
	// $optiongroups[10]->options[] = new Option('counter_hits', '<s>Current Page Hits</s> ** Not used', 'text');

	$optiongroups[15] = new OptionGroup('Player Signatures');
	$optiongroups[15]->options[] = new Option('sigbackground', 'Background Used: Enter numbers 1-11, random or leave blank)</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Look in hlstatsimg->sig folder to see background choices', 'text');
	$optiongroups[15]->options[] = new Option('modrewrite', 'Use modrewrite?</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">To make forum signature image compatible with more forum types. To utilize this, you <strong>must</strong> have modrewrite enabled on your webserver and add the following text to a .htaccess file in the directory of hlstats.php<br /><br /><textarea class="windmill-textarea block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="3" cols="72" style="overflow:hidden;" disabled>
RewriteEngine On
RewriteRule sig-(.*)-(.*).png$ sig.php?player_id=$1&background=$2 [L]</textarea>', 'select');

	$optiongroups[20] = new OptionGroup('Map Paths');
	$optiongroups[20]->options[] = new Option('map_dlurl', 'Map Download URL</span><br /><span class="text-xs text-gray-600 dark:text-gray-400"><span class="fSmall">(%MAP% = map, %GAME% = gamecode)</span>. Leave blank to suppress download link.', 'text');

	$optiongroups[30] = new OptionGroup('Visual Style');
	$optiongroups[30]->options[] = new Option('graphbg_load', 'Server Load graph: background color hex# (RRGGBB)', 'text');
	$optiongroups[30]->options[] = new Option('graphtxt_load', 'Server Load graph: text color# (RRGGBB)', 'text');
	$optiongroups[30]->options[] = new Option('graphbg_trend', 'Player Trend graph: background color hex# (RRGGBB)', 'text');
	$optiongroups[30]->options[] = new Option('graphtxt_trend', 'Player Trend graph: text color hex# (RRGGBB)', 'text');
	$optiongroups[30]->options[] = new Option('style', 'Stylesheet filename to use', 'styles');
	// $optiongroups[30]->options[] = new Option('display_style_selector', '<s>Display Style Selector?</s> ** Not used', 'select-disabled');
	$optiongroups[30]->options[] = new Option('playerinfo_tabs', 'Use tabs in playerinfo to show/hide sections current page or just show all at once', 'select');
	
	
	$optiongroups[35] = new OptionGroup('Ranking');
	$optiongroups[35]->options[] = new Option('rankingtype', '<span class="text-red-600 dark:text-red-400">*</span> Ranking type', 'select');
	$optiongroups[35]->options[] = new Option('MinActivity', '<span class="text-red-600 dark:text-red-400">*</span> HLstatsX will automatically hide players which have no event more days than this value. (Default 28 days)', 'text');
	
	$optiongroups[40] = new OptionGroup('Daemon');
	$optiongroups[40]->options[] = new Option('Mode', '<span class="text-red-600 dark:text-red-400">*</span> Player-tracking mode.</span><br /><span class="text-xs text-gray-600 dark:text-gray-400"><ul><LI><b>Steam ID</b>     - Recommended for public Internet server use. Players will be tracked by Steam ID.<LI><b>Player Name</b>  - Useful for shared-PC environments, such as Internet cafes, etc. Players will be tracked by nickname. <LI><b>IP Address</b>        - Useful for LAN servers where players do not have a real Steam ID. Players will be tracked by IP Address. </UL>', 'select');
	$optiongroups[40]->options[] = new Option('AllowOnlyConfigServers', '<span class="text-red-600 dark:text-red-400">*</span> Allow only servers set up in admin panel to be tracked. Other servers will NOT automatically added and tracked! This is a big security thing', 'select');
	$optiongroups[40]->options[] = new Option('DeleteDays', '<span class="text-red-600 dark:text-red-400">*</span> History Autodelete</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">HLstatsX will automatically delete history events from the events tables when they are over this many days old. This is important for performance reasons. Set lower if you are logging a large number of game servers or find the load on the MySQL server is too high', 'text');
	$optiongroups[40]->options[] = new Option('DNSResolveIP', '<span class="text-red-600 dark:text-red-400">*</span> Resolve player IP addresses to hostnames.</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Requires a working DNS setup (on the server running hlstats.pl)', 'select');
	$optiongroups[40]->options[] = new Option('DNSTimeout', '<span class="text-red-600 dark:text-red-400">*</span> Time, in seconds, to wait for DNS queries to complete before cancelling DNS resolves</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">You may need to increase this if on a slow connection or if you find a lot of IPs are not being resolved; however, hlstats.pl cannot be parsing log data while waiting for an IP to resolve', 'text');
	$optiongroups[40]->options[] = new Option('MailTo', '<span class="text-red-600 dark:text-red-400">*</span> E-mail address to mail database errors to', 'text');
	$optiongroups[40]->options[] = new Option('MailPath', '<span class="text-red-600 dark:text-red-400">*</span> Path to the mail program</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">usually /usr/sbin/sendmail on webhosts', 'text');
	$optiongroups[40]->options[] = new Option('Rcon', '<span class="text-red-600 dark:text-red-400">*</span> Allow HLstatsX to send Rcon commands to the game servers', 'select');
	$optiongroups[40]->options[] = new Option('RconIgnoreSelf', '<span class="text-red-600 dark:text-red-400">*</span> Ignore (do not log) Rcon commands originating from the same IP as the server being rcon-ed</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">useful if you run any kind of monitoring script which polls the server regularly by rcon', 'select');
	$optiongroups[40]->options[] = new Option('RconRecord', '<span class="text-red-600 dark:text-red-400">*</span> Record Rcon commands to the Admin event table</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">This can be useful to see what your admins are doing, but if you run programs like PB it can also fill your database up with a lot of useless junk', 'select');
	$optiongroups[40]->options[] = new Option('UseTimestamp', '<span class="text-red-600 dark:text-red-400">*</span> If no (default), use the current time on the database server for the timestamp when recording events.<br>If yes, use the timestamp provided on the log data.</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Unless you are processing old log files on STDIN or your game server is in a different timezone than webhost, you probably want to set this to no', 'select');
	$optiongroups[40]->options[] = new Option('TrackStatsTrend', '<span class="text-red-600 dark:text-red-400">*</span> Save how many players, kills etc, are in the database each day and give access to graphical statistics', 'select');
	// $optiongroups[40]->options[] = new Option('GlobalBanning', '*Make player bans available on all participating servers. Players who were banned permanently are automatic hidden from rankings', 'select');
	$optiongroups[40]->options[] = new Option('LogChat', '<span class="text-red-600 dark:text-red-400">*</span> Log player chat to database', 'select');
	$optiongroups[40]->options[] = new Option('LogChatAdmins', '<span class="text-red-600 dark:text-red-400">*</span> Log admin chat to database', 'select');
	$optiongroups[40]->options[] = new Option('GlobalChat', '<span class="text-red-600 dark:text-red-400">*</span> Broadcast chat messages through all particapting servers</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">To all, none, or admins only', 'select');
	
	$optiongroups[50] = new OptionGroup('Point Calculations');
	$optiongroups[50]->options[] = new Option('SkillMaxChange', '<span class="text-red-600 dark:text-red-400">*</span> Maximum number of skill points a player will gain from each frag. Default 25', 'text');
	$optiongroups[50]->options[] = new Option('SkillMinChange', '<span class="text-red-600 dark:text-red-400">*</span> Minimum number of skill points a player will gain from each frag. Default 2', 'text');
	$optiongroups[50]->options[] = new Option('PlayerMinKills', '<span class="text-red-600 dark:text-red-400">*</span> Number of kills a player must have before receiving regular points. Default 50</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">Before this threshold is reached, the killer and victim will only gain/lose the minimum point value', 'text');
	$optiongroups[50]->options[] = new Option('SkillRatioCap', '<span class="text-red-600 dark:text-red-400">*</span> Cap killer\'s gained skill with ratio using *XYZ*SaYnt\'s method</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">"designed such that an excellent player will have to get about a 2:1 ratio against noobs to hold steady in points"', 'select');

	$optiongroups[60] = new OptionGroup('Daemon Proxy');
	$optiongroups[60]->options[] = new Option('Proxy_Key', '<span class="text-red-600 dark:text-red-400">*</span> Key to use when sending remote commands to Daemon, empty for disable', 'text');
	$optiongroups[60]->options[] = new Option('Proxy_Daemons', '<span class="text-red-600 dark:text-red-400">*</span> List of daemons to send PROXY events from (used by proxy-daemon.pl)</span><br /><span class="text-xs text-gray-600 dark:text-gray-400">use "," as delimiter, eg &lt;ip&gt;:&lt;port&gt;,&lt;ip&gt;:&lt;port&gt;,... ', 'text');
	
	if (!empty($_POST))
	{
			foreach ($optiongroups as $og)
			{
				$og->update();
			}
			message('success', '<span class="text-l text-green-600 dark:text-green-400">Options updated successfully</span>');
	}
	
	
	$result = $db->query("SELECT keyname, value FROM hlstats_Options");
	while ($rowdata = $db->fetch_row($result))
	{
		$optiondata[$rowdata[0]] = $rowdata[1];
	}
	
	foreach ($optiongroups as $og)
	{
		$og->draw();
	}
?>
	<tr style="height:50px;">
		<td style="text-align:center;" colspan="2"><input type="submit" value="  Apply  " class="<?php echo windmill_button_class(); ?>"></td>
	</tr>
</table>

