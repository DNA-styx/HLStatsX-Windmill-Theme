<?php
	pageHeader(
		array('Teamspeak viewer'),
		array('Teamspeak viewer' => '')
	);

	include_once(PAGE_PATH . '/voicecomm_serverlist.php');
	include_once(INCLUDE_PATH . 'teamspeak3/inc_ts3_settings.php');

	$tsId = valid_request(intval($_GET['tsId']), true);

	$db->query("SELECT addr, queryPort, UDPPort, serverType FROM hlstats_Servers_VoiceComm WHERE serverId=$tsId");
	$s = $db->fetch_array();

	// Redirect to the game page if no TS3 server is configured for this tsId
	if (!$s || $s['serverType'] != TS3) {
		header("Location: hlstats.php?game=" . urlencode($game));
		exit;
	}

	$uip   = $s['addr'];
	$tPort = $s['queryPort'];
	$port  = $s['UDPPort'];

	include(INCLUDE_PATH . "/teamspeak3/ts3ssv.php");
	$ts3ssv = new ts3ssv($uip, $tPort);
	$ts3ssv->useServerPort($port);
	$ts3ssv->imagePath        = IMAGE_PATH . "/teamspeak3/";
	$ts3ssv->iconPack         = $ts3_icon_pack;
	$ts3ssv->flagPath         = IMAGE_PATH . "/flags/";
	$ts3ssv->timeout          = 2;
	$ts3ssv->setLoginPassword($ts3_server_query_username, $ts3_server_query_password);
	$ts3ssv->setCache($ts3_cache_timeout, INCLUDE_PATH . "/teamspeak3/ts3ssv.php.cache");
	$ts3ssv->hideEmptyChannels  = false;
	$ts3ssv->hideParentChannels = false;
	$ts3ssv->showNicknameBox    = false;
	$ts3ssv->showPasswordBox    = true;
	$ts3ssv->limitToChannels();
	$ts3_returned_data = $ts3ssv->render();

	/* Split the returned text to be used in two areas */
	$arr                = explode("*-1@9-*", $ts3_returned_data);
	$ts3_server_info    = $arr[0];
	$ts3_server_channels = $arr[1];
?>
<!-- Start teamspeak.php -->
<div class="grid gap-6 mb-8 md:grid-cols-2">
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
		<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
			Server Details
		</h4>
		<p class="text-gray-600 dark:text-gray-400">
			<?php echo $ts3_server_info; ?>
		</p>
	</div>
	<div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
		<h4 class="mb-4 font-semibold text-gray-600 dark:text-gray-300">
			Channels and Members
		</h4>
		<p>
			<?php echo $ts3_server_channels; ?>
		</p>
	</div>
</div>
<!-- End teamspeak.php -->