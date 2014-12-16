<!DOCTYPE html>
<html> 
<head> 
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
  <title>DOTC Bus Stops</title> 
  <script src="http://maps.google.com/maps/api/js?sensor=false" 
          type="text/javascript"></script>
</head> 
<body>
<h1>Quiapo - Taytay</h1>
  <div id="map" style="width: 100%; height: 650px;"></div>

  <script type="text/javascript">
    var locations = [
	//southbound 38 stops
      ['Quiapo', 14.598784,120.98432, 1],
      ['Loyola Street', 14.601411,120.988467, 2],
      ['Legarda Street', 14.6004,120.990665, 3],
      ['Stop on Shop', 14.601433,120.992819, 4],
	  ['Pureza', 14.601483,121.005027, 5],
	  ['V.Mapa', 14.603988,121.017266, 6],
	  ['SM City Sta.Mesa', 14.605125,121.018985, 7],
	  ['San Juan Plaza', 14.60536,121.023157, 8],
	  ['San Juan City Hall', 14.606045,121.023763, 9],
	  ['P.Guevara Street', 14.604079,121.032154, 10],
	  ['Philippine Long Distance Telephone', 14.607029,121.039662, 11],
	  ['Madison', 14.604576,121.042866, 12],
	  ['Greenhills', 14.600259,121.048607, 13],
	  ['Dept of Transportation and Communications', 14.594523,121.056398, 14],
	  ['Robinson Galleria', 14.592096,121.059518, 15],
	  ['Meralco Avenue', 14.588566,121.064594, 16],
	  ['Gate 2', 14.58871,121.066571, 17],
	  ['MFI Foundation, Inc', 14.588971,121.070574, 18],
	  ['Frontera Dr', 14.58951,121.077992, 19],
	  ['I.P.I Bus Stop', 14.589664,121.080751, 20],
	  ['Sto.Rosario Church', 14.590395,121.087305, 21],
	  ['Jennys', 14.590074,121.091167, 22],
	  ['Luckygold Plaza', 14.589898,121.094357, 23],
	  ['Super 8', 14.58892,121.103098, 24],
	  ['Ever Gotesco Ortigas Complex', 14.588326,121.104791, 25],
	  ['Junction', 14.586722,121.113338, 26],
	  ['Meralco', 14.585882,121.116328, 27],
	  ['J.G. Gracia Sr.', 14.58352,121.12403, 28],
	  ['Don Celso S. Tuazon Avenue', 14.582122,121.128451, 29],
	  ['E.Rodriguez Avenue', 14.579882,121.135638, 30],
	  ['Tikling', 14.577718,121.14242, 31],
	  ['Golden City', 14.567196,121.141444, 32],
	  ['SM City Taytay', 14.565852,121.140478, 33],
	  ['Jollibee', 14.559073,121.136136, 34],
	  ['Puregold', 14.554698,121.137809, 35],
	  ['Nationa College of Business and Arts', 14.552969,121.138715, 36],
	  ['G-Liner', 14.548454,121.141162, 37],
	  ['RRCG', 14.537827,121.148643, 38],
	//northbound 37 stops
	  ['RRCG', 14.537915,121.148768, 40, 1],
	  ['G-Liner', 14.548534,121.141271, 41, 1],
	  ['Nationa College of Business and Arts', 14.552687,121.139071, 42, 1],
	  ['Puregold', 14.554544,121.138041, 43, 1],
	  ['BPI', 14.558974,121.136344, 44, 1],
	  ['SM City Taytay', 14.565591,121.140587, 45, 1],
	  ['Golden City', 14.566713,121.141417, 46, 1],
	  ['Tikling', 14.577963,121.142572, 47, 1],
	  ['E.Rodriguez Avenue', 14.579882,121.136409, 48, 1],
	  ['Don Celso S. Tuazon Avenue', 14.582034,121.129536, 49, 1],
	  ['Sunset Dr', 14.583698,121.124249, 50, 1],
	  ['Meralco', 14.586161,121.116438, 51, 1],
	  ['Junction', 14.586929,121.113374, 52, 1],
	  ['Banco De Oro', 14.589053,121.103131, 53, 1],
	  ['Iglesia ni Cristo', 14.58975,121.097812, 54, 1],
	  ['Luckygold Plaza', 14.590081,121.094365, 55, 1],
	  ['Allied Bank', 14.590466,121.089397, 56, 1],
	  ['Sto.Rosario Church', 14.590659,121.087342, 57, 1],
	  ['I.P.I Bus Stop', 14.590008,121.080945, 58, 1],
	  ['MFI Foundation, Inc', 14.589208,121.070564, 59, 1],
	  ['The Medical City', 14.589058,121.068905, 60, 1],
	  ['Gate 2', 14.588894,121.066223, 61, 1],
	  ['Center Gate', 14.589301,121.063252, 62, 1],
	  ['Robinson Galleria', 14.592287,121.059725, 63, 1],
	  ['Dept of Transportation and Communications', 14.594701,121.0566, 64, 1],
	  ['Greenhills', 14.600484,121.04881, 65, 1],
	  ['Madison', 14.604752,121.043024, 66, 1],
	  ['Ronac Art Center', 14.607219,121.03981, 67, 1],
	  ['P.Guevara Street', 14.604223,121.032086, 68, 1],
	  ['San Juan City Hall', 14.606201,121.023675, 69, 1],
	  ['San Juan Plaza', 14.605443,121.022969, 70, 1],
	  ['SM City Sta.Mesa', 14.605301,121.019199, 71, 1],
	  ['V.Mapa', 14.604482,121.017413, 72, 1],
	  ['Pureza', 14.60184,121.005487, 73, 1],
	  ['Stop on Shop', 14.601591,120.992836, 74, 1],
	  ['SM City Manila', 14.590428,120.984169, 75, 1],
	  ['Central Terminal', 14.592831,120.981804, 76, 1],
    ];

    var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 13,
      center: new google.maps.LatLng(14.589307,121.071961),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });
	
    var marker, i;

    for (i = 0; i < locations.length; i++) {  
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map,
		icon : ( typeof locations[i][4] != 'undefined' ? 'http://gmapsmarkergenerator.eu01.aws.af.cm/getmarker?scale=1&color=58D3F7' : 'http://gmapsmarkergenerator.eu01.aws.af.cm/getmarker?scale=1&color=FE2E2E' )
      });

      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
		    var infowindow = new google.maps.InfoWindow({
			content:'<p>'+locations[i][0]+'</p>',
			});
          infowindow.open(map, marker);
        }
      })(marker, i));
    }
  </script>
</body>
</html>