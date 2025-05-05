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

class Auth
{
	var $ok = false;
	var $error = false;

	var $username, $password, $savepass;
	var $sessionStart, $session;

	var $userdata = array();

	function __construct()
	{
		//@session_start();

		if (valid_request($_POST['authusername'], false))
		{
			$this->username = valid_request($_POST['authusername'], false);
			$this->password = valid_request($_POST['authpassword'], false);
			$this->savepass = valid_request($_POST['authsavepass'], false);
			$this->sessionStart = 0;

			# clear POST vars so as not to confuse the receiving page
			unset($_POST);
			$_POST = array();

			$this->session = false;

			if($this->checkPass()==true)
			{
				// if we have success, save it in this users SESSION
				$_SESSION['username']=$this->username;
				$_SESSION['password']=$this->password;
				$_SESSION['authsessionStart']=time();
				$_SESSION['acclevel'] = $this->userdata['acclevel'];
			}
		}
		elseif (isset($_SESSION['loggedin']))
		{
			$this->username = $_SESSION['username'];
			$this->password = $_SESSION['password'];
			$this->savepass = 0;
			$this->sessionStart = $_SESSION['authsessionStart'];
			$this->ok = true;
			$this->error = false;
			$this->session = true;
			
			if(!$this->checkPass())
			{
				unset($_SESSION['loggedin']);
			}
		}
		else
		{
			$this->ok = false;
			$this->error = false;

			$this->session = false;

			$this->printAuth();
		}
	}

	function checkPass()
	{
		global $db;

		$db->query("
				SELECT
					*
				FROM
					hlstats_Users
				WHERE
					username='$this->username'
				LIMIT 1
			");

		if ($db->num_rows() == 1)
		{
			// The username is OK

			$this->userdata = $db->fetch_array();
			$db->free_result();

			if (md5($this->password) == $this->userdata["password"])
			{
				// The username and the password are OK

				$this->ok = true;
				$this->error = false;
				$_SESSION['loggedin']=1;
				if ($this->sessionStart > (time() - 3600))
				{
					// Valid session, update session time & display the page
					$this->doCookies();
					return true;
				}
				elseif ($this->sessionStart)
				{
					// A session exists but has expired
					if ($this->savepass)
					{
						// They selected 'Save my password' so we just
						// generate a new session and show the page.
						$this->doCookies();
						return true;
					}
					else
					{
						$this->ok = false;
						$this->error = 'Your session has expired. Please try again.';
						$this->password = '';

						$this->printAuth();
						return false;
					}
				}
				elseif (!$this->session)
				{
					// No session and no cookies, but the user/pass was
					// POSTed, so we generate cookies.
					$this->doCookies();
					return true;
				}
				else
				{
					// No session, user/pass from a cookie, so we force auth
					$this->printAuth();
					return false;
				}
			}
			else
			{
				// The username is OK but the password is wrong

				$this->ok = false;
				if ($this->session)
				{
					// Cookie without 'Save my password' - not an error
					$this->error = false;
				}
				else
				{
					$this->error = 'The password you supplied is incorrect.';
				}
				$this->password = '';
				$this->printAuth();
			}
		}
		else
		{
			// The username is wrong
			$this->ok = false;
			$this->error = 'The username you supplied is not valid.';
			$this->printAuth();
		}
	}

	function doCookies()
	{
		return;
		setcookie('authusername', $this->username, time() + 31536000, '', '', 0);

		if ($this->savepass)
		{
			setcookie('authpassword', $this->password, time() + 31536000, '', '', 0);
		}
		else
		{
			setcookie('authpassword', $this->password, 0, '', '', 0);
		}
		setcookie('authsavepass', $this->savepass, time() + 31536000, '', '', 0);
		setcookie('authsessionStart', time(), 0, '', '', 0);
	}

	function printAuth()
	{
		global $g_options;

		include (PAGE_PATH . '/adminauth.php');
	}
}

	$auth = new Auth;
	if ($auth->ok === false || $auth->userdata['acclevel'] < 80)
	{
		return;
	}

    if (!defined('IN_HLSTATS')) {
        die('Do not access this file directly.');
    }

// Global Server Chat History
	$showserver = 0;
	if (isset($_GET['server_id'])) {
		$showserver = valid_request(strval($_GET['server_id']), true);
	}

	if ($showserver == 0) {
		$whereclause = "hlstats_Servers.game='$game'";
	} else {
		$whereclause = "hlstats_Servers.game='$game' AND hlstats_Events_Chat.serverId=$showserver";
	}

	$db->query("
		SELECT
			hlstats_Games.name
		FROM
			hlstats_Games
		WHERE
			hlstats_Games.code = '$game'
	");

	if ($db->num_rows() < 1) {
        error("No such game '$game'.");
	}

	list($gamename) = $db->fetch_row();

	$db->free_result();

	pageHeader
	(
		array ($gamename, 'Server Chat Statistics'),
		array ($gamename=>"%s?game=$game", 'Server Chat Statistics'=>'')
	);

	flush();

	$servername = "(All Servers)";
	
	if ($showserver != 0)
	{
		$result=$db->fetch_array
		(
			$db->query
			("
				SELECT
					hlstats_Servers.name
				FROM
					hlstats_Servers
				WHERE
					hlstats_Servers.serverId = ".$db->escape($showserver)."
			")
		);
		$servername = "(" . $result['name'] . ")";
	}
?>

<!-- Start chat.php -->
<?php display_page_title("$gamename $servername Server Chat Log (Last ".$g_options['DeleteDays'].' Days)'); ?>

	<div class="flex items-center justify-center p-4 mb-8 text-sm px-4 py-3 bg-white rounded-lg shadow-md dark:bg-gray-800">

		<form method="get" action="<?php echo $g_options['scripturl']; ?>">
			<input type="hidden" name="mode" value="chat">
			<input type="hidden" name="game" value="<?php echo $game; ?>">
			<span class="font-semibold text-center text-gray-700 dark:text-gray-400">Show Chat from
			<?php


				$result = $db->query
				("
					SELECT
						hlstats_Servers.serverId,
						hlstats_Servers.name
					FROM
						hlstats_Servers
					WHERE
						hlstats_Servers.game='$game'
					ORDER BY
						hlstats_Servers.sortorder,
						hlstats_Servers.name,
						hlstats_Servers.serverId ASC
					LIMIT
						0,
						50
				");

				echo '</span>';
				echo '<select class="mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" name="server_id"><option value="0">All Servers</option>';
				$dates = array ();
				$serverids = array();
				while ($rowdata = $db->fetch_array())
				{
					$serverids[] = $rowdata['serverId'];
					$dates[] = $rowdata; 
					if ($showserver == $rowdata['serverId'])
						echo '<option value="'.$rowdata['serverId'].'" selected>'.$rowdata['name'].'</option>';
					else
						echo '<option value="'.$rowdata['serverId'].'">'.$rowdata['name'].'</option>';
				}
				echo '</select>';
				$filter=isset($_REQUEST['filter'])?$_REQUEST['filter']:"";
			?>
			<span class="font-semibold text-center text-gray-700 dark:text-gray-400">Filter: </span>
			<input type="text" name="filter" value="<?php echo htmlentities($filter); ?>" class="mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"> 
			<input type="submit" value="View" class="windmill-button px-4 py-2 mb-2 text-sm font-medium leading-5 text-center border border-transparent rounded-lg btn">
		</form>
	</div>




	<div style="clear:both;padding-top:20px;"></div>
		<?php
			if ($showserver == 0)
			{
				$table = new Table(
					array
					(
						new TableColumn
						(
							'eventTime',
							'Date',
							'width=16'
						),
						new TableColumn
						(
							'lastName',
							'Player',
							'width=17&sort=no&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
						),
						new TableColumn
						(
							'message',
							'Message',
							'width=34&sort=no&embedlink=yes'
						),
						new TableColumn
						(
							'serverName',
							'Server',
							'width=23&sort=no'
						),
						new TableColumn
						(
							'map',
							'Map',
							'width=10&sort=no'
						)
					),
					'playerId',
					'eventTime',
					'lastName',
					false,
					50,
					"page",
					"sort",
					"sortorder"
				);
			}
			else
			{
				$table = new Table(
					array
					(
						new TableColumn
						(
							'eventTime',
							'Date',
							'width=16'
						),
						new TableColumn
						(
							'lastName',
							'Player',
							'width=24&sort=no&flag=1&link=' . urlencode('mode=playerinfo&amp;player=%k')
						),
						new TableColumn
						(
							'message',
							'Message',
							'width=44&sort=no&embedlink=yes'
						),
						new TableColumn
						(
							'map',
							'Map',
							'width=16&sort=no'
						)
					),
					'playerId',
					'eventTime',
					'lastName',
					false,
					50,
					"page",
					"sort",
					"sortorder"
				);
			}
			$whereclause2='';
			if(!empty($filter))
			{
				$whereclause2="AND MATCH (hlstats_Events_Chat.message) AGAINST ('" . $db->escape($filter) . "' in BOOLEAN MODE)";
			}
			$surl = $g_options['scripturl'];

			$result = $db->query
			("
				SELECT SQL_NO_CACHE 
					hlstats_Events_Chat.eventTime,
					unhex(replace(hex(hlstats_Players.lastName), 'E280AE', '')) as lastName,
					IF(hlstats_Events_Chat.message_mode=2, CONCAT('(Team) ', hlstats_Events_Chat.message), IF(hlstats_Events_Chat.message_mode=3, CONCAT('(Squad) ', hlstats_Events_Chat.message), hlstats_Events_Chat.message)) AS message,
					hlstats_Servers.name AS serverName,
					hlstats_Events_Chat.playerId,
					hlstats_Players.flag,
					hlstats_Events_Chat.map
				FROM
					hlstats_Events_Chat
				INNER JOIN
					hlstats_Players
				ON
					hlstats_Players.playerId = hlstats_Events_Chat.playerId
				INNER JOIN 
					hlstats_Servers
				ON
					hlstats_Servers.serverId = hlstats_Events_Chat.serverId
				WHERE
					$whereclause $whereclause2
				ORDER BY
					hlstats_Events_Chat.id $table->sortorder
				LIMIT
					$table->startitem,
					$table->numperpage;
			", true, false);

			$db->query
			("
				SELECT
		 			count(*)
				FROM
					hlstats_Events_Chat
				INNER JOIN
					hlstats_Players
				ON
					hlstats_Players.playerId = hlstats_Events_Chat.playerId
				INNER JOIN 
					hlstats_Servers
				ON
					hlstats_Servers.serverId = hlstats_Events_Chat.serverId
				WHERE
					$whereclause $whereclause2
			");
			if ($db->num_rows() < 1) $numitems = 0;
			else 
			{
				list($numitems) = $db->fetch_row();
			}
			$db->free_result();	

			$table->draw($result, $numitems, 95);
		?>
	</div>
<!-- end chat.php -->