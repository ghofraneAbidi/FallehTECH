document.addEventListener('DOMContentLoaded', function () {
    let favorites = []; // Array to hold favorite products

    // Update the favorites count in the navbar
    function updateFavoritesCount() {
        const favoritesCount = document.getElementById('favorites-count');
        favoritesCount.textContent = favorites.length;
    }

    // Add heart icon functionality
    document.querySelectorAll('.heart-icon i').forEach(icon => {
        icon.addEventListener('click', function (event) {
            const card = this.closest('.card');
            const productId = card.dataset.productId;
            const productName = card.dataset.productName;
            const productImage = card.dataset.productImage;
            const productPrice = card.dataset.productPrice;

            // Add the product to the favorites array
            const product = { id: productId, name: productName, image: productImage, price: productPrice, stock: 'En stock' };

            // Prevent duplicates
            if (!favorites.find(fav => fav.id === productId)) {
                favorites.push(product);
            }

            // Update favorites count
            updateFavoritesCount();

            // Optionally save to database
            fetch('/favoris/add/' + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name: productName, image: productImage, price: productPrice })
            });

            // Update heart icon color
            this.style.color = "red";

            alert("Produit ajouté aux favoris!");
            
            // Update favorites modal
            updateFavoritesModal();
        });
    });

    // Update the favorites modal
    function updateFavoritesModal() {
        const favoritesList = document.getElementById('favorites-list');
        favoritesList.innerHTML = ''; // Clear the modal list

        favorites.forEach(favorite => {
            const listItem = document.createElement('tr');
            listItem.innerHTML = `
                <td><img src="${favorite.image}" alt="${favorite.name}" style="width: 50px; height: 50px;"></td>
                <td><strong>${favorite.name}</strong></td>
                <td>${favorite.price} €</td>
                <td>${favorite.stock}</td>
                <td><button class="btn btn-danger btn-sm" onclick="removeFromFavorites(${favorite.id})">Retirer</button></td>
            `;
            favoritesList.appendChild(listItem);
        });
    }

    // Remove from favorites
    window.removeFromFavorites = function (productId) {
        favorites = favorites.filter(product => product.id !== productId);
        updateFavoritesCount();
        updateFavoritesModal();
        
        // Optionally remove from database
        fetch('/favoris/remove/' + productId, {
            method: 'POST',
        });
        
        // Update heart icon color
        const heartIcon = document.querySelector(`[data-product-id="${productId}"] .heart-icon i`);
        if (heartIcon) {
            heartIcon.style.color = ""; // Reset color
        }
    };

    // Show the modal for favorites
    document.getElementById('showFavoritesBtn').addEventListener('click', function () {
        updateFavoritesModal();
        const favoritesModal = new bootstrap.Modal(document.getElementById('favoritesModal'));
        favoritesModal.show();
    });
});


    // Open modal for product details
    document.querySelectorAll('.btn-details').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productDescription = this.dataset.productDescription;
            const productPrice = this.dataset.productPrice;
            const productImage = this.dataset.productImage;

            document.getElementById('productName').textContent = productName;
            document.getElementById('productDescription').textContent = productDescription;
            document.getElementById('productPrice').textContent = productPrice;
            document.getElementById('productImage').src = productImage;

            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        });
    });

