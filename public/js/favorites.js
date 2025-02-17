document.addEventListener('DOMContentLoaded', function () {
    let favorites = [];  // Array to hold favorite products

    // Update the favorites count in the navbar
    function updateFavoritesCount() {
        const favoritesCount = document.getElementById('favorites-count');
        if (favoritesCount) {
            favoritesCount.textContent = favorites.length;
        }
    }

    // Heart icon functionality for adding/removing from favorites
    function initializeHeartIcons() {
        document.querySelectorAll('.heart-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const productId = this.closest('.card').dataset.productId;

                // Make API request to add to favorites
                fetch(`/produit/${productId}/add-to-favorites`, {  // Update URL to match the route
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        productId: productId,
                        isFavorite: true,
                        userId: 1  // Static user ID (can be dynamic based on logged-in user)
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Produit ajouté aux favoris');
                        this.classList.add('text-danger');  // Change heart color to red
                        updateFavoritesCount();
                    } else {
                        alert('Erreur, veuillez réessayer');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue. Veuillez réessayer.');
                });
            });
        });
    }

    // Initialize heart icons when the page loads
    initializeHeartIcons();

    // You can call your updateFavoritesCount method if you have a way to load existing favorites at the start
});
