function toggleNav() {
    document.getElementById("nav-menu").classList.toggle("open");
}

const searchForm = document.querySelector('form[action="/recipe/recipes/search.php"]');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        const input = this.querySelector('input[name="q"]');
        if (!input.value.trim()) {
            e.preventDefault();
            alert('Please enter the name of a recipe.');
        }
    });
}

