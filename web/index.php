<!DOCTYPE html>
<?php
session_start();
$pass = getenv("ACCESS_PASSWORD");
$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
$form = '<center><form action="" method="post">Code: <input type="password" name="code"><input type="submit" value="Submit"></form></center>';
if (!isset($_SESSION["access"]) && !isset($_POST['code'])){
  die($form);
}
if(isset($_POST['code'])) {
  $pw = htmlspecialchars($_POST['code']);
  if ($pw != $pass)  {
    die($form.'<br><center><font color="red">Invalid Code!</font></center>');
  }
  $_SESSION["access"] = "yes";
}
?>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 100; padding: 100 }
      #map-canvas { width: 60%; height: 100%; left: 0px; position: absolute }
      #event-log { width: 25%; height: 10%; left: 60%; position: absolute }
	  #chat {top: 40%; left: 60%; right: 0px; position: absolute }
	  #legend {width: 14%; right: 0px; position: absolute }
      /*	start styles for the ContextMenu	*/
      .context_menu{
        background-color:white;
        border:1px solid gray;
      }
      .context_menu_item{
        padding:3px 6px;
      }
      .context_menu_item:hover{
        background-color:#CCCCCC;
      }
      .context_menu_separator{
        background-color:gray;
        height:1px;
        margin:0;
        padding:0;
      }
      .labels {
        color: red;
        background-color: white;
        font-family: "Lucida Grande", "Arial", sans-serif;
        font-size: 10px;
        font-weight: bold;
        text-align: center;
        width: 40px;     
        border: 2px solid black;
        white-space: nowrap;
      }
      /*	end styles for the ContextMenu	*/
      #tlkio {
        width: 25%;
        height: 400px;
        position: absolute;
        left: 60%;
        bottom: 0;
        z-index: 1000;
      }
    </style>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBKtDQobTOCsgqSEzlx2BIK3sSceNNw99I"></script>
    <script type="text/javascript" src="./src/MarkerWithLabel.js"></script>
    <script type="text/javascript" src="./src/ContextMenu.js"></script>
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	
	
    <script type="text/javascript">
      var eventCount = 0;
      var line = 0;
      var map;
      var id;
      var markers = {};
      var markerMenuListenerAdded = false;
      var donep = true;

      function initialize() {
          var mapOptions = {
              center: new google.maps.LatLng(42.448066, -76.486831),
              zoom: 18,
              minZoom: 18,
              maxZoom: 20,
              streetViewControl: false,
              mapTypeControl: false,
              mapTypeId: google.maps.MapTypeId.HYBRID
          }
          var allowedBounds = new google.maps.LatLngBounds(
              new google.maps.LatLng(42.447143, -76.488262),
              new google.maps.LatLng(42.450127, -76.486055)
          )
          map = new google.maps.Map(document.getElementById("map-canvas"),
              mapOptions);

          // ADD START
          var z1Coordinates = [
              new google.maps.LatLng(42.446879, -76.486098),
              new google.maps.LatLng(42.449848, -76.48731)
          ]
          var z2Coordinates = [
              new google.maps.LatLng(42.447766, -76.4876),
              new google.maps.LatLng(42.448787, -76.48569)
          ]
          var fenceCoordinates = [
              new google.maps.LatLng(42.447486, -76.485025),
              new google.maps.LatLng(42.447055, -76.485282),
              new google.maps.LatLng(42.447397, -76.48606),
              new google.maps.LatLng(42.448147, -76.486049),
              new google.maps.LatLng(42.448164, -76.485626),
              new google.maps.LatLng(42.449481, -76.485808),
              new google.maps.LatLng(42.449701, -76.486035),
              new google.maps.LatLng(42.449689, -76.486692),
              new google.maps.LatLng(42.449389, -76.487079),
              new google.maps.LatLng(42.44903, -76.486972),
              new google.maps.LatLng(42.449204, -76.487734),
              new google.maps.LatLng(42.446971, -76.487632),
              new google.maps.LatLng(42.446467, -76.487364),
              new google.maps.LatLng(42.446387, -76.486613),
              new google.maps.LatLng(42.446224, -76.486124)
          ];
          var patrolCoordinates = [
              new google.maps.LatLng(42.448543, -76.486641),
              new google.maps.LatLng(42.448018, -76.486664),
              new google.maps.LatLng(42.447895, -76.486959),
              new google.maps.LatLng(42.446925, -76.486951)
          ];
          var z1Line = new google.maps.Polyline({
              path: z1Coordinates,
              strokeColor: "#00FFAA",
              strokeOpacity: 0.5,
              strokeWeight: 2
          });
          var z2Line = new google.maps.Polyline({
              path: z2Coordinates,
              strokeColor: "#00FFAA",
              strokeOpacity: 0.5,
              strokeWeight: 2
          });
          var patrol = new google.maps.Polyline({
              path: patrolCoordinates,
              strokeColor: "#FF0000",
              strokeOpacity: 0.8,
              strokeWeight: 3
          });
          var fence = new google.maps.Polyline({
              path: fenceCoordinates,
              strokeColor: "#0000FF",
              strokeOpacity: 0.8,
              strokeWeight: 3
          });
          //z1Line.setMap(map);
          //z2Line.setMap(map);
          patrol.setMap(map);
          fence.setMap(map);

          var stage = new google.maps.Rectangle({
              strokeColor: '#000000',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#000000',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(

                  new google.maps.LatLng(42.447736, -76.48768),
                  new google.maps.LatLng(42.447404, -76.48759))
          });
          var blueFlag = new google.maps.Rectangle({
              strokeColor: '#0000FF',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#0000FF',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.447875, -76.486562),
                  new google.maps.LatLng(42.447788, -76.486486))
          });
          var yellowFlag = new google.maps.Rectangle({
              strokeColor: '#FFFF00',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#FFFF00',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.446897, -76.486686),
                  new google.maps.LatLng(42.446842, -76.486591))
          });
          var redFlag = new google.maps.Rectangle({
              strokeColor: '#FF0000',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#FF0000',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.448603, -76.486495),
                  new google.maps.LatLng(42.448528, -76.486409))
          });
          var greenFlag = new google.maps.Rectangle({
              strokeColor: '#00FF00',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#00FF00',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.447554, -76.486476),
                  new google.maps.LatLng(42.447456, -76.486363))
          });
          var emergencyTent = new google.maps.Rectangle({
              strokeColor: '#FF8800',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#FF8800',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.446867, -76.487197),
                  new google.maps.LatLng(42.446794, -76.487069))
          });
          var soundBooth = new google.maps.Rectangle({
              strokeColor: '#000000',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#000000',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.448007, -76.487131),
                  new google.maps.LatLng(42.447977, -76.487087))
          });
          var IDCheck = new google.maps.Rectangle({
              strokeColor: '#AA00FF',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#AA00FF',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.447239, -76.486626),
                  new google.maps.LatLng(42.447102, -76.486535))
          });
          var eco = new google.maps.Rectangle({
              strokeColor: '#FF44FF',
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: '#FF44FF',
              fillOpacity: 0.35,
              map: map,
              bounds: new google.maps.LatLngBounds(
                  new google.maps.LatLng(42.447207, -76.486241),
                  new google.maps.LatLng(42.447163, -76.486202))
          });
          google.maps.event.addListener(patrol, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(fence, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(stage, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(blueFlag, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(yellowFlag, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(redFlag, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(greenFlag, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(emergencyTent, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(soundBooth, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(IDCheck, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });
          google.maps.event.addListener(eco, 'rightclick', function(mouseEvent) {
              google.maps.event.trigger(map, 'rightclick', mouseEvent);
          });

          // ADD END


          //	create the ContextMenuOptions object
          var contextMenuOptions = {};
          contextMenuOptions.classNames = {
              menu: 'context_menu',
              menuSeparator: 'context_menu_separator'
          };
          var markerContextMenuOptions = {};
          markerContextMenuOptions.classNames = {
              menu: 'context_menu',
              menuSeparator: 'context_menu_separator'
          };

          //	create an array of ContextMenuItem objects
          var menuItems = [];
          menuItems.push({
              className: 'context_menu_item',
              eventName: 'ems_click',
              label: 'Pin EMS'
          });
          menuItems.push({
              className: 'context_menu_item',
              eventName: 'police_click',
              label: 'Pin Police'
          });
          menuItems.push({
              className: 'context_menu_item',
              eventName: 'patrol_click',
              label: 'Pin Patrol'
          });
          //	a menuItem with no properties will be rendered as a separator
          menuItems.push({});
          menuItems.push({
              className: 'context_menu_item',
              eventName: 'center_map_click',
              label: 'Center map here'
          });
          contextMenuOptions.menuItems = menuItems;

          var markerMenuItems = [];
          markerMenuItems.push({
              className: 'context_menu_item',
              eventName: 'resolve',
              label: 'Resolve Situation'
          });
          markerMenuItems.push({
              className: 'context_menu_item',
              eventName: 'alert',
              label: 'Alert Situation'
          });
          markerMenuItems.push({
              className: 'context_menu_item',
              eventName: 'responding',
              label: 'Help On The Way'
          }); /////////////////////////NEW***
          markerContextMenuOptions.menuItems = markerMenuItems;

          //	create the ContextMenu object
          var contextMenu = new ContextMenu(map, contextMenuOptions);
          var markerContextMenu = new ContextMenu(map, markerContextMenuOptions);

          var lastValidCenter = map.getCenter();

          function setup() {
              $.ajax({
                  url: "log.php",
                  cache: false
              }).done(function(data) {
                  n = data.match(/[^\r\n]+/g);
                  if (n != null) {
                      line = n.length;
                      var arr = [];
                      var logt = "";
                      for (var i = line - 1; i > -1; i--) {
                          str = n[i];
                          s = str.split("|");
                          logt = logt + s[4] + '\n';
                          if ($.inArray(s[1], arr) == -1) {
                              arr.push(s[1]);
                              eventCount = Math.max(eventCount, s[1] * 1 + 1);
                              if (s[0] == "new") {
                                  cord = s[2].split(",");
                                  lat = cord[0].substring(1);
                                  lon = cord[1].substring(0, cord[1].length - 1);
                                  latLng2 = new google.maps.LatLng(lat, lon);
                                  placeMarker(latLng2, s[3], markerContextMenu, s[1]);
                              }
                              if (s[0] == "alr" || s[0] == "hlp") {
                                  cord = s[2].split(",");
                                  lat = cord[0].substring(1);
                                  lon = cord[1].substring(0, cord[1].length - 1);
                                  latLng2 = new google.maps.LatLng(lat, lon);
                                  cl = s[3] == "p" ? "blue" : "red";
                                  if (s[3] == "n") {
                                      cl = "green";
                                  }
                                  placeMarker(latLng2, cl, markerContextMenu, s[1]);
                                  if (s[0] == "alr") alertMarker(s[1]);
                                  if (s[0] == "hlp") helpMarker(s[1]);
                              }
                          }
                      }
                      txt.log.value = logt + "Event Log";
                  }
              });
          }
          $.when(setup()).done(setInterval(update, 2000));


          function update() {
              $.ajax({
                  url: "log.php",
                  cache: false
              }).done(function(data) {
                  n = data.match(/[^\r\n]+/g);
                  if (n != null) {
                      var arr2 = [];
                      var logt2 = "";
                      for (var i = n.length - 1; i > -1; i--) {
                          str = n[i];
                          s = str.split("|");
                          logt2 = logt2 + s[4] + '\n';
                      }
                      txt.log.value = logt2 + "Event Log";
                      for (var i = n.length - 1; i > line - 1; i--) {
                          str = n[i];
                          s = str.split("|");
                          if ($.inArray(s[1], arr2) == -1) {
                              arr2.push(s[1]);
                              eventCount = Math.max(eventCount, s[1] * 1 + 1);
                              if (s[0] == "new") {
                                  cord = s[2].split(",");
                                  lat = cord[0].substring(1);
                                  lon = cord[1].substring(0, cord[1].length - 1);
                                  latLng2 = new google.maps.LatLng(lat, lon);
                                  placeMarker(latLng2, s[3], markerContextMenu, s[1]);
                              } else if (s[0] == "rem") {
                                  removeMarker(s[1]);
                              } else {
                                  if (s[0] == "alr") alertMarker(s[1]);
                                  if (s[0] == "hlp") helpMarker(s[1]);
                              }

                          }
                      }
                      line = n.length;
                  } else {
                      if (markers[1] != null)
                          location.reload();
                  }
              });
              donep = true;
          }
          map.setHeading(270);

          google.maps.event.addListener(map, 'center_changed', function() {
              if (allowedBounds.contains(map.getCenter())) {
                  // still within valid bounds, so save the last valid position
                  lastValidCenter = map.getCenter();
                  return;
              }
              // not valid anymore => return to last valid position
              map.panTo(lastValidCenter);
          });
          //	display the ContextMenu on a Map right click
          google.maps.event.addListener(map, 'rightclick', function(mouseEvent) {
              if (donep)
                  contextMenu.show(mouseEvent.latLng);
          });
          //	listen for the ContextMenu 'menu_item_selected' event
          google.maps.event.addListener(contextMenu, 'menu_item_selected', function(latLng, eventName) {
              //	latLng is the position of the ContextMenu
              //	eventName is the eventName defined for the clicked ContextMenuItem in the ContextMenuOptions
              var currentTime = new Date()
              var hours = currentTime.getHours()
              var minutes = currentTime.getMinutes()
              if (minutes < 10) {
                  minutes = "0" + minutes
              }
              var seconds = currentTime.getSeconds()
              if (seconds < 10) {
                  seconds = "0" + seconds
              }
              label = eventCount.toString();
              switch (eventName) {
                  case 'ems_click':
                      donep = false;
                      txt2 = hours + ":" + minutes + ":" + seconds + ' EMS Marker ';
                      $.when(change('red', latLng, txt2)).done(update());
                      break;
                  case 'police_click':
                      donep = false;
                      txt2 = hours + ":" + minutes + ":" + seconds + ' Police Marker ';
                      $.when(change('blue', latLng, txt2)).done(update());
                      break;
                  case 'patrol_click':
                      donep = false;
                      txt2 = hours + ":" + minutes + ":" + seconds + ' Patrol Marker ';
                      $.when(change('green', latLng, txt2)).done(update());
                      break;
                  case 'center_map_click':
                      map.panTo(latLng);
                      break;
              }
          });
      }

      function change(p, l, ll) {
          $.post("log.php", {
              values: 'new|' + ll + '|' + l + '|' + p
          });
      }

      function change2(n, l, ll) {
          $.post("log.php", {
              values: n + '|' + l + '|||' + ll
          });
      }

      function change3(d, m, c, ll) {
          $.post("log.php", {
              values: 'alr|' + d + '|' + m + '|' + c + '|' + ll
          });
      }

      function change4(d, m, c, ll) { /////////////////////////NEW***
          $.post("log.php", {
              values: 'hlp|' + d + '|' + m + '|' + c + '|' + ll
          });
      }


      function placeMarker(location, color, markerContextMenu, label) {
          var clickedLocation = new google.maps.LatLng(location);
          var iconLink;
          var markerType;
          if (color == 'blue') {
              iconLink = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
              markerType = 'p';
          } else if (color == 'red') {
              iconLink = 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
              markerType = 'e';
          } else {
              iconLink = 'https://maps.google.com/mapfiles/ms/icons/green-dot.png';
              markerType = 'n';
          }
          var marker = new MarkerWithLabel({ //new google.maps.Marker({
              icon: iconLink,
              position: location,
              map: map,
              optimized: false,
              labelContent: label,
              labelAnchor: new google.maps.Point(22, 0),
              labelClass: "labels", // the CSS class for the label
              labelStyle: {
                  opacity: 0.75
              },
              customInfo: markerType,
          });
          id = parseInt(marker.labelContent);
          markers[id] = marker;
          google.maps.event.addListener(marker, "click", function(mouseEvent) {
              markerContextMenu.show(mouseEvent.latLng);
              id = parseInt(marker.labelContent); //removeMarker(id) 
          });
          if (!markerMenuListenerAdded) {
              markerMenuListenerAdded = true;
              google.maps.event.addListener(markerContextMenu, 'menu_item_selected', function(latLng, eventName) {
                  //	latLng is the position of the ContextMenu
                  //	eventName is the eventName defined for the clicked ContextMenuItem in the ContextMenuOptions
                  var currentTime = new Date()
                  var hours = currentTime.getHours()
                  var minutes = currentTime.getMinutes()
                  if (minutes < 10) {
                      minutes = "0" + minutes
                  }
                  var seconds = currentTime.getSeconds()
                  if (seconds < 10) {
                      seconds = "0" + seconds
                  }
                  switch (eventName) {
                      case 'resolve':
                          txt2 = hours + ":" + minutes + ":" + seconds + " Resolved event " + id;
                          $.when(change2('rem', id, txt2)).done(update());

                          // removeMarker(id);

                          break;
                      case 'alert':
                          txt2 = hours + ":" + minutes + ":" + seconds + " Attention needed at event " + id;
                          $.when(change3(id, markers[id].position, markers[id].customInfo, txt2)).done(update());

                          break;
                      case 'responding': /////////////////////////NEW***
                          txt2 = hours + ":" + minutes + ":" + seconds + " Help on the way to event " + id;
                          $.when(change4(id, markers[id].position, markers[id].customInfo, txt2)).done(update());

                          break;
                  }
              });
          }
      }

      var removeMarker = function(id) {
          marker = markers[id];
          marker.setMap(null);
      }

      var alertMarker = function(id) {
          m = markers[id];
          if (m.customInfo == 'p') {
              markers[id].setIcon('./src/blue-yellow.gif');
          } else if (m.customInfo == 'e') {
              markers[id].setIcon('./src/red-yellow.gif');
          } else {
              markers[id].setIcon('./src/green-yellow.gif');
          }
      }

      var helpMarker = function(id) { /////////////////////////NEW***
          m = markers[id];
          if (m.customInfo == 'p') {
              markers[id].setIcon('./src/blue-purple-dot.png');
          } else if (m.customInfo == 'e') {
              markers[id].setIcon('./src/red-purple-dot.png');
          } else {
              markers[id].setIcon('./src/green-purple-dot.png');
          }
      }

      google.maps.event.addDomListener(window, 'load', initialize);


    </script>
  </head>
  <body>
    <div id="map-canvas"/></div>
    <div id="event-log"/>
    <form name="txt" id="txt" method="post" action="">
    <textarea readonly name="log" cols="40" rows="20">Event Log</textarea><br>
    </form>
    </div>
	</div>
    <div id="tlkio" data-channel="slopeday"></div>
    <script async src="https://tlk.io/embed.js" type="text/javascript"></script>
    <div id="legend"/>
        <img src='legend.png'>
    </div>	
  </body>
</html>
