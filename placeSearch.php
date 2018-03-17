<?php
//  avoid ssl verify
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);
$googleKey = "AIzaSyD2pdznycdiUiGqjriuApAQwEY5RoL1Q-8"; // get one on google api website




//submit search
    $keyword="";
    $category ="default";
    $raw_distance=10;
    $otherLocationName="";
    $place_id="";
$placesNearByjson = 1111;
$currentPlaceDetailsjson =2222;

    if(isset($_POST["submit"])) {
        $keyword = trim($_POST["keyword"]);
        $category = $_POST["category"];
        if(isset($_POST["otherLocationName"])) {
            $otherLocationName = $_POST["otherLocationName"];
        }else{
            $otherLocationName="";
        }

        $distance=$raw_distance = trim($_POST["distance"]);

        if(isset($_POST["place_id"]) && !empty($_POST["place_id"])) {
            $place_id = $_POST["place_id"];
            $currentPlaceDetails = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".$place_id."&key=".$googleKey;
            $currentPlaceDetailsjson = file_get_contents($currentPlaceDetails, false, stream_context_create($arrContextOptions));
            $currentPlaceDetailsjson_ret =json_decode($currentPlaceDetailsjson,true);
            for($i =0;$i<sizeof($currentPlaceDetailsjson_ret['result']['photos'])&& $i < 5;$i++){
                $photo = $currentPlaceDetailsjson_ret['result']['photos'][$i];
                $url= "https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference=".$photo['photo_reference']."&key=".$googleKey;
                $file = file_get_contents($url, false, stream_context_create($arrContextOptions));
                file_put_contents("./image".$i.".png",$file);
            }
        }else {


            if ($distance == "") {
                $distance = "10";
            }
            $distance = (int)$distance * 1609.34;
            $location = $_POST["location"];
            $lat = 34.0223519;
            $lng = -118.285117;
            if ($location != "here") {
                $locationText = "";
                $locationText = trim($_POST["otherLocationName"]);
                $locationText = str_replace(",", "", $locationText);
                $location = str_replace(" ", "+", $locationText);
                $locationGeoCode = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $location . "&key=" . $googleKey;
                $locationGeoCodejson = file_get_contents($locationGeoCode, false, stream_context_create($arrContextOptions));
                $locationGeoCodeResult = json_decode($locationGeoCodejson, true);
                $lat = $locationGeoCodeResult["results"][0]["geometry"]["location"]["lat"];
                $lng = $locationGeoCodeResult["results"][0]["geometry"]["location"]["lng"];
            } else {
                $locationGeoCodejson = file_get_contents("http://ip-api.com/json");
                $locationGeoCodeResult = json_decode($locationGeoCodejson, true);
                $lat = $locationGeoCodeResult["lat"];
                $lng = $locationGeoCodeResult["lon"];
            }
            $placesNearBy = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=" . $lat . "," . $lng . "&radius=" . $distance . "&type=" . $category . "&keyword=" . $keyword . "&key=" . $googleKey;
            $placesNearByjson = file_get_contents($placesNearBy, false, stream_context_create($arrContextOptions));
//        $placesNearByjson = 2222;
        }
    }
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Search Page</title>
    <style type="text/css">
    form {
        width: 60%;
        margin: 0 auto;
        padding: 10px;
        border: 2px solid grey;
        background-color: #F5F5F5;
    }
    table {
        width: 60%;
        margin: 0 auto;
    }
    a {
        text-decoration: none;
    }
    #placeName, #reviewDiv, #pictureDiv {
        width: 60%;
        margin: 0 auto;
    }
    .div_button{
        background-color:lightgray;
    }
    .div_button:hover{
        background-color:gray;
        cursor:pointer;
    }
    </style>
    <script type="text/javascript">
        function radioCheck() { //enable and required location text when location is checked ~
            if(document.getElementById('here').checked==true) {
                document.getElementById('otherLocationName').disabled=true;
                document.getElementById('otherLocationName').required=false;
            }
            else {
                document.getElementById('otherLocationName').disabled=false;
                document.getElementById('otherLocationName').required=true;
                document.getElementById('searchButton').disabled=false;
            }
        }

        function showResultTable() { // show result table after submit
            console.log(document.getElementById('keyword').value);
            placeNearByjson = <?php echo $currentPlaceDetailsjson; ?>;
        }

        function singlePlace(resultId) {
            document.getElementById("place_id").value=resultId;
            document.getElementById("myform").submit.click();
        }

        function showPhotos() {
            var reviews= document.getElementById("reviews");
            var photos= document.getElementById("photos");
            if(photos.style.display=="none") {
                document.getElementById("photo_image").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                photos.style.display="block";
                document.getElementById("review_image").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                reviews.style.display="none";
            }else{
                document.getElementById("photo_image").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                photos.style.display="none";
            }
        }
        function showReviews() {
            var reviews= document.getElementById("reviews");
            var photos= document.getElementById("photos");
            if(reviews.style.display=="none") {
                document.getElementById("review_image").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                reviews.style.display="block";
                document.getElementById("photo_image").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                photos.style.display="none";
            }else{
                document.getElementById("review_image").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                reviews.style.display="none";
            }
        }

        function clearForm() {  //function for the clear button in the form
            document.getElementById('keyword').value="";
            document.getElementById('category').value="default";
            document.getElementById('distance').value="";
            document.getElementById('here').checked=true;
            document.getElementById('otherLocationName').value="";
        }
        document.addEventListener("click", function(event){
            var target =event.target;
            if(target.className === "div_button"){
                toThere(target);
            }
        });
        function toThere(target){
            var travelMode;
            if(target.textContent ==="drive there"){
                travelMode ="DRIVING";
            }
            if(target.textContent ==="walk there"){
                travelMode ="WALKING";
            }
            if(target.textContent ==="bike there"){
                travelMode ="BICYCLING";
            }
            var haight = new google.maps.LatLng(window.originPlace.lat, window.originPlace.lng);
            var oceanBeach = new google.maps.LatLng(window.destinPlace.lat, window.destinPlace.lng);
            var request = {
                origin: haight,
                destination: oceanBeach,
                // Note that Javascript allows us to access the constant
                // using square brackets and a string value as its
                // "property."
                travelMode: travelMode
            };

            directionsService.route(request, function(response, status) {
                if (status == 'OK') {
                    directionsDisplay.setDirections(response);
                }
            });
        }
        function showMap(element, lat, lng){
            // toggle map
            if(document.getElementById('map').style.display == "none"){
                document.getElementById('map').style.display = "block";
                // position
                var rect=element.getBoundingClientRect();
                var bodyRect = document.body.getBoundingClientRect();
                document.getElementById('map').style.left = rect.x - bodyRect.x;
                document.getElementById('map').style.top = rect.bottom - bodyRect.y;
                // center
                var uluru =  {lat:  lat, lng: lng};
                window.destinPlace= uluru;
                var map = new google.maps.Map(document.getElementById('map_inner'), {
                    zoom: 8,
                    center: uluru
                });
                var marker = new google.maps.Marker({
                    position: uluru,
                    map: map
                });
                window.directionsService = new google.maps.DirectionsService();
                window.directionsDisplay = new google.maps.DirectionsRenderer();
                window.directionsDisplay.setMap(map);

            }else{
                document.getElementById('map').style.display = "none";
            }



        }
    </script>
</head>
    <form name="myform" id="myform" method="POST" >
        <h1 align="center"><i>Travel and Entertainment Search</i></h1>
        <hr>
        <b>Keyword</b><input type="text" name="keyword" id="keyword" maxlength="255" size="20" required value="<?php echo $keyword?>"/>
        <br />
        <b>Category</b> <select name="category" id="category" value="<?php echo $category?>">
            <option value="default">default</option>
            <option value="cafe">cafe</option>
            <option value="bakery">bakery</option>
            <option value="restaurant">restaurant</option>
            <option value="beauty_salon">beauty salon</option>
            <option value="casino">casino</option>
            <option value="movie_theater">movie theater</option>
            <option value="lodging">lodging</option>
            <option value="airport">airport</option>
            <option value="train_station">train station</option>
            <option value="subway_station">subway station</option>
            <option value="bus_station">bus station</option>
        </select>
        <br />
        <ul style="list-style: none; margin:0; padding:0">
        <li style="float: left"><b>Distance(miles)</b><input type="text" name="distance" id="distance" size="20" placeholder="10" value="<?php echo $raw_distance?>"/></li>
        <li style="float: left"><b>&nbsp;from&nbsp;</b>
            <input type="radio" name="location" id="here" checked="checked" value="here" onclick="radioCheck()">Here<br />
        <b style="visibility: hidden;">&nbsp;from&nbsp;</b>
        <input type="radio" name="location" id="otherLocation" onclick="radioCheck()" value="otherLocation"/>
        <input type="text" name="otherLocationName" id="otherLocationName" maxlength="255" size="20" placeholder="location" value="<?php echo $otherLocationName?>" disabled/></li>
        </ul>
        <br /><br /><br /><br />
        <div style="padding-left: 75px">
        <input type="submit" name="submit" id="searchButton" value="Search" onclick="showResultTable()"/>
        <input type="button" name="clear" value="Clear" onclick="clearForm()"/>
            <input id="place_id" type="place_id" name="place_id" style="display:none" value=""/>
        </div>
    </form>
    <div id="singlePlaceReview" style="text-align:center">
        <?php
            if($currentPlaceDetailsjson != 2222) {
                $currentPlaceDetailsjson = json_decode($currentPlaceDetailsjson, true);
                 $place_result = $currentPlaceDetailsjson['result'];
        ?>
            <div id="placeName"><h5>  <?php echo $place_result['name'] ?></h5></div><br />
            <div id="reviewDiv"><p>click to show reviews</p>
                <a href="#" onclick="showReviews(); return false;">
                <img id="review_image" src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png" width='50' height='20'>
                </a>
                    <div id="reviews" style="display:none">
                        <?php
                        $reviews = $place_result['reviews'];
                        if(sizeof($reviews) == 0) {
                        ?>
                         <table border="2">
                             <tr><td><b>No reviews found</b></td></tr>
                         </table>
                        <?php
                        }else{
                            $review_html="<table border=\"2\">";
                            for ($i = 0; $i < sizeof($reviews) && $i < 5; $i++) {
                                $review_html.="<tr><td style=\"text-align:center\"><img width=16 height=16 src=\"".$reviews[$i]['profile_photo_url']."\">".$reviews[$i]['author_name']."</td></tr>";
                                 $review_html.="<tr><td>".$reviews[$i]['text']."</td></tr>";
                            }
                            $review_html.="</table>";
                            echo $review_html;
                        }
                        ?>
                    </div>
            </div>
            <div id="pictureDiv"><p>click to show photos</p>
                <a href="#" onclick="showPhotos(); return false;">
                <img  id="photo_image" src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png" width='50' height='20'>
                </a>
                    <div id="photos" style="display:none">
                        <?php
                        $photos = $place_result['photos'];
                        if(sizeof($photos) == 0) {
                            ?>
                            <table border="2">
                                <tr><td><b>No photos found</b></td></tr>
                            </table>
                            <?php
                        }else{
                            $photo_html="<table border=\"2\">";
                            for ($i = 0; $i < sizeof($photos) && $i < 5; $i++) {
                                $photo_html.="<tr><td><a target=\"_blank\" href=\"./image".$i.".png\"><img  src=\"image".$i.".png\"></a></td></tr>";
                            }
                            $photo_html.="</table>";
                            echo $photo_html;
                        }
                        ?>
                    </div>
            </div>
        <?php
            }
        ?>
    </div>
    <p id="resultTable">
        <?php
            if($placesNearByjson != 1111) {
                $placesNearByjson = json_decode($placesNearByjson, true);
                $result = $placesNearByjson['results'];
                $html_text = "<table border='2'><tbody>";
                if (sizeof($result) == 0) {
                    $html_text .= "<tr><td>No Records has been found</td></tr>";
                } //establish result table data
                else {
                    $html_text .= "<tr><th>Category</th><th>Name</th><th>Address</th></tr>";
                    for ($i = 0; $i < sizeof($result); $i++) {
                        $html_text .= "<tr><td><img src='" . $result[$i]['icon'] .
                            "'</td><td><a href=\"#\" onclick=\"singlePlace('" . $result[$i]['place_id'] . "'); return false;\">" . $result[$i]['name'] .
                            "</td><td><a href=\"#\" onclick=\"showMap(this," . $result[$i]['geometry']['location']['lat'].",".$result[$i]['geometry']['location']['lng']. "); return false;\">" . $result[$i]['vicinity'] .
                            "</td></tr>";
                    }
                }
                $html_text .= "</tbody></table>";
                echo $html_text;
            }
        ?>
    </p>
    <div id="map" style="width:400px;height:280px;display:none;position:absolute">
        <div style="position:absolute;z-index:1">
            <div class="div_button">walk there</div>
            <div class="div_button">bike there</div>
            <div class="div_button">drive there</div>
        </div>
        <div id="map_inner"  style="width:100%;height:100%">
    </div>
    </div>
    <script type="text/javascript">
        function getIpApi(){ //ip-api.com callback function to get user location(a.k.a. here)
            console.log(arguments[0].status);
            console.log(arguments[0].lat);
            console.log(arguments[0].lon);
            window.originPlace ={
                lat:arguments[0].lat,
                lng:arguments[0].lon
            };
            document.getElementById("keyword").required=true;
            document.getElementById("searchButton").disabled=true;
            if(arguments[0].status == 'success') {
                document.getElementById("searchButton").disabled=false;
            }
            /*xmlhttp0 = new XMLHttpRequest();
            URLgeo = "place.php?lat=" + arguments[0].lat + "&lon=" + arguments[0].lon;
            xmlhttp0.open("GET", URLgeo, true);
            xmlhttp0.send();*/
        }
        function initMap() {
            var uluru = {lat: -25.363, lng: 131.044};
            window.directionsService = new google.maps.DirectionsService();
            window.directionsDisplay = new google.maps.DirectionsRenderer();

            window.map = new google.maps.Map(document.getElementById('map_inner'), {
                zoom: 8,
                center: uluru
            });
            var marker = new google.maps.Marker({
                position: uluru,
                map: map
            });
            window.directionsDisplay.setMap(map);
        }
    </script>
    <script type="text/javascript" src="http://ip-api.com/json/?callback=getIpApi"></script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD2pdznycdiUiGqjriuApAQwEY5RoL1Q-8&callback=initMap">
    </script>
</body>
</html>
