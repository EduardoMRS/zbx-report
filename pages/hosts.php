<?php
$zabbix = new ZabbixAPI();
?>
<style>
    .badge-ip {
        background-color: #6f42c1;
        color: white;
    }

    .badge-dedicated-yes {
        background-color: #fd4b03;
    }

    .badge-status.active {
        background-color: rgb(48, 202, 55);
        color: #000000;
    }

    .badge-status {
        background-color: rgb(209, 209, 209);
        color: #000000;
    }

    .host-name {
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

    #host-inputs {
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
        <h1 class="h3 mb-0">Hosts</h1>
    </div>
    <div class="search-bar">
        <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar por nome, ip, descrição, tag, ou macro">
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
                        <th class="sortable" data-sort="host">IP
                            <span class="order"></span>
                        </th>
                        <th class="text-center sortable" data-sort="status">Status
                            <span class="order"></span>
                        </th>
                        <th>Dedicado?
                            <span class="order"></span>
                        </th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody id="host-list">
                    <td colspan='5'><span class="loader"></span></td>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="custom-modal-overlay" id="host-modal-overlay"></div>
<div class="custom-modal" id="host-modal">
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
    const hostList = document.getElementById('host-list');

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

        window.history.pushState({}, '', `?page=hosts&sort=${column}&order=${newOrder}`);

        searchHost();
    }

    document.getElementById("searchInput").addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            searchHost();
        }
    });


    let searchButton = document.getElementById("search-btn");
    searchButton.addEventListener("click", () => {
        if (searchButton.innerHTML == "Limpar") {
            document.getElementById("searchInput").value = "";
        }
        searchHost();
    })

    function searchHost() {
        search = (document.getElementById("searchInput").value).trim();
        currentPage = 0;
        hostList.innerHTML = "<tr><td colspan='5' class='text-center'>Carregando...</td></tr>";
        loadMoreHosts();
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
            loadMoreHosts();
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        hostList.innerHTML = "";
        searchHost();
    })

    function loadMoreHosts() {
        isLoading = true;
        currentPage++;
        let search = (searchInput.value).length > 0 ? `&search=${searchInput.value}` : "";
        const urlParams = new URLSearchParams(window.location.search);
        const sortColumn = urlParams.get('sort') || 'name';
        const sortOrder = urlParams.get('order') || 'asc';

        fetch(`<?= $site_url ?>api/?op=host-search${search}&page=${currentPage}&sort=${sortColumn}&order=${sortOrder}`)
            .then(response => response.json())
            .then(data => {
                if (currentPage = 1) {
                    hostList.innerHTML = "";
                }
                if (data.hosts.length > 0) {
                    data.hosts.forEach(host => {
                        isActive = host.status === '0';
                        status = isActive ? '<span class="badge badge-status active">Ativo</span>' : '<span class="badge badge-status">Inativo</span>';
                        dedicated = host.dedicated == "true" ? '<span class="badge badge-dedicated-yes">✓</span>' : '<span class="badge badge-dedicated">✗</span>';

                        hostList.innerHTML += `
                        <tr>
                            <td>${host.name}</td>
                            <td data-label="IP"><span class="badge badge-ip">${host.ip}</span></td>
                            <td data-label="Status" class="text-center">${status}</td>
                            <td data-label="Dedicado?" class="text-center">${dedicated}</td>
                            <td>
                                <div class="action-buttons actions">
                                    <button class="btn btn-sm btn-outline-primary me-1 btn-open-modal" 
                                            data-modal="dynamic" data-modaltype="host-details" 
                                            data-hostid="${host.id}" title="Dados">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary me-1 btn-open-modal" 
                                            data-modal="dynamic" data-modaltype="dedicated-details" 
                                            data-hostid="${host.id}" title="Dados">
                                        <i class="bi bi-calendar-week"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    });
                }
                isLoading = false;
                loadInteraction();
            });
    }

    loadInteraction();

    function loadInteraction() {
        document.querySelectorAll(".btn-open-modal[data-modal]").forEach(item => {
            item.addEventListener("click", () => {
                let type = item.dataset.modaltype;
                let modalBody = document.getElementById("host-modal").querySelector(".modal-content");
                modalBody.innerHTML = "";

                if (type) {
                    switch (type) {
                        case "host-details":
                            fetch(`<?= $site_url ?>?modal=host-details&id=${item.dataset.hostid}`)
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
                                    <div class="alert alert-danger">Ocorreu um erro ao carregar os detalhes do host.</div>
                                </div>
                            `;
                                });
                            break;
                        case "dedicated-details":
                            fetch(`<?= $site_url ?>?modal=dedicated-details&id=${item.dataset.hostid}`)
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
                                    <div class="alert alert-danger">Ocorreu um erro ao carregar os detalhes do host.</div>
                                </div>
                            `;
                                });
                            break;

                        default:
                            break;
                    }
                }
                document.getElementById("host-modal-overlay").style.display = "flex";
                document.getElementById("host-modal").style.display = "flex";
                document.body.style.overflow = 'hidden';
            });
        });
    }


    document.querySelector(".close-modal").addEventListener("click", () => {
        document.getElementById("host-modal-overlay").style.display = "none";
        document.getElementById("host-modal").style.display = "none";
        document.body.style.overflow = 'auto';
    });
</script>