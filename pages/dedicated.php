<?php
$zabbix = new ZabbixAPI();
?>
<style>
    .dedicated-name {
        font-weight: 500;
    }

    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .buttons-inline {
        display: flex;
        flex-direction: row;
        gap: 4px;
    }

    #dedicated-inputs {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 100% !important;
            display: flex !important;
        }
    }

    .center {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Clientes Dedicados</h1>
    </div>
    <div class="search-bar">
        <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar por nome, endereço ou e-mail">
        <button id="search-btn" class="btn btn-primary">Buscar</button>
    </div>
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="name">Nome
                            <span class="order"></span>
                        </th>
                        <th class="sortable" data-sort="email">E-Mail
                            <span class="order"></span>
                        </th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody id="dedicated-list">
                    <td colspan='3'><span class="loader"></span></td>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="custom-modal-overlay" id="dedicated-modal-overlay"></div>
<div class="custom-modal" id="dedicated-modal">
    <div class="modal-header">
        <h2></h2>
        <button class="close-modal">X</button>
    </div>
    <div class="modal-content">
    </div>
    <div class="modal-footer">
    </div>
</div>

<script>
    const searchInput = document.getElementById("searchInput");
    const dedicatedList = document.getElementById('dedicated-list');

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

        window.history.pushState({}, '', `?page=dedicated&sort=${column}&order=${newOrder}`);

        searchDedicated();
    }

    document.getElementById("searchInput").addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            searchDedicated();
        }
    });


    let searchButton = document.getElementById("search-btn");
    searchButton.addEventListener("click", () => {
        if (searchButton.innerHTML == "Limpar") {
            document.getElementById("searchInput").value = "";
        }
        searchDedicated();
    })

    function searchDedicated() {
        search = (document.getElementById("searchInput").value).trim();
        currentPage = 0;
        dedicatedList.innerHTML = "<tr><td colspan='3' class='text-center'>Carregando...</td></tr>";
        loadMoreDedicated();
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
            loadMoreDedicated();
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        dedicatedList.innerHTML = "";
        searchDedicated();
    })

    function loadMoreDedicated() {
        isLoading = true;
        currentPage++;
        let search = (searchInput.value).length > 0 ? `&search=${searchInput.value}` : "";
        const urlParams = new URLSearchParams(window.location.search);
        const sortColumn = urlParams.get('sort') || 'name';
        const sortOrder = urlParams.get('order') || 'asc';

        fetch(`<?= $site_url ?>api/?op=dedicated-search${search}&page=${currentPage}&sort=${sortColumn}&order=${sortOrder}`)
            .then(response => response.json())
            .then(data => {
                if (currentPage = 1) {
                    dedicatedList.innerHTML = "";
                }
                if (data.dedicateds.length > 0) {
                    data.dedicateds.forEach(dedicated => {
                        dedicatedList.innerHTML += `
                        <tr>
                            <td>${dedicated.name}</td>
                            <td data-label="E-Mail">${dedicated.email}</td>
                            <td>
                                <div class="action-buttons actions">
                                    <button class="btn btn-sm btn-outline-primary me-1 btn-open-modal" 
                                            data-modal="dynamic" data-modaltype="dedicated-details" 
                                            data-hostid="${dedicated.id}" title="Dados">
                                        <i class="bi bi-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary me-1 btn-open-modal" 
                                            data-modal="dynamic" data-modaltype="dedicated-history" 
                                            data-hostid="${dedicated.id}" title="Dados">
                                        <i class="bi bi-calendar-week"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    });
                }else {
                    currentPage = 0;
                    dedicatedList.innerHTML = "<tr><td colspan='3' class='text-center'>Nenhum cliente encontrado!</td></tr>";
                }
                isLoading = false;
                loadInteraction();
            })
            .catch(error => {
                console.log(error);
                userList.innerHTML = "<tr><td colspan='3' class='text-center'>Erro ao carregar clientes.</td></tr>";
            });
    }

    loadInteraction();

    function loadInteraction() {
        document.querySelectorAll(".btn-open-modal[data-modal]").forEach(item => {
            item.addEventListener("click", () => {
                let type = item.dataset.modaltype;
                let modalBody = document.getElementById("dedicated-modal").querySelector(".modal-content");
                modalBody.innerHTML = "";

                if (type) {
                    switch (type) {
                        case "dedicated-details":
                            fetch(`<?= $site_url ?>?modal=dedicated-details&dedicatedId=${item.dataset.hostid}`)
                                .then(response => response.text())
                                .then(data => {
                                    modalBody.innerHTML = data;

                                    const oldScripts = document.querySelectorAll(`script[data-dynamic-modal]`);
                                    oldScripts.forEach(script => script.remove());

                                    const scripts = modalBody.querySelectorAll('script');
                                    scripts.forEach(script => {
                                        const newScript = document.createElement('script');
                                        if (script.src) {
                                            newScript.src = script.src;
                                        } else {
                                            newScript.textContent = script.textContent;
                                        }
                                        newScript.setAttribute(`data-dynamic-modal`, 'true');
                                        document.body.appendChild(newScript);
                                    });
                                })
                                .catch(error => {
                                    console.error('Erro ao carregar o modal:', error);
                                    modalBody.innerHTML = `
                                <div class="modal-header">
                                    <h5 class="modal-title">Erro</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-danger">Ocorreu um erro ao carregar os detalhes do dedicated.</div>
                                </div>
                            `;
                                });
                            break;
                        case "dedicated-history":
                            fetch(`<?= $site_url ?>?modal=dedicated-history&dedicatedId=${item.dataset.hostid}`)
                                .then(response => response.text())
                                .then(data => {
                                    modalBody.innerHTML = data;

                                    const oldScripts = document.querySelectorAll(`script[data-dynamic-modal]`);
                                    oldScripts.forEach(script => script.remove());

                                    const scripts = modalBody.querySelectorAll('script');
                                    scripts.forEach(script => {
                                        const newScript = document.createElement('script');
                                        if (script.src) {
                                            newScript.src = script.src;
                                        } else {
                                            newScript.textContent = script.textContent;
                                        }
                                        newScript.setAttribute(`data-dynamic-modal`, 'true');
                                        document.body.appendChild(newScript);
                                    });
                                })
                                .catch(error => {
                                    console.error('Erro ao carregar o modal:', error);
                                    modalBody.innerHTML = `
                                <div class="modal-header">
                                    <h5 class="modal-title">Erro</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-danger">Ocorreu um erro ao carregar os detalhes do dedicated.</div>
                                </div>
                            `;
                                });
                            break;
                        default:
                            break;
                    }
                }
                document.getElementById("dedicated-modal-overlay").style.display = "flex";
                document.getElementById("dedicated-modal").style.display = "flex";
                document.body.style.overflow = 'hidden';
            });
        });
    }


    document.querySelector(".close-modal").addEventListener("click", () => {
        document.getElementById("dedicated-modal-overlay").style.display = "none";
        document.getElementById("dedicated-modal").style.display = "none";
        document.body.style.overflow = 'auto';
    });
</script>