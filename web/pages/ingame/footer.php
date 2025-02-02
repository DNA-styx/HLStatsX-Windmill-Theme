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

    // Hide Footer if using these pages elsewhere
    if (!isset($_GET['hide'])) {  

?>
<!-- Start footer bar -->
<div
    class="flex items-center justify-between p-4 mb-4 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800 text-gray-600 dark:text-gray-400">
    <div class="flex items-center">
        <span>
            <a class="font-semibold" href="https://github.com/startersclan/hlstatsx-community-edition" target="_blank">
                HLstatsX Community Edition v<?php echo $g_options['version'] ?> 
            </a>
        </span>
    </div>
    <div class="flex items-center">
        <span align="right">
            <a class="font-semibold" href="https://github.com/DNA-styx/hlstatsx-windmill-theme" target="_blank">
                Theme <?php 
                $theme_version = file_get_contents("assets/theme_version.txt");
                echo str_replace(" ", "",str_replace("**", "", $theme_version)) . "\n";
                ?>
            </a>
                based on 
            <a class="font-semibold" href="https://github.com/estevanmaito/windmill-dashboard" target="_blank">
                Windmill Dashboard
            </a>
        </span>
    </div>
</div>
<!-- end footer bar -->
<?php
}
?>
</body>
</html>