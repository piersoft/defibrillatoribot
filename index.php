<?php
$lat=$_GET['lat'];
$lon=$_GET['lon'];
$r=$_GET['r'];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8 />
<title>Defibrillatori Lecce</title>
<meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
<script src='https://api.mapbox.com/mapbox.js/v2.2.2/mapbox.js'></script>
<link href='https://api.mapbox.com/mapbox.js/v2.2.2/mapbox.css' rel='stylesheet' />
<meta name="viewport" content="width=device-width, initial-scale=0.8, maximum-scale=1.0, user-scalable=no">
<meta property="og:image" content="http://www.piersoft.it/daebot/daebot.png"/>

</head>
<body onload="getLocation()">
  <div id='loader'><span class='message'>Sto cercando la tua posizione..</span></div>
  <style>
  #loader {
      position:absolute; top:0; bottom:0; width:100%;
      background:rgba(255, 255, 255, 1);
      transition:background 1s ease-out;
      -webkit-transition:background 1s ease-out;
  }
  #loader.done {
      background:rgba(255, 255, 255, 0);
  }
  #loader.hide {
      display:none;
  }
  #loader .message {
      position:absolute;
      left:30%;
      top:50%;
      font-family: Titillium Web, Arial, Sans-Serif;
      font-size: 15px;
  }
  </style>

<!--
  This example requires jQuery to load the file with AJAX.
  You can use another tool for AJAX.

  This pulls the file airports.csv, converts into into GeoJSON by autodetecting
  the latitude and longitude columns, and adds it to the map.

  Another CSV that you use will also need to contain latitude and longitude
  columns, and they must be similarly named.
-->

<script src='https://code.jquery.com/jquery-1.11.0.min.js'></script>
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-pip/v0.0.2/leaflet-pip.js'></script>
<script>
var latphp="";
var lonphp="";
var r="";
var options = {
  enableHighAccuracy: true,
  timeout: 5000,
  maximumAge: 0
};

function success(pos) {
  var crd = pos.coords;

  console.log('Your current position is:');
  console.log('Latitude : ' + crd.latitude);
  console.log('Longitude: ' + crd.longitude);
  console.log('More or less ' + crd.accuracy + ' meters.');
};

function error(err) {
  alert('Devi usare https e non http nell\'URL per attivare la geolocalizzazione automatica e impostare in ON il localizzatore GPS del tuo smartphone');
  console.warn('ERROR(' + err.code + '): ' + err.message);
};
function getLocation() {
console.log('<?php echo $isSecure ?>');
    if (navigator.geolocation ) {
        navigator.geolocation.getCurrentPosition(showPosition, error, options);

    } else {
alert('Abilita la localizzazione GPS per cortesia :) ')

    }
}
function showPosition(position) {


  //  x.innerHTML = "Latitude: " + position.coords.latitude +
  //  "<br>Longitude: " + position.coords.longitude;
  latphp = parseFloat('<?php printf($_GET['lat']); ?>');
  lonphp = parseFloat('<?php printf($_GET['lon']); ?>');
  r = parseFloat('<?php printf($_GET['r']); ?>');
  if (!latphp || 0 === latphp.length){
    latphp=position.coords.latitude;
    lonphp=position.coords.longitude;
    r=1;
  }else{
alert ("Abilita la localizzazione sul tuo smartphone");
  }

console.log(latphp+" "+lonphp+" "+r);
  window.location.href = "http://www.piersoft.it/daebot/map/index.php?lat="+latphp+"&lon="+lonphp+"&r="+r;
}
</script>
</body>
</html>
