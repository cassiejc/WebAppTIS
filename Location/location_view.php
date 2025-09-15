<!DOCTYPE html>
<html>
<head>
    <title>Geolocation in CodeIgniter</title>
</head>
<body>
    <button onclick="getLocation()">Get My Location</button>
    <p id="output"></p>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                document.getElementById("output").innerText = "Geolocation not supported.";
            }
        }

        function showPosition(position) {
            let latitude = position.coords.latitude;
            let longitude = position.coords.longitude;
            document.getElementById("output").innerText =
                "Latitude: " + latitude + ", Longitude: " + longitude;
        }

        function showError(error) {
            alert("Error getting location: " + error.message);
        }
    </script>
</body>
</html>
