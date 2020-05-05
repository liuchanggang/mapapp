<html>
<head>
<meta charset=utf-8 />
<title>Custom tooltip</title>
<meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
<script src='https://api.mapbox.com/mapbox.js/v3.3.0/mapbox.js'></script>
<link href='https://api.mapbox.com/mapbox.js/v3.3.0/mapbox.css' rel='stylesheet' />
<style>
  body { margin:0; padding:0; }
  #map { position:absolute; top:0; bottom:0; width:100%; }
</style>
</head>
<body>
<div id='map'><?php 
if(count($stores) == 0) {
    echo 'There is no restaurant open right now. Please check again later';
}
?></div>
<?php 
if(count($stores) > 0) {
?>
<script>
L.mapbox.accessToken = 'pk.eyJ1IjoiYmFsZHdpbmxvdWllIiwiYSI6ImNrOHBnZXVqbzAwcHIzZW1rM3N1bmFkbGsifQ.i8_wxiTjFryHhzy5UF5Qjw';
var map = L.mapbox.map('map')
    .setView([<?php echo $stores[0]['geometry']['coordinates'][1]?>, <?php echo $stores[0]['geometry']['coordinates'][0]?>], 12)
    .addLayer(L.mapbox.styleLayer('mapbox://styles/mapbox/streets-v11'));

var featureLayer = L.mapbox.featureLayer({
        type: 'FeatureCollection',
        features: <?php echo json_encode($stores)?>
    })
    .addTo(map);

// Note that calling `.eachLayer` here depends on setting GeoJSON _directly_
// above. If you're loading GeoJSON asynchronously, like from CSV or from a file,
// you will need to do this within a `featureLayer.on('ready'` event.
featureLayer.eachLayer(function(layer) {

    // here you call `bindPopup` with a string of HTML you create - the feature
    // properties declared above are available under `layer.feature.properties`
    var content = '<h2>' + layer.feature.properties.name+ '<\/h2>' +
        '<p>' + layer.feature.properties.address + '<\/p>' +
        '<p>' + layer.feature.properties.phone + '<\/p>' +
        '<p>' + layer.feature.properties.hours + '<\/p>' +
        '<p><a href="#" onclick="parent.saveBusiness(\'' + layer.feature.properties.id + '\');return false;">Add to favorite</a><\/p>';
    layer.bindPopup(content);
});
</script>
<?php }?>
</body>
</html>