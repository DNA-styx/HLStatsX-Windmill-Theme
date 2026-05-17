<?php
	// VOICECOMM MODULE
	global $db;

	define('TS', 0);
	define('VENT', 1);
	define('TS3', 2);
	define('DISCORD', 3);
	define('MUMBLE', 4);
	define('STEAM', 5);

	// Truncate a server name to 20 characters, adding ellipsis if longer
	function vc_truncate_name($name, $max = 20) {
		return mb_strlen($name) > $max ? mb_substr($name, 0, $max) . '…' : $name;
	}

	$result = $db->query("
		SELECT
			serverId,
			name,
			addr,
			password,
			descr,
			queryPort,
			UDPPort,
			serverType
		FROM
			hlstats_Servers_VoiceComm
        ");

	if ($db->num_rows($result) >= 1) {
?>

<!-- start voicecomm_serverlist.php -->
<?php
	// Issue 5 fix: added isset() guard before comparing $_GET['mode']
	if (isset($_GET['mode']) && $_GET['mode'] == 'teamspeak') {
		display_page_title('Voice Servers');
	} else {
		display_page_subtitle("Voice Servers");
	}
?>
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
	<div class="w-full overflow-x-auto">
		<table class="w-full whitespace-no-wrap">
			<tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
				<td style="min-width:11rem; max-width:11rem;">&nbsp;</td>
				<td style="padding-right:1rem;">Status</td>
				<td style="padding-right:1rem;">Server Address</td>
				<td style="padding-right:1rem;">Password</td>
				<td style="white-space:nowrap; padding-right:1rem;">Slots&nbsp;used</td>
				<td style="white-space:nowrap; padding-right:1rem;">Server Uptime</td>
				<td style="white-space:nowrap;">Notes</td>
			</tr>
			<tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
<?php
		$i = 0; // TS1 counter
		$j = 0; // Ventrilo counter
		$t = 0; // TS3 counter
		$d = 0; // Discord counter
		$m = 0; // Mumble counter  -- Issue 1 fix: dedicated counter, was incorrectly sharing $t
		$s = 0; // Steam counter   -- Issue 1 fix: dedicated counter, was incorrectly sharing $t and $m

		while ($row = $db->fetch_array()) {
			if ($row['serverType'] == TS) {
				$ts_servers[$i]['serverId']  = $row['serverId'];
				$ts_servers[$i]['name']      = $row['name'];
				$ts_servers[$i]['addr']      = $row['addr'];
				$ts_servers[$i]['password']  = $row['password'];
				$ts_servers[$i]['descr']     = $row['descr'];
				$ts_servers[$i]['queryPort'] = $row['queryPort'];
				$ts_servers[$i]['UDPPort']   = $row['UDPPort'];
				$i++;
			} else if ($row['serverType'] == VENT) {
				$vent_servers[$j]['serverId']  = $row['serverId'];
				$vent_servers[$j]['name']      = $row['name'];
				$vent_servers[$j]['addr']      = $row['addr'];
				$vent_servers[$j]['password']  = $row['password'];
				$vent_servers[$j]['descr']     = $row['descr'];
				$vent_servers[$j]['queryPort'] = $row['queryPort'];
				$j++;
			} else if ($row['serverType'] == TS3) {
				$ts3_servers[$t]['serverId']  = $row['serverId'];
				$ts3_servers[$t]['name']      = $row['name'];
				$ts3_servers[$t]['addr']      = $row['addr'];
				$ts3_servers[$t]['password']  = $row['password'];
				$ts3_servers[$t]['descr']     = $row['descr'];
				$ts3_servers[$t]['queryPort'] = $row['queryPort'];
				$ts3_servers[$t]['UDPPort']   = $row['UDPPort'];
				$t++;
			} else if ($row['serverType'] == DISCORD) {
				// Issue 1 fix: was using $t as index (shared with TS3) and $d++ was unused as index
				$discord_servers[$d]['serverId']  = $row['serverId'];
				$discord_servers[$d]['name']      = $row['name'];
				$discord_servers[$d]['addr']      = $row['addr'];
				$discord_servers[$d]['password']  = $row['password'];
				$discord_servers[$d]['descr']     = $row['descr'];
				$discord_servers[$d]['queryPort'] = $row['queryPort'];
				$discord_servers[$d]['UDPPort']   = $row['UDPPort'];
				$d++;
			} else if ($row['serverType'] == MUMBLE) {
				// Issue 1 fix: was using $t as index (shared with TS3/Discord)
				$mumble_servers[$m]['serverId']  = $row['serverId'];
				$mumble_servers[$m]['name']      = $row['name'];
				$mumble_servers[$m]['addr']      = $row['addr'];
				$mumble_servers[$m]['password']  = $row['password'];
				$mumble_servers[$m]['descr']     = $row['descr'];
				$mumble_servers[$m]['queryPort'] = $row['queryPort'];
				$mumble_servers[$m]['UDPPort']   = $row['UDPPort'];
				$m++;
			} else if ($row['serverType'] == STEAM) {
				// Issue 1 fix: was using $t as index (shared with TS3/Discord/Mumble) and $m++ was shared with Mumble
				$steam_groups[$s]['serverId']  = $row['serverId'];
				$steam_groups[$s]['name']      = $row['name'];
				$steam_groups[$s]['addr']      = $row['addr'];
				$steam_groups[$s]['password']  = $row['password'];
				$steam_groups[$s]['descr']     = $row['descr'];
				$steam_groups[$s]['queryPort'] = $row['queryPort'];
				$steam_groups[$s]['UDPPort']   = $row['UDPPort'];
				$s++;
			}
		}

		if (isset($ts_servers))
		{
			require_once(PAGE_PATH . '/teamspeak_class.php');
			foreach ($ts_servers as $ts_server)
			{
				$settings = $teamspeakDisplay->getDefaultSettings();
				$settings['serveraddress']   = $ts_server['addr'];
				$settings['serverqueryport'] = $ts_server['queryPort'];
				$settings['serverudpport']   = $ts_server['UDPPort'];
				$ts_info = $teamspeakDisplay->queryTeamspeakServerEx($settings);
				if ($ts_info['queryerror'] != 0) {
					$ts_channels = 'err';
					$ts_slots    = $ts_info['queryerror'];
				} else {
					$ts_channels = count($ts_info['channellist']);
					$ts_slots    = count($ts_info['playerlist']) . '/' . $ts_info['serverinfo']['server_maxusers'];
				}
				$ts_display_name = vc_truncate_name(trim($ts_server['name']));
?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td style="min-width:11rem; max-width:11rem; overflow:hidden;">
					<div class="flex items-center">
						<img src="<?php echo IMAGE_PATH; ?>/teamspeak/teamspeak.gif" alt="tsicon" class="mr-2">
						<a style="white-space:nowrap;" href="<?php echo $g_options['scripturl'] . "?mode=teamspeak&amp;game=$game&amp;tsId=" . $ts_server['serverId']; ?>" title="<?php echo htmlspecialchars(trim($ts_server['name'])); ?>"><?php echo htmlspecialchars($ts_display_name); ?></a>
					</div>
				</td>
				<td style="padding-right:1rem;">-</td>
				<td style="padding-right:1rem;">
					<a href="teamspeak://<?php echo $ts_server['addr'] . ':' . $ts_server['UDPPort'] ?>/?channel=?password=<?php echo $ts_server['password']; ?>"><?php echo $ts_server['addr'] . ':' . $ts_server['UDPPort']; ?></a>
				</td>
				<td style="padding-right:1rem;"><?php echo $ts_server['password']; ?></td>
				<td style="white-space:nowrap; padding-right:1rem;"><?php echo $ts_slots; ?></td>
				<td style="padding-right:1rem;">-</td>
				<td style="white-space:nowrap;"><?php echo $ts_server["descr"]; ?></td>
			</tr>
<?php
			}
		}

		if (isset($vent_servers))
		{
			require_once(PAGE_PATH . '/ventrilostatus.php');
			foreach ($vent_servers as $vent_server)
			{
				$ve_info = new CVentriloStatus;
				$ve_info->m_cmdcode = 2; // Detail mode.
				$ve_info->m_cmdhost = $vent_server['addr'];
				$ve_info->m_cmdport = $vent_server['queryPort'];
				$rc = $ve_info->Request();
				$ve_channels       = $ve_info->m_channelcount;
				$ve_slots          = $ve_info->m_clientcount . '/' . $ve_info->m_maxclients;
				$vent_display_name = vc_truncate_name($vent_server['name']);
?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td style="min-width:11rem; max-width:11rem; overflow:hidden;">
					<div class="flex items-center">
						<img src="<?php echo IMAGE_PATH; ?>/ventrilo/ventrilo.png" alt="venticon" class="mr-2">
						<a style="white-space:nowrap;" href="<?php echo $g_options['scripturl'] . "?mode=ventrilo&amp;game=$game&amp;veId=" . $vent_server['serverId']; ?>" title="<?php echo htmlspecialchars($vent_server['name']); ?>"><?php echo htmlspecialchars($vent_display_name); ?></a>
					</div>
				</td>
				<td style="padding-right:1rem;">-</td>
				<td style="padding-right:1rem;">
					<a href="ventrilo://<?php echo $vent_server['addr'] . ':' . $vent_server['queryPort'] ?>/servername=<?php echo $ve_info->m_name; ?>">
						<?php echo $vent_server['addr'] . ':' . $vent_server['queryPort']; ?>
					</a>
				</td>
				<td style="padding-right:1rem;"><?php echo $vent_server['password']; ?></td>
				<td style="white-space:nowrap; padding-right:1rem;"><?php echo $ve_slots; ?></td>
				<td style="padding-right:1rem;">-</td>
				<td style="white-space:nowrap;"><?php echo $vent_server["descr"]; ?></td>
			</tr>
<?php
			}
		}

		if (isset($ts3_servers))
		{
			require_once(INCLUDE_PATH . '/teamspeak3/ts3admin_class.php');
			require_once(INCLUDE_PATH . '/teamspeak3/inc_ts3_settings.php');

			/**
			 * Generates server list via ts3admin.class
			 * by par0noid solutions - ts3admin.info https://github.com/Speckmops/ts3admin.class
			 */
			foreach ($ts3_servers as $ts3_server)
			{
				$ts3_id        = $ts3_server['serverId'];
				$ts3_ip        = $ts3_server['addr'];
				$ts3_queryport = $ts3_server['queryPort'];
				$ts3_user      = $ts3_server_query_username;
				$ts3_pass      = $ts3_server_query_password;

				$tsAdmin = new ts3admin($ts3_ip, $ts3_queryport);

				if ($tsAdmin->getElement('success', $tsAdmin->connect())) {
					$tsAdmin->login($ts3_user, $ts3_pass);
					$servers = $tsAdmin->serverList();

					foreach ($servers['data'] as $server) {
						$ts3q_server_id      = $server['virtualserver_id'];
						$ts3q_server_name    = $server['virtualserver_name'];
						$ts3q_display_name   = vc_truncate_name($ts3q_server_name);
						$ts3q_server_page    = '<span style="white-space:nowrap;">' . htmlspecialchars($ts3q_display_name) . "</span>&nbsp;<a href=\"" . $g_options['scripturl'] . "?mode=teamspeak&tsId=" . $ts3_id . "\"><span class=\"windmill-text-link\">(View)</span></a>";
						$ts3q_server_port    = $server['virtualserver_port'];

						$ts3q_server_clients = isset($server['virtualserver_clientsonline'])
							? $server['virtualserver_clientsonline'] . '/' . $server['virtualserver_maxclients']
							: '-';

						$ts3q_server_status = "<span class=\"px-2 leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100\">" . htmlspecialchars($server['virtualserver_status']) . "</span>";

						$ts3q_server_uptime = isset($server['virtualserver_uptime'])
							? $tsAdmin->convertSecondsToStrTime($server['virtualserver_uptime'])
							: '-';

						$ts3q_server_link = $ts3_server['addr'] . ":" . $ts3q_server_port
							. "&nbsp;<span class=\"windmill-text-link\"><a href=\"ts3server://" . $ts3_server['addr'] . "?"
							. "port=" . $ts3q_server_port
							. "&password=" . $ts3_server['password']
							. "&addbookmark=" . htmlspecialchars($ts3q_server_name)
							. "\">(Join)</a></span>";
					}
				} else {
					$ts3q_server_id      = '0';
					$ts3q_server_page    = '<span style="white-space:nowrap;">' . htmlspecialchars(vc_truncate_name($ts3_server['name'])) . '</span>';
					$ts3q_server_port    = $ts3_server['UDPPort'];
					$ts3q_server_clients = '-';
					$ts3q_server_status  = '<span class="px-2 leading-tight text-red-700 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700">offline</span>';
					$ts3q_server_uptime  = '-';
					$ts3q_server_link    = $ts3_server['addr'] . ':' . $ts3_server['UDPPort'];
				}
?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td style="min-width:11rem; max-width:11rem; overflow:hidden;">
					<div class="flex items-center">
						<img src="<?php echo IMAGE_PATH; ?>/teamspeak3/ts3.png" alt="tsicon" class="mr-2">
						<?php echo $ts3q_server_page; ?>
					</div>
				</td>
				<td style="padding-right:1rem;"><?php echo $ts3q_server_status; ?></td>
				<td style="padding-right:1rem;"><?php echo $ts3q_server_link; ?></td>
				<td style="padding-right:1rem;"><?php echo $ts3_server['password']; ?></td>
				<td style="white-space:nowrap; padding-right:1rem;"><?php echo $ts3q_server_clients; ?></td>
				<td style="padding-right:1rem;"><?php echo $ts3q_server_uptime; ?></td>
				<td style="white-space:nowrap;"><?php echo $ts3_server["descr"]; ?></td>
			</tr>
<?php
			}
			$tsAdmin->ts3quit();
		}

		if (isset($discord_servers))
		{
			require_once(INCLUDE_PATH . '/discord/inc_discord_settings.php');

			foreach ($discord_servers as $discord_server)
			{
				/* Credit: https://stackoverflow.com/questions/47454876/get-total-number-of-members-in-discord-using-php/74583912#74583912 */
				$discord_invite_code      = $discord_server['addr'];
				$discord_invite_short_url = "discord.gg/" . $discord_invite_code;
				$discord_invite_full_url  = "https://discord.gg/" . $discord_invite_code;
				$discord_display_name     = vc_truncate_name($discord_server['name']);

				$discord_api_url    = "https://discord.com/api/v9/invites/" . $discord_invite_code . "?with_counts=true&with_expiration=true";
				$discord_api_jsonIn = url_get_contents($discord_api_url);

				if ($discord_api_jsonIn) {
					$discord_api_json_obj         = json_decode($discord_api_jsonIn, $assoc = false);
					$discord_voice_presence_total = max(($discord_api_json_obj->approximate_presence_count - $discord_number_of_bots), 0) . "/" . $discord_api_json_obj->approximate_member_count . " (" . $discord_number_of_bots . " bots)";
					$discord_invite_link          = $discord_invite_short_url . "&nbsp;<span class=\"windmill-text-link\"><a href=\"" . $discord_invite_full_url . "\">(Join)</a></span>";
					$discord_server_status        = "<span class=\"px-2 leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100\">online</span>";
				} else {
					$discord_voice_presence_total = "-";
					$discord_invite_link          = "-";
					$discord_server_status        = '<span class="px-2 leading-tight text-red-700 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700">invalid</span>';
				}
?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td style="min-width:11rem; max-width:11rem; overflow:hidden;">
					<div class="flex items-center">
						<img src="<?php echo IMAGE_PATH; ?>/discord/discord.png" alt="Discord" class="mr-2">
						<span style="white-space:nowrap;" title="<?php echo htmlspecialchars($discord_server['name']); ?>"><?php echo htmlspecialchars($discord_display_name); ?></span>
					</div>
				</td>
				<td style="padding-right:1rem;"><?php echo $discord_server_status; ?></td>
				<td style="padding-right:1rem;"><?php echo $discord_invite_link; ?></td>
				<td style="padding-right:1rem;"><?php echo $discord_server['password']; ?></td>
				<td style="white-space:nowrap; padding-right:1rem;"><?php echo $discord_voice_presence_total; ?></td>
				<td style="padding-right:1rem;">&#8734;</td>
				<td style="white-space:nowrap;"><?php echo $discord_server["descr"]; ?></td>
			</tr>
<?php
			}
		}

		if (isset($mumble_servers))
		{
			require_once(INCLUDE_PATH . '/mumble/inc_mumble_functions.php');

			foreach ($mumble_servers as $mumble_server)
			{
				$mumble_cvp_url    = $mumble_server['addr'];
				$mumble_cvp_jsonIn = file_get_contents($mumble_cvp_url);

				if ($mumble_cvp_jsonIn) {
					$mumble_api_json_obj = json_decode($mumble_cvp_jsonIn, $assoc = false);

					if (!$mumble_api_json_obj->error) {
						$mumble_server_name      = $mumble_api_json_obj->name;
						$mumble_server_link      = $mumble_api_json_obj->x_connecturl;
						$mumble_server_uptime    = secondsToTime($mumble_api_json_obj->uptime);
						$mumble_users_root       = count($mumble_api_json_obj->root->users);
						$mumble_users_rest       = 0; // Issue 3 fix: initialise before accumulation loop
						foreach ($mumble_api_json_obj->root->channels as $item) {
							$mumble_users_rest = $mumble_users_rest + count($item->users);
						}
						$mumble_users_total      = $mumble_users_root + $mumble_users_rest;
						$mumble_server_status    = "<span class=\"px-2 leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100\">online</span>";
						$mumble_server_join_link = "Mumble Server <span class=\"windmill-text-link\"><a href=\"" . $mumble_server_link . "\">(Join)</a></span>";
					} else {
						$mumble_server_name      = $mumble_server['name'];
						$mumble_users_total      = "-";
						$mumble_server_uptime    = '-';
						$mumble_server_status    = '<span class="px-2 leading-tight text-red-700 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700">offline</span>';
						$mumble_server_join_link = '-';
					}
				} else {
					$mumble_server_name      = $mumble_server['name'];
					$mumble_users_total      = "-";
					$mumble_server_uptime    = '-';
					$mumble_server_status    = '<span class="px-2 leading-tight text-red-700 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700">invalid</span>';
					$mumble_server_join_link = '-';
				}
				$mumble_display_name = vc_truncate_name($mumble_server_name);
?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td style="min-width:11rem; max-width:11rem; overflow:hidden;">
					<div class="flex items-center">
						<img src="<?php echo IMAGE_PATH; ?>/mumble/mumble.png" alt="Mumble" class="mr-2">
						<span style="white-space:nowrap;" title="<?php echo htmlspecialchars($mumble_server_name); ?>"><?php echo htmlspecialchars($mumble_display_name); ?></span>
					</div>
				</td>
				<td style="padding-right:1rem;"><?php echo $mumble_server_status; ?></td>
				<td style="padding-right:1rem;"><?php echo $mumble_server_join_link; ?></td>
				<td style="padding-right:1rem;"><?php echo $mumble_server['password']; ?></td>
				<td style="white-space:nowrap; padding-right:1rem;"><?php echo $mumble_users_total; ?></td>
				<td style="padding-right:1rem;"><?php echo $mumble_server_uptime; ?></td>
				<td style="white-space:nowrap;"><?php echo $mumble_server["descr"]; ?></td>
			</tr>
<?php
			}
		}

		// Issue 19 fix: blank line before block
		if (isset($steam_groups))
		{
			// Issue 8: full fix requires inc_steamgroup_class.php changes - addressed in that file's update
			foreach ($steam_groups as $steam_group)
			{
				$steam_group_link_group_ID64 = $steam_group['addr'];
				$steam_group_name            = $steam_group['name'];
				$steam_display_name          = vc_truncate_name($steam_group_name);
				include(INCLUDE_PATH . '/steamgroup/inc_steamgroup_class.php');
?>
			<tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
				<td style="min-width:11rem; max-width:11rem; overflow:hidden;">
					<div class="flex items-center">
						<img src="<?php echo IMAGE_PATH; ?>/steamgroup/steamgroup.png" alt="Steam Group" class="mr-2">
						<span style="white-space:nowrap;" title="<?php echo htmlspecialchars($steam_group_name); ?>"><?php echo htmlspecialchars($steam_display_name); ?></span>
					</div>
				</td>
				<td style="padding-right:1rem;"><?php echo $steam_group_server_status; ?></td>
				<td style="padding-right:1rem;"><?php echo !empty($steam_group_xml_error) ? htmlspecialchars(vc_truncate_name($steam_group_xml_error)) : $steam_group_server_address; ?></td>
				<td style="padding-right:1rem;"></td>
				<td style="white-space:nowrap; padding-right:1rem;"><?php echo $steam_group_chat_total; ?></td>
				<td style="padding-right:1rem;">&#8734;</td>
				<td style="white-space:nowrap;"><?php echo $steam_group["descr"]; ?></td>
			</tr>
<?php
			}
		}
?>
			</tbody>
		</table>
	</div>
	<!-- Bottom footer bar to match other tables on the site -->
	<div class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">&nbsp;</div>
</div>
<?php
	}
?>
<!-- end voicecomm_serverlist.php -->
