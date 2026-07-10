<?php
// Database configuration
// Using HLStatsx native Variables
$host = DB_ADDR;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

// Declare vars
$amcharts_rank_get = 0;
$amcharts_rank_display = "";
$amcharts__rank_count = 0;

// Which map are we displaying?

$name_map = [
    "top" => ["query" => "SELECT playerId,skill,lastName,lat,lng FROM `hlstats_Players` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame' && !hideranking) ORDER BY skill DESC LIMIT 50", "title" => "Top Players", "rank_get" => 1],
    "active" => ["query" => "SELECT hlstats_Livestats.player_Id AS playerId,hlstats_Livestats.skill,hlstats_Livestats.`Name` AS lastName,hlstats_Livestats.cli_lat AS lat,hlstats_Livestats.cli_lng AS lng FROM `hlstats_Livestats` INNER JOIN `hlstats_Servers` ON hlstats_Servers.serverId = hlstats_Livestats.server_id WHERE (hlstats_Livestats.cli_lng IS NOT NULL AND hlstats_Servers.game = '$amcharts_mapgame')", "title" => "Active Players"],
    "recent" => ["query" => "SELECT playerId,skill,lastName,lat,lng FROM `hlstats_Players` WHERE (lat IS NOT NULL && game = '$amcharts_mapgame' && !hideranking) ORDER BY last_event DESC LIMIT 20", "title" => "Recent Players"],
    "random" => ["query" => "SELECT lastName,lat,lng FROM `hlstats_Players` WHERE lat IS NOT NULL ORDER BY RAND() LIMIT 20", "title" => "Ten Random Players"]
];

$amcharts_maptype = $amcharts_maptype ?? "random";

$amcharts_player_query = $name_map[$amcharts_maptype]["query"];
$amcharts_title = $name_map[$amcharts_maptype]["title"];
$amcharts_rank_get = $name_map[$amcharts_maptype]["rank_get"] ?? null;

$amcharts_region_query = "SELECT `value` FROM `hlstats_Options` WHERE `keyname` = 'google_map_region'";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare an array to hold the JSON objects
    $result = [];
    $item1List = [];

        // Query to fetch map region from admin options
        $singleRowSql = $amcharts_region_query;
        $singleRowStmt = $pdo->query($singleRowSql);
        $singleRow = $singleRowStmt->fetch(PDO::FETCH_ASSOC);
        
        $map_center = region_to_lat_lon($singleRow['value']);

        $server_lat = $map_center['lat'];
        $server_lng = $map_center['lng'];
        $server_zoom = $map_center['zoom'];


    // Query to fetch data
    $sql = $amcharts_player_query;
    $stmt = $pdo->query($sql);

    // Fetch data row by row
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $amcharts__rank_count ++;
        
        if ($amcharts_rank_get) {
            $amcharts_rank_display = "Rank: #" . $amcharts__rank_count . '<br>';
        }
      
        if (array_key_exists('skill',$row) && array_key_exists('playerId',$row)) {
            $player_skill = $row['skill'];
            $player_id = $row['playerId'];
        }else{
            $player_skill = 0;
            $player_id = 1;
        }

        $result[] = [
            'id' => $row['lastName'],
            'title' => '<center>' . $row['lastName'] . '<br><small>' . $amcharts_rank_display . 'Points: ' . $player_skill . '<br>(Click me!)</small></center>',
            'url' => '../../hlstats.php?mode=playerinfo&player=' . $player_id , 
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    " ". floatval(round($row['lng'],2)), // Convert to float for proper JSON formatting
                    " ". floatval(round($row['lat'],2))
                ]
            ]
        ];
        $item1List[] = $row['lastName'];
    }

    // Encode the result as a JSON string
    // echo "JSON with full data:\n";
    // echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	$json_player_details = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // echo "\n\nJSON with item1 list:\n";
    // echo json_encode($item1List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $json_player_names = json_encode($item1List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Query to fetch server locations for the current game
    $server_result = [];
    $server_sql = "SELECT serverId, name, lat, lng FROM `hlstats_Servers` WHERE game = '$amcharts_mapgame' AND lat IS NOT NULL AND lng IS NOT NULL";
    $server_stmt = $pdo->query($server_sql);
    while ($srow = $server_stmt->fetch(PDO::FETCH_ASSOC)) {
        $server_result[] = [
            'id' => $srow['name'],
            'title' => htmlspecialchars($srow['name'], ENT_QUOTES),
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    " ". floatval(round($srow['lng'],2)),
                    " ". floatval(round($srow['lat'],2))
                ]
            ]
        ];
    }
    $json_server_details = json_encode($server_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


function region_to_lat_lon($region) {
    $regions = [
        'ARGENTINA' => ['lat' => -38.4161, 'lng' => -63.6167, 'zoom' => 8],
        'ASIA' => ['lat' => 34.0479, 'lng' => 100.6197, 'zoom' => 8],
        'AUSTRALIA' => ['lat' => -25.2744, 'lng' => 133.7751, 'zoom' => 8],
        'AUSTRIA' => ['lat' => 47.5162, 'lng' => 14.5501, 'zoom' => 8],
        'BELGIUM' => ['lat' => 50.8503, 'lng' => 4.3517, 'zoom' => 8],
        'BRAZIL' => ['lat' => -14.2350, 'lng' => -51.9253, 'zoom' => 8],
        'CHINA' => ['lat' => 35.8617, 'lng' => 104.1954, 'zoom' => 8],
        'DENMARK' => ['lat' => 56.2639, 'lng' => 9.5018, 'zoom' => 8],
        'EAST EUROPE' => ['lat' => 55.3781, 'lng' => 37.6173, 'zoom' => 8],
        'EUROPE' => ['lat' => 54.5260, 'lng' => 15.2551, 'zoom' => 8],
        'FINLAND' => ['lat' => 61.9241, 'lng' => 25.7482, 'zoom' => 8],
        'FRANCE' => ['lat' => 46.6034, 'lng' => 1.8883, 'zoom' => 8],
        'GERMANY' => ['lat' => 51.1657, 'lng' => 10.4515, 'zoom' => 8],
        'ITALY' => ['lat' => 41.8719, 'lng' => 12.5674, 'zoom' => 8],
        'JAPAN' => ['lat' => 36.2048, 'lng' => 138.2529, 'zoom' => 8],
        'NETHERLANDS' => ['lat' => 52.1326, 'lng' => 5.2913, 'zoom' => 8],
        'NORTH AFRICA' => ['lat' => 26.8206, 'lng' => 30.8025, 'zoom' => 8],
        'NORTH AMERICA' => ['lat' => 54.5260, 'lng' => -105.2551, 'zoom' => 8],
        'NORTH EUROPE' => ['lat' => 60.4720, 'lng' => 8.4689, 'zoom' => 8],
        'NORWAY' => ['lat' => 60.4720, 'lng' => 8.4689, 'zoom' => 8],
        'POLAND' => ['lat' => 51.9194, 'lng' => 19.1451, 'zoom' => 8],
        'ROMANIA' => ['lat' => 45.9432, 'lng' => 24.9668, 'zoom' => 8],
        'RUSSIA' => ['lat' => 61.5240, 'lng' => 105.3188, 'zoom' => 8],
        'SOUTH AFRICA' => ['lat' => -30.5595, 'lng' => 22.9375, 'zoom' => 8],
        'SOUTH AMERICA' => ['lat' => -14.2350, 'lng' => -51.9253, 'zoom' => 8],
        'SOUTH KOREA' => ['lat' => 35.9078, 'lng' => 127.7669, 'zoom' => 8],
        'SPAIN' => ['lat' => 40.4637, 'lng' => -3.7492, 'zoom' => 8],
        'SUISSE' => ['lat' => 46.8182, 'lng' => 8.2275, 'zoom' => 8],
        'SWEDEN' => ['lat' => 60.1282, 'lng' => 18.6435, 'zoom' => 8],
        'TAIWAN' => ['lat' => 23.6978, 'lng' => 120.9605, 'zoom' => 8],
        'TURKEY' => ['lat' => 38.9637, 'lng' => 35.2433, 'zoom' => 8],
        'UNITED KINGDOM' => ['lat' => 55.3781, 'lng' => -3.4360, 'zoom' => 8],
        'WORLD' => ['lat' => 24.2155, 'lng' => 12.8858, 'zoom' => 3],
    ];

    return $regions[$region] ?? ['lat' => null, 'lng' => null, 'zoom' => null];
}

?>