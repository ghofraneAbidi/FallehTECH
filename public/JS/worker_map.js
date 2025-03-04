document.addEventListener("DOMContentLoaded", function() {
    var map = L.map('map').setView([36.8065, 10.1815], 8); // Centered on Tunisia

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Load registered lands
    fetch('/land/list')
        .then(response => response.json())
        .then(lands => {
            lands.forEach(land => {
                if (!land.coordinates || !Array.isArray(land.coordinates) || land.coordinates.length === 0) {
                    console.error(`❌ Invalid coordinates for land ID ${land.id}`, land);
                    return;
                }

                let polygon = L.polygon(
                    land.coordinates.map(p => [p.latitude, p.longitude]),
                    { color: 'green', fillColor: 'green', fillOpacity: 0.5 }
                ).addTo(map);

                let offersHtml = land.offers && land.offers.length > 0 ? 
                    land.offers.map(offer => `
                        <li><strong>${offer.title}</strong>: ${offer.description} 💰 ${offer.salaire} TND</li>
                    `).join('') : '<li>No offers available</li>';

                let popupContent = `
                    <h3>${land.name}</h3>
                    <p><strong>Owner:</strong> ${land.owner}</p>
                    <p><strong>Area:</strong> ${land.area} m²</p>
                    <h4>Offers:</h4>
                    <ul>${offersHtml}</ul>
                    <button onclick="window.location.href='/land/land/profile/${land.id}'" class="btn btn-primary">View Details</button>
                `;

                // Bind popup but don't open automatically
                polygon.bindPopup(popupContent);

                // ✅ Show popup when hovering over the land
                polygon.on('mouseover', function (e) {
                    this.openPopup();
                });

                // ✅ Close popup when the mouse leaves the land
                polygon.on('mouseout', function (e) {
                    this.closePopup();
                });

                // ✅ Redirect when clicking on the land
                polygon.on('click', function() {
                    window.location.href = `/land/land/profile/${land.id}`;
                });
            });
        })
        .catch(error => console.error("❌ Error loading lands:", error));
});
