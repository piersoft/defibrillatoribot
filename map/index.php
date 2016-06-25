<?php

?>
<!DOCTYPE html>
<html>
<head>
  <title>Defibrillator by @piersoft</title>
  <meta charset="utf-8" />
  <link rel="shortcut icon" href="favicon.ico" />
  <meta property="og:image" content="http://www.piersoft.it/dae/dae.png"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.6.2/leaflet.css" />
  <link rel="stylesheet" href="OverPassLayer.css" />
  <script src="http://cdn.leafletjs.com/leaflet-0.6.2/leaflet-src.js"></script>
  <script src="OverPassLayer2.js"></script>
  <style>
    body {
      padding: 0;
      margin: 0;
    }
    html, body, #map {
      height: 100%;
      width: 100%;
    }
  </style>
</head>
<body>
  <div id="map"></div>
  <script>
	  var attr_osm = 'Map data &copy; <a href="http://openstreetmap.org/">OpenStreetMap</a> contributors. Defibrillator Map powered by @piersoft',
      attr_overpass = 'POI via <a href="http://www.overpass-api.de/">Overpass API</a>';
    var osm = new L.TileLayer('http://a.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {opacity: 0.7, attribution: [attr_osm, attr_overpass].join(', ')});
    var lat=parseFloat('<?php printf($_GET['lat']); ?>'),
        lon=parseFloat('<?php printf($_GET['lon']); ?>'),
          zoom=14;
		var map = new L.Map('map').addLayer(osm).setView(new L.LatLng(lat,lon), 16);
    var markeryou = L.marker([parseFloat('<?php printf($_GET['lat']); ?>'), parseFloat('<?php printf($_GET['lon']); ?>')]).addTo(map);
    markeryou.bindPopup("<b>Sei qui</b>");
    //OverPassAPI overlay
    function number_format (number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    function Dist(lat1, lon1, lat2, lon2)
      {
      rad = function(x) {return x*Math.PI/180;}

      var R     = 6378.137;                 //Raggio terrestre in km (WGS84)
      var dLat  = rad( lat2 - lat1 );
      var dLong = rad( lon2 - lon1 );

      var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLong/2) * Math.sin(dLong/2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      var d = R * c;

      return d.toFixed(3);                      //Ritorno 3 decimali
    }

    var opl = new L.OverPassLayer({
      endpoint: "http://overpass.osm.rambler.ru/cgi/",
      query: "node(BBOX)['emergency'='defibrillator'];out;",
      callback: function(data) {
        for(var i=0;i<data.elements.length;i++) {
          var e = data.elements[i];

          if (e.id in this.instance._ids) return;
          this.instance._ids[e.id] = true;
          var pos = new L.LatLng(e.lat, e.lon);

        //  var data=0.0;
        //  if (miles >=1){
        //    data =number_format($miles, 2, '.', '')+" Km";
        //  } else data =number_format(($miles*1000), 0, '.', '')+" mt";
          var distanzap=Dist(e.lat, e.lon,lat,lon);
        //    if (distanzap >=1){
        //      data =number_format(distanzap, 2, '.', '')+" Km";
        //    } else data =number_format((distanzap*1000), 0, '.', '')+" mt";
        //  console.log(distanzap);
          var popup = this.instance._poiInfo(e.tags,e.id,e.lat, e.lon,lat,lon,distanzap);
          var color = e.tags.collection_times ? 'green':'red';
          var circle = L.circle(pos, 20, {
            color: color,
            fillColor: '#fa3',
            fillOpacity: 0.5
          })
          .bindPopup(popup);
          this.instance.addLayer(circle);
        }
      },
      minZoomIndicatorOptions: {
        position: 'topright',
        minZoomMessageNoLayer: "no layer assigned",
        minZoomMessage: "current Zoom-Level: CURRENTZOOM all data at Level: MINZOOMLEVEL"
      }
    });
    map.addLayer(opl);

  </script>
</body>
</html>
