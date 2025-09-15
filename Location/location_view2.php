<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Current Location and Address</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        button {
            padding: 12px 25px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #location-info {
            margin-top: 20px;
            font-size: 1.1em;
            color: #333;
            text-align: left;
            word-wrap: break-word;
        }
        #location-info strong {
            color: #007bff;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📍 Your Current Location</h1>
        <button id="getLocationBtn">Get My Location</button>
        <div id="location-info">
            <p>Click the button to find your current location and convert it to an address.</p>
        </div>
    </div>

    <script>
        const getLocationBtn = document.getElementById('getLocationBtn');
        const locationInfoDiv = document.getElementById('location-info');

        getLocationBtn.addEventListener('click', () => {
            if (navigator.geolocation) {
                locationInfoDiv.innerHTML = '<p><strong>Fetching location...</strong></p>';
                navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            } else {
                locationInfoDiv.innerHTML = '<p class="error">Geolocation is not supported by your browser.</p>';
            }
        });

        function successCallback(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            locationInfoDiv.innerHTML = `
                <p><strong>Latitude:</strong> ${latitude}</p>
                <p><strong>Longitude:</strong> ${longitude}</p>
                <p><strong>Converting to address...</strong></p>
            `;

            // Panggil proxy CI untuk ambil alamat
            fetch(`http://localhost/ci_magang/index.php/location/reverse_geocode?lat=${latitude}&lon=${longitude}`)
                .then(response => response.json())
                .then(data => {
                    let address = data.display_name ? data.display_name : 'Address not found';

                    locationInfoDiv.innerHTML = `
                        <p><strong>Latitude:</strong> ${latitude}</p>
                        <p><strong>Longitude:</strong> ${longitude}</p>
                        <p><strong>Address:</strong> ${address}</p>
                    `;

                    // Simpan ke database via AJAX
                    fetch("http://localhost/ci_magang/index.php/location/save_location", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `latitude=${latitude}&longitude=${longitude}&address=${encodeURIComponent(address)}`
                    })
                    .then(res => res.json())
                    .then(res => {
                        console.log("Saved to DB:", res);
                    })
                    .catch(err => console.error("Error saving:", err));
                })
                .catch(error => {
                    locationInfoDiv.innerHTML = `
                        <p><strong>Latitude:</strong> ${latitude}</p>
                        <p><strong>Longitude:</strong> ${longitude}</p>
                        <p class="error">Error fetching address: ${error.message}</p>
                    `;
                });
        }

        function errorCallback(error) {
            let errorMessage = 'An unknown error occurred.';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = 'You denied the request for Geolocation. Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = 'Location information is unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMessage = 'The request to get user location timed out.';
                    break;
                case error.UNKNOWN_ERROR:
                    errorMessage = 'An unknown error occurred.';
                    break;
            }
            locationInfoDiv.innerHTML = `<p class="error">Error getting location: ${errorMessage}</p>`;
        }
    </script>
</body>
</html>
