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

	if ($auth->userdata["acclevel"] < 80) {
        die ("Access denied!");
	}

	$edlist = new EditList("id", "hlstats_ClanTags", "clan", false);
	$edlist->columns[] = new EditListColumn("pattern", "Pattern", 40, true, "text", "", 64);
	$edlist->columns[] = new EditListColumn("position", "Match Position", 0, true, "select", "EITHER/EITHER;START/START only;END/END only");
	
	if ($_POST)
	{
		if ($edlist->update())
			message("success", "Operation successful.");
		else
			message("warning", $edlist->error());
	}
	
?>
<div class="ml-6 mb-6">
<p class="text-sm text-gray-600 dark:text-gray-400">
Here you can define the patterns used to determine what clan a player is in. These patterns 
are applied to players' names when they connect or change name.<br>
<br>
Special characters in the pattern:<br>
<br>
<table border=0 cellspacing=0 cellpadding=4>

<tr class="head">
	<td class="text-gray-700 dark:text-gray-400">Character</td>
	<td class="text-gray-700 dark:text-gray-400">Description</td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>A</tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches one character  (i.e. a character is required)</td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>X</tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches zero or one characters  (i.e. a character is optional)</td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>a</tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches literal A or a</td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>x</tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches literal X or x</td>
</tr>

</table>
<br>
<p class="text-gray-700 dark:text-gray-400">Example patterns:<p>

<table border=0 cellspacing=0 cellpadding=4>

<tr class="head">
	<td class="text-gray-700 dark:text-gray-400">Pattern</td>
	<td class="text-gray-700 dark:text-gray-400">Description</td>
	<td class="text-gray-700 dark:text-gray-400">Example</td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>[AXXXXX]</tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches 1 to 6 characters inside square braces</td>
	<td class="text-gray-700 dark:text-gray-400"><tt>[ZOOM]Player</tt></td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>{AAXX}</tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches 2 to 4 characters inside curly braces</td>
	<td class="text-gray-700 dark:text-gray-400"><tt>{S3G}Player</tt></td>
</tr>

<tr>
	<td class="text-gray-700 dark:text-gray-400"><tt>rex>></tt></td>
	<td class="text-gray-700 dark:text-gray-400">Matches the string "rex>>", "REX>>", etc.</td>
	<td class="text-gray-700 dark:text-gray-400"><tt>REX>>Tyranno</tt></td>
</tr>

</table>
<br>
<p class="text-gray-700 dark:text-gray-400">
Avoid adding patterns to the database that are too generic. Always ensure you have at least 
one literal (non-special) character in the pattern -- for example if you were to add the pattern 
"AXXA", it would match any player with 2 or more letters in their name!<br>
<br>
The Match Position field sets which end of the player's name the clan tag is allowed to appear.<br>
<br>
</p>
<?php
	
	$result = $db->query("
		SELECT
			id,
			pattern,
			position
		FROM
			hlstats_ClanTags
		ORDER BY
			id
	");
	
	$edlist->draw($result);
?>
	</p>
	</div>

	<input type="submit" value="  Apply  " class="<?php echo windmill_button_class(); ?>">
