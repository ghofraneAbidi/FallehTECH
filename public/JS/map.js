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
    var popupContent = `
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
        <button onclick="saveLandDetails(${polygons.indexOf(polygonData)})">Save</button>
    `;

    polygon.bindPopup(popupContent).openPopup();
}

// Function to save land details and display a circular marker
// Function to save land details and display a circular marker
function saveLandDetails(index) {
    var landName = document.getElementById("land-name").value || "Unnamed";
    var workNeeded = document.getElementById("work-needed").value || "Not specified";
    var numTrees = document.getElementById("num-trees").value || "0";
    var soilType = document.getElementById("soil-type").value || "Unknown";

    var polygonData = polygons[index];

    // Ensure centroid is valid
    if (!polygonData.centroid || polygonData.centroid.length < 2) {
        console.error("❌ Invalid centroid. Marker not added.");
        return;
    }

    // 🌍 **Fix: Ensure correct coordinate order**
    let lat = polygonData.centroid[1]; // Swap order for Leaflet
    let lng = polygonData.centroid[0];

    console.log(`📍 Adding marker for Land ID: ${polygonData.id} at Lat: ${lat}, Lng: ${lng}`);

    // 🛠 **Fix: Use a correctly formatted div icon**
    var landLabel = L.divIcon({
        className: 'circle-marker',
        html: `<div class="circle-marker">${polygonData.id}</div>`,
        iconSize: [30, 30], 
        iconAnchor: [15, 15] // Center correctly
    });

    // 🛠 **Fix: Ensure the marker is placed inside the correct polygon**
    var labelMarker = L.marker([lat, lng], { icon: landLabel, zIndexOffset: 1000 }).addTo(map);
    polygonData.label = labelMarker;

    // 📌 **Ensure polygon remains on the map**
    polygonData.layer.addTo(map);

    // 🛠 **Fix: Prevent movement unless out of bounds**
    if (!map.getBounds().contains(labelMarker.getLatLng())) {
        map.setView([lat, lng], 10, { animate: true });
    }

    // ✅ **Add data to the table**
    var table = document.querySelector("#land-table tbody");
    var newRow = table.insertRow();
    newRow.innerHTML = `
        <td>${polygonData.id}</td>
        <td>${landName}</td>
        <td>${polygonData.area} m²</td>
        <td>${workNeeded}</td>
        <td>${numTrees}</td>
        <td>${soilType}</td>
    `;

    alert(`✅ Land details saved for ${landName}!`);
}

// Function to generate a random color
function getRandomColor() {
    return "#" + Math.floor(Math.random() * 16777215).toString(16);
}

map.on('click', addPoint);
