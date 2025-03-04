var map = L.map('map').setView([36.8065, 10.1815], 8); // Default: Tunis

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

var points = [];
var markers = [];
var polylineLayer = null;
var polygons = [];
var landCounter = 1; // Counter to assign unique IDs

// Function to update the polyline connecting the points
function updatePolyline() {
    if (polylineLayer) {
        map.removeLayer(polylineLayer);
    }
    if (points.length > 1) {
        polylineLayer = L.polyline(points, { color: 'blue', weight: 3, dashArray: '5, 5' }).addTo(map);
    }
}

// Function to add a point to the map
function addPoint(e) {
    var latlng = e.latlng;
    points.push([latlng.lat, latlng.lng]);
    var marker = L.marker(latlng).addTo(map);
    markers.push(marker);
    updatePolyline();

    if (points.length > 2 && isSamePoint(points[0], points[points.length - 1])) {
        closePolygon();
    }
}

// Function to remove the last point added
function removeLastPoint() {
    if (points.length > 0) {
        var lastMarker = markers.pop();
        map.removeLayer(lastMarker);
        points.pop();
        updatePolyline();
    }
}

// Function to check if two points are the same
function isSamePoint(p1, p2) {
    return Math.abs(p1[0] - p2[0]) < 0.0001 && Math.abs(p1[1] - p2[1]) < 0.0001;
}

// Function to calculate the centroid of a polygon
// Function to calculate the centroid of a polygon
function calculateCentroid(coords) {
    var polygon = turf.polygon([[...coords, coords[0]]]); // Close the polygon
    var centroid = turf.centroid(polygon);

    if (!centroid || !centroid.geometry || !centroid.geometry.coordinates) {
        console.error("❌ Centroid calculation failed!", centroid);
        return null;
    }

    let lng = centroid.geometry.coordinates[0]; // Longitude
    let lat = centroid.geometry.coordinates[1]; // Latitude

    console.log(`📍 Corrected Centroid: Latitude = ${lat}, Longitude = ${lng}`);
    
    return [lat, lng]; // Swap to [lat, lng] for Leaflet
}

// Function to close the polygon and display area details
function closePolygon() {
    if (polylineLayer) {
        map.removeLayer(polylineLayer);
        polylineLayer = null;
    }

    var newColor = getRandomColor();
    var polygonLayer = L.polygon(points, { color: newColor, fillColor: newColor, fillOpacity: 0.5 }).addTo(map);
    var area = turf.area(turf.polygon([[...points, points[0]]])).toFixed(2);

    var landId = landCounter++;
    var centroid = calculateCentroid(points);

    if (!centroid) {
        console.error("⚠️ Cannot place marker - centroid is null");
        return;
    }

    console.log(`✅ Land ID ${landId} created at ${centroid[1]}, ${centroid[0]}`);

    var polygonData = { id: landId, layer: polygonLayer, area: area, centroid: centroid };
    polygons.push(polygonData);

    showLandDetailsPopup(polygonLayer, area, polygonData);

    points = [];
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
}

// Function to display land details popup and save data in the table
function showLandDetailsPopup(polygon, area, polygonData) {
    var popupContent = document.createElement('div');
    popupContent.innerHTML = `
        <h3>Enter Land Details</h3>
        <label>Land Name:</label>
        <input type="text" id="land-name"><br>
        <label>Work Needed:</label>
        <input type="text" id="work-needed"><br>
        <label>Number of Trees:</label>
        <input type="number" id="num-trees" min="0"><br>
        <label>Soil Type:</label>
        <select id="soil-type">
            <option value="sandy">Sandy</option>
            <option value="clay">Clay</option>
            <option value="loamy">Loamy</option>
        </select><br>
        <button id="save-land-btn">Save</button>
    `;

    polygon.bindPopup(popupContent).openPopup();

    // ✅ Add event listener properly
    document.getElementById("save-land-btn").addEventListener("click", function () {
        saveLandDetails(polygons.indexOf(polygonData));
    });
}

// Function to save land details and display a circular marker
// Function to save land details and display a circular marker
function saveLandDetails(index) {
    console.log(`🔍 Saving land details for index: ${index}`);

    var landNameInput = document.getElementById("land-name");
    if (!landNameInput) {
        console.error("❌ Land name input field not found!");
        alert("⚠️ Land name input field not found!");
        return;
    }

    var landName = landNameInput.value.trim() || "Unnamed";

    var polygonData = polygons[index];
    if (!polygonData) {
        console.error("❌ Polygon data not found for index:", index);
        return;
    }

    let coordinates = polygonData.layer.getLatLngs()[0].map(p => ({
        latitude: p.lat,
        longitude: p.lng
    }));

    let landData = {
        name: landName,
        area: polygonData.area,
        coordinates: coordinates // ✅ Only sending name, area, and coordinates
    };

    console.log("📝 Sending land data to API:", landData);

    fetch('/land/create', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(landData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || "Server error"); });
        }
        return response.json();
    })
    .then(data => {
        console.log("✅ Server Response:", data);
        alert(`✅ ${landName} has been saved successfully!`);

        // ✅ Update popup with saved details
        polygonData.layer.bindPopup(`
            <strong>${landName}</strong><br>
            📏 Area: ${polygonData.area} m²
        `).openPopup();

        loadLands(); // Reload saved lands
    })
    .catch(error => {
        console.error("❌ Error saving land:", error);
        alert("⚠️ Failed to save land. Check console for details.");
    });
}



// Function to generate a random color
function getRandomColor() {
    return "#" + Math.floor(Math.random() * 16777215).toString(16);
}

map.on('click', addPoint);

function loadLands() {
    fetch('/land/list')
    .then(response => response.text()) // Get raw text response
    .then(text => {
        console.log("🔍 Raw API Response:", text); // Log raw response

        try {
            const lands = JSON.parse(text); // Try to parse as JSON
            console.log("🌍 Parsed Lands:", lands);

            lands.forEach(land => {
                if (!land.coordinates || !Array.isArray(land.coordinates) || land.coordinates.length === 0) {
                    console.error(`❌ Invalid coordinates for land ID ${land.id}`, land);
                    return;
                }

                let polygon = L.polygon(
                    land.coordinates.map(p => [p.latitude, p.longitude]), 
                    { color: 'green' }
                ).addTo(map);

                let offersHtml = land.offers && land.offers.length > 0 ? 
                    land.offers.map(offer => `
                        <li>
                            <strong>${offer.title}</strong>: ${offer.description} <br>
                            💰 Price: ${offer.price} TND
                        </li>
                    `).join('') : '<li>No offers available</li>';

                let popupContent = `
                    <h3>${land.name}</h3>
                    <p><strong>Owner:</strong> ${land.owner}</p>
                    <p><strong>Area:</strong> ${land.area} m²</p>
                    <h4>Offers:</h4>
                    <ul>${offersHtml}</ul>
                `;

                polygon.bindPopup(popupContent);
            });
        } catch (error) {
            console.error("❌ Error parsing JSON:", error);
            console.log("🔍 API Response that caused error:", text);
        }
    })
    .catch(error => console.error("❌ Error loading lands:", error));
}


// 🔥 Load lands when the page is ready
document.addEventListener("DOMContentLoaded", loadLands);



function saveLand() {
    let landNameInput = document.getElementById("land-name");
    
    if (!landNameInput) {
        alert("⚠️ Land name input field not found!");
        return;
    }

    let landName = landNameInput.value.trim();
    if (!landName) {
        alert("⚠️ Please enter a land name.");
        return;
    }

    if (points.length < 3) {
        alert("⚠️ A land must have at least 3 points to form a valid polygon.");
        return;
    }

    let area = calculateArea(points);
    let coordinates = points.map(p => ({ latitude: p[0], longitude: p[1] }));

    console.log("📝 Sending data to API:", { name: landName, area, coordinates });

    fetch('/land/create', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: landName,
            area: area,
            coordinates: coordinates
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || "Server error"); });
        }
        return response.json();
    })
    .then(data => {
        console.log("✅ Server Response:", data);
        if (data.message) {
            alert(`✅ ${data.message}`);
            loadLands(); // Refresh the map
        } else {
            alert("⚠️ Error: " + data.error);
        }
    })
    .catch(error => console.error("❌ Error saving land:", error));
}
let ownerColors = {}; // ✅ Store colors outside the fetch function to persist

// ✅ Store drawn polygons globally to remove them before reloading
let landLayers = [];

function loadLands() {
    fetch('/land/list')
        .then(response => response.json())
        .then(lands => {
            console.log("🌍 Full API Response:", lands);

            // ✅ Remove previous land layers before adding new ones
            landLayers.forEach(layer => map.removeLayer(layer));
            landLayers = [];

            lands.forEach(land => {
                if (!land.coordinates || !Array.isArray(land.coordinates) || land.coordinates.length === 0) {
                    console.error(`❌ Invalid coordinates for land ID ${land.id}`, land);
                    return;
                }

                let ownerName = land.owner;
                if (!ownerColors[ownerName]) {
                    ownerColors[ownerName] = getRandomColor(); // Assign a new color if not already assigned
                }
                let ownerColor = ownerColors[ownerName]; // Use the same color for this owner's lands

                let polygon = L.polygon(
                    land.coordinates.map(p => [p.latitude, p.longitude]), 
                    { color: ownerColor, fillColor: ownerColor, fillOpacity: 0.5 }
                ).addTo(map);

                landLayers.push(polygon); // ✅ Store polygon to remove it on reload

                // ✅ Check if offers exist
                let offersHtml = land.offers && land.offers.length > 0 ? 
                    land.offers.map(offer => `
                        <li>
                            <strong>${offer.title}</strong>: ${offer.description} <br>
                            💰 Salary: ${offer.salaire} TND
                        </li>
                    `).join('') : '<li>No offers available</li>';

                // ✅ Show owner & offers in popup
                let popupContent = `
                    <h3>${land.name}</h3>
                    <p><strong>Owner:</strong> ${land.owner}</p>
                    <p><strong>Area:</strong> ${land.area} m²</p>
                    <h4>Offers:</h4>
                    <ul>${offersHtml}</ul>
                `;

                polygon.bindPopup(popupContent);
            });
        })
        .catch(error => {
            console.error("❌ Error loading lands:", error);
        });
}

// ✅ Function to generate random colors
function getRandomColor() {
    return "#" + Math.floor(Math.random() * 16777215).toString(16);
}

// ✅ Load lands when the page is ready
document.addEventListener("DOMContentLoaded", loadLands);
