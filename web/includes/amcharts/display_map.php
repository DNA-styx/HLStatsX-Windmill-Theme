<?php 
// This allows us to access native files directly
define('IN_HLSTATS', true);
// Allows access to native database connection variables 
require('../../config.php');
// Allow access to native functions
require_once('../functions.php');

// Get map and game type
if (isset($_GET['maptype'])) {
  $amcharts_maptype = valid_request(strval($_GET['maptype']),false);
} else {
  $amcharts_maptype = "rand";
}

if (isset($_GET['mapgame'])) {
  $amcharts_mapgame = valid_request(strval($_GET['mapgame']),false);
} else {
  die('MapGame not passed');
}

// Troubleshooting
// echo $amcharts_maptype . " & " . $amcharts_mapgame;

require_once('inc_amcharts_functions.php');

?>

<head>

</head>

<!-- Styles -->
<style>
#amcharts_chartdiv {
  width: 100%;
  height: 100%;
  position: relative;
}
</style>

<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/map.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<!-- Chart code -->
<script>
am5.ready(function() {
  var root = am5.Root.new("amcharts_chartdiv");

  root.setThemes([ am5themes_Animated.new(root) ]);

  var chart = root.container.children.push(am5map.MapChart.new(root, {
    panX: "rotateX",
    panY: "translateY",
    projection: am5map.geoMercator(),
    minZoomLevel: 5,
    maxZoomLevel: 50
  }));

  var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
    geoJSON: am5geodata_worldLow
  }));

  polygonSeries.mapPolygons.template.setAll({
    tooltipHTML: "{name}"
  });

  var citySeries = chart.series.push(am5map.MapPointSeries.new(root, {}));

  citySeries.bullets.push(function() {
    var circle = am5.Circle.new(root, {
      radius: 5,
      tooltipHTML: "{title}",
      cursorOverStyle: "pointer",
      tooltipY: 0,
      fill: am5.color(0x7c3aed),
      stroke: root.interfaceColors.get("background"),
      strokeWidth: 2
    });

    circle.events.on("click", (e) => {
      window.open(e.target.dataItem.dataContext.url);
    });

    return am5.Bullet.new(root, { sprite: circle });
  });

  var arrowSeries = chart.series.push(am5map.MapPointSeries.new(root, {}));

  arrowSeries.bullets.push(function() {
    var arrow = am5.Graphics.new(root, {
      fill: am5.color(0x000000),
      stroke: am5.color(0x000000),
      draw: function(display) {
        display.moveTo(0, -3);
        display.lineTo(8, 0);
        display.lineTo(0, 3);
        display.lineTo(0, -3);
      }
    });

    return am5.Bullet.new(root, { sprite: arrow });
  });

  var cities = <?php echo $json_player_details; ?>;
  citySeries.data.setAll(cities);

  polygonSeries.events.on("datavalidated", function() {
    chart.zoomToGeoPoint({
      longitude: <?php echo $server_lng ?>,
      latitude: <?php echo $server_lat ?>
    }, <?php echo $server_zoom ?>);
  });

  chart.appear(1000, 100);
});
</script>

<!-- HTML -->
<div id="amcharts_chartdiv"></div>