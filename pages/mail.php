<style>
    .historySendMail-name {
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 100% !important;
            display: flex !important;
        }
    }
</style>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">E-Mails Enviados</h1>
    </div>
    <div class="search-bar">
        <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar por nome de cliente, ou e-mail">
        <button id="search-btn" class="btn btn-primary">Buscar</button>
    </div>
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="name">Cliente
                            <span class="order"></span>
                        </th>
                        <th class="sortable" data-sort="email">E-Mail
                            <span class="order"></span>
                        </th>
                        <th class="sortable" data-sort="subject">Assunto
                            <span class="order"></span>
                        </th>
                        <th class="sortable" data-sort="data_send">Enviado em
                            <span class="order"></span>
                        </th>
                    </tr>
                </thead>
                <tbody id="historySendMail-list">
                    <td colspan='3'><span class="loader"></span></td>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById("searchInput");
    const dedicatedList = document.getElementById('historySendMail-list');

    document.querySelectorAll(".sortable[data-sort]").forEach(element => {
        element.addEventListener("click", () => {
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort');
            const currentOrder = urlParams.get('order');

            let newOrder = 'asc';
            if (element.dataset.sort === currentSort && currentOrder === 'asc') {
                newOrder = 'desc';
            }

            document.querySelectorAll(".sortable span").forEach(span => {
                span.innerHTML = "";
            });

            const indicatorSpan = element.querySelector("span");
            indicatorSpan.innerHTML = newOrder === 'asc' ? '▲' : '▼';

            sortTable(element.dataset.sort, newOrder);
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (element.dataset.sort === urlParams.get('sort')) {
            const currentOrder = urlParams.get('order') || 'asc';
            element.querySelector("span").innerHTML = currentOrder === 'asc' ? '▲' : '▼';
        }
    });

    // Função para ordenar a tabela
    function sortTable(column) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');
        const currentOrder = urlParams.get('order');


        let newOrder = 'asc';
        if (currentSort === column && currentOrder === 'asc') {
            newOrder = 'desc';
        }

        window.history.pushState({}, '', `?page=mail-history&sort=${column}&order=${newOrder}`);

        searchHistorySendMail();
    }

    document.getElementById("searchInput").addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            searchHistorySendMail();
        }
    });


    let searchButton = document.getElementById("search-btn");
    searchButton.addEventListener("click", () => {
        if (searchButton.innerHTML == "Limpar") {
            document.getElementById("searchInput").value = "";
        }
        searchHistorySendMail();
    })

    function searchHistorySendMail() {
        search = (document.getElementById("searchInput").value).trim();
        currentPage = 0;
        dedicatedList.innerHTML = "<tr><td colspan='4' class='text-center'>Carregando...</td></tr>";
        loadMoreHistorySendMail();
        if (search.length > 0) {
            searchButton.innerHTML = "Limpar";
            searchButton.classList.add("btn-danger");
        } else {
            searchButton.innerHTML = "Search";
            searchButton.classList.remove("btn-danger");
        }

    }

    let isLoading = false;
    let currentPage = 0;

    window.addEventListener('scroll', function() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500 && !isLoading) {
            loadMoreHistorySendMail();
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        dedicatedList.innerHTML = "";
        searchHistorySendMail();
    })

    function loadMoreHistorySendMail() {
        isLoading = true;
        currentPage++;
        let search = (searchInput.value).length > 0 ? `&search=${searchInput.value}` : "";
        const urlParams = new URLSearchParams(window.location.search);
        const sortColumn = urlParams.get('sort') || 'data_send';
        const sortOrder = urlParams.get('order') || 'desc';

        fetch(`<?= $site_url ?>api/?op=mail-history-search${search}&page=${currentPage}&sort=${sortColumn}&order=${sortOrder}`)
            .then(response => response.json())
            .then(data => {
                if (currentPage = 1) {
                    dedicatedList.innerHTML = "";
                }
                if (data.content.length > 0) {
                    data.content.forEach(dedicated => {
                        dedicatedList.innerHTML += `
                        <tr>
                            <td>${dedicated.name}</td>
                            <td data-label="E-Mail">${dedicated.email}</td>
                            <td>${dedicated.subject?? ""}</td>
                            <td data-label="Envio">${dedicated.data_send}</td>
                        </tr>
                    `;
                    });
                } else {
                    currentPage = 0;
                    dedicatedList.innerHTML = "<tr><td colspan='4' class='text-center'>Nenhum e-mail encontrado!</td></tr>";
                }
                isLoading = false;
            })
            .catch(error => {
                console.log(error);
                userList.innerHTML = "<tr><td colspan='4' class='text-center'>Erro ao carregar historico de e-mails.</td></tr>";
            });
    }
</script>