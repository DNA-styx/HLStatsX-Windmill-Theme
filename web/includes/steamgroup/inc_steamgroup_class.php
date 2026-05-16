<?php
// URL to download the XML
$steam_group_xml_url   = "https://steamcommunity.com/gid/" . $steam_group_link_group_ID64 . "/memberslistxml/?xml=1";

// Issue 2 fix: cache file is now unique per group ID so multiple groups don't overwrite each other
$steam_group_cache_id  = preg_replace('/[^0-9]/', '', $steam_group_link_group_ID64);
$steam_group_xml_cache = INCLUDE_PATH . '/steamgroup/memberslist_' . $steam_group_cache_id . '.xml';

$cacheTime           = 3600; // 60 minutes in seconds
$steam_group_xml_error = ""; // Variable to store error messages

// Issue 8 fix: guard against redeclaration when included multiple times in a loop
if (!function_exists('downloadXml')) {
    function downloadXml($url, $filePath) {
        global $steam_group_xml_error;
        $xmlContent = @file_get_contents($url);
        if ($xmlContent === false) {
            $steam_group_xml_error = "Error: Unable to fetch XML data from URL.";
            return false;
        }
        if (@file_put_contents($filePath, $xmlContent) === false) {
            $steam_group_xml_error = "Error: Unable to write XML data to file ($filePath). Check permissions.";
            return false;
        }
        return true;
    }
}

// Ensure the file exists or can be created
if (!file_exists($steam_group_xml_cache)) {
    if (@file_put_contents($steam_group_xml_cache, '') === false) {
        $steam_group_xml_error = "Error: Unable to create the XML file ($steam_group_xml_cache). Check permissions.";
    }
}

// If no errors so far, check if the file is writable
if (empty($steam_group_xml_error) && !is_writable($steam_group_xml_cache)) {
    $steam_group_xml_error = "Error: XML file ($steam_group_xml_cache) is not writable. Please check permissions.";
}

// Check if the local file is recent or needs updating
if (empty($steam_group_xml_error) && (!file_exists($steam_group_xml_cache) || (time() - filemtime($steam_group_xml_cache)) > $cacheTime)) {
    if (!downloadXml($steam_group_xml_url, $steam_group_xml_cache)) {
        // Error message already set inside downloadXml
    }
}

// Attempt to load the XML file
if (empty($steam_group_xml_error)) {
    $xml = @simplexml_load_file($steam_group_xml_cache);
    if ($xml === false) {
        if (downloadXml($steam_group_xml_url, $steam_group_xml_cache)) {
            $xml = @simplexml_load_file($steam_group_xml_cache);
            if ($xml === false) {
                $steam_group_xml_error = "Error: Unable to load XML file after re-downloading. The file may still be corrupted.";
            }
        } else {
            $steam_group_xml_error = "Error: Unable to re-download the XML file.";
        }
    }
}

// Display the membersInChat element or the error message
if (empty($steam_group_xml_error)) {
    if (isset($xml->groupDetails->membersInChat)) {
        $steam_group_chat_total      = htmlspecialchars($xml->groupDetails->membersOnline) . '/' . htmlspecialchars($xml->memberCount) . ' (' . htmlspecialchars($xml->groupDetails->membersInChat) . ' in chat)';
        $steam_group_url             = htmlspecialchars($xml->groupDetails->groupURL);
        $steam_group_server_status   = "<span class=\"px-2 leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100\">online</span>";
        $steam_group_server_address  = "Steam Group <span class=\"windmill-text-link\"><a href=\"https://steamcommunity.com/groups/" . $steam_group_url . "\">(Join)</a></span>";
        $steam_group_name            = htmlspecialchars($xml->groupDetails->groupName);
    } else {
        $steam_group_xml_error = "Error: 'membersInChat' element not found in the XML file.";
    }
}

if (!empty($steam_group_xml_error)) {
    $steam_group_server_status = '<span class="px-2 leading-tight text-red-700 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700">invalid</span>';
    $steam_group_chat_total    = "-";
}
?>
