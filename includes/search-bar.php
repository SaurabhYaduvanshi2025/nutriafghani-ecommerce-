<div class="modal fade modal-search" id="search">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Search</h5>
                <span class="icon-close icon-close-popup" data-bs-dismiss="modal"></span>
            </div>
            <form class="form-search" action="shop.php" method="get" id="siteSearchForm">
                <fieldset class="text">
                    <input
                        type="text"
                        placeholder="Search products or categories..."
                        class=""
                        name="search"
                        id="siteSearchInput"
                        tabindex="0"
                        value=""
                        aria-required="true"
                        required="" />
                </fieldset>
                <button class="" type="submit">
                    <svg
                        class="icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z"
                            stroke="#181818"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"></path>
                        <path
                            d="M21.35 21.0004L17 16.6504"
                            stroke="#181818"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"></path>
                    </svg>
                </button>
            </form>
            <div class="search-results-wrap" id="siteSearchResults" style="display: none;">
                <div class="search-result-group" id="siteSearchCategories"></div>
                <div class="search-result-group" id="siteSearchProducts"></div>
            </div>
            <div>
                <h5 class="mb_16">Feature Keywords</h5>
                <ul class="list-tags">
                    <li><a href="#" class="radius-60 link">Afghan Almonds</a></li>
                    <li><a href="#" class="radius-60 link">Premium Cashews</a></li>
                    <li><a href="#" class="radius-60 link">Soft Dates</a></li>
                    <li><a href="#" class="radius-60 link">Afghan Pistachios</a></li>
                    <li><a href="#" class="radius-60 link">Afghan Walnuts</a></li>
                    <li><a href="#" class="radius-60 link">Afghan Raisins</a></li>
                    <li><a href="#" class="radius-60 link">Dry Fruit Mix</a></li>
                    <li><a href="#" class="radius-60 link">Premium Anjeer</a></li>
                    <li><a href="#" class="radius-60 link">Organic Dry Fruits</a></li>
                    <li><a href="#" class="radius-60 link">Healthy Snacking</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .search-results-wrap {
        margin: 22px 0;
        display: grid;
        gap: 18px;
    }

    .search-result-group h6 {
        margin-bottom: 10px;
        font-weight: 700;
    }

    .search-result-list {
        display: grid;
        gap: 10px;
    }

    .search-result-item {
        display: grid;
        grid-template-columns: 56px 1fr;
        align-items: center;
        gap: 12px;
        color: inherit;
        padding: 8px;
        border: 1px solid #eee;
        border-radius: 8px;
    }

    .search-result-item img {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 6px;
    }

    .search-result-title {
        display: block;
        font-weight: 600;
        line-height: 1.3;
    }

    .search-result-meta {
        display: block;
        color: #777;
        font-size: 13px;
        margin-top: 3px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('siteSearchInput');
    const resultsWrap = document.getElementById('siteSearchResults');
    const categoryBox = document.getElementById('siteSearchCategories');
    const productBox = document.getElementById('siteSearchProducts');

    if (!input || !resultsWrap || !categoryBox || !productBox) {
        return;
    }

    let timer = null;
    let controller = null;

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function renderGroup(title, items, metaBuilder) {
        if (!items.length) {
            return '';
        }

        const list = items.map(function (item) {
            return `
                <a href="${escapeHtml(item.url)}" class="search-result-item">
                    <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
                    <span>
                        <span class="search-result-title">${escapeHtml(item.name)}</span>
                        <span class="search-result-meta">${escapeHtml(metaBuilder(item))}</span>
                    </span>
                </a>
            `;
        }).join('');

        return `<h6>${escapeHtml(title)}</h6><div class="search-result-list">${list}</div>`;
    }

    function clearResults() {
        categoryBox.innerHTML = '';
        productBox.innerHTML = '';
        resultsWrap.style.display = 'none';
    }

    function runSearch() {
        const query = input.value.trim();

        if (query.length < 2) {
            clearResults();
            return;
        }

        if (controller) {
            controller.abort();
        }

        controller = new AbortController();

        fetch('search-process.php?q=' + encodeURIComponent(query), {
            signal: controller.signal,
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                const categories = Array.isArray(data.categories) ? data.categories : [];
                const products = Array.isArray(data.products) ? data.products : [];

                categoryBox.innerHTML = renderGroup('Categories', categories, function (item) {
                    return item.count + (Number(item.count) === 1 ? ' item' : ' items');
                });
                productBox.innerHTML = renderGroup('Products', products, function (item) {
                    return (item.category ? item.category + ' - ' : '') + item.price;
                });

                resultsWrap.style.display = categories.length || products.length ? 'grid' : 'block';
                if (!categories.length && !products.length) {
                    productBox.innerHTML = '<p class="text-secondary">No products or categories found.</p>';
                }
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    clearResults();
                }
            });
    }

    input.addEventListener('input', function () {
        window.clearTimeout(timer);
        timer = window.setTimeout(runSearch, 250);
    });
});
</script>
