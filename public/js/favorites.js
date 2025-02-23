document.addEventListener('DOMContentLoaded', function () {
    let favorites = [];  // Store favorite products

    // Function to update favorites count in the navbar
    function updateFavoritesCount() {
        fetch('/favorites/count')  // Make sure this route exists in your Symfony app
            .then(response => response.json())
            .then(data => {
                const favoritesCount = document.getElementById('favorites-count');
                if (favoritesCount) {
                    favoritesCount.textContent = data.count; // Update UI with correct count
                }
            })
            .catch(error => console.error('Erreur de mise à jour des favoris:', error));
    }

    // Function to handle adding/removing favorites
    function toggleFavorite(productId, heartIcon) {
        fetch(`/favoris/add/${productId}`, {  // Updated correct route
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                productId: productId,
                userId: 1  // Static user ID, change dynamically if authentication is available
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                heartIcon.classList.toggle('text-danger');  // Toggle heart color
                alert(data.message); // Show message
                updateFavoritesCount(); // Update counter
            } else {
                alert(data.message || 'Erreur, veuillez réessayer');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        });
    }

    // Initialize heart icons for all products
    function initializeHeartIcons() {
        document.querySelectorAll('.heart-icon').forEach(icon => {
            icon.addEventListener('click', function () {
                const productId = this.closest('.card').dataset.productId;
                toggleFavorite(productId, this);
            });
        });
    }

    // Initialize everything
    initializeHeartIcons();
    updateFavoritesCount(); // Load initial count when page loads
});
