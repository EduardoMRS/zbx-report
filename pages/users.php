<?php
if (!$userData || $userData["access_level"] != "admin") {
    echo "<div class='container'><h1>Ooops! Pagina não encontrada!</h1></div>";
    $hasInclude = "error";
    exit;
}

if (!isset($_SESSION['idUser'])) {
    exit;
}
?>

<script src="<?= $site_url ?>assets/js/jquery.mask.min.js"></script>
<style>
    .modal-content {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background-color: var(--color-nav-background);
        color: var(--color-nav-text);
        border-bottom: 1px solid var(--color-card-shadow);
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        border-top: 1px solid var(--color-card-shadow);
        padding: 15px;
    }

    .btn-close {
        filter: invert(1);
    }

    .form-control {
        margin-bottom: 15px;
    }

    .form-label {
        font-weight: 500;
        color: var(--color-text);
    }
</style>

<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Lista de Usuários</h1>
    </div>
    <div class="search-bar">
        <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar por nome, email, telefone, cpf...">
        <button id="search-btn" class="btn btn-primary">Buscar</button>
        <button class="btn btn-secondary" data-bs-toggle='modal' data-bs-target='#editUserModal'>+</button>
    </div>
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="firstName">Nome
                            <span></span>
                        </th>
                        <th class="sortable" data-sort="email">E-mail
                            <span></span>
                        </th>
                        <th class="sortable" data-sort="sex">Sexo
                            <span></span>
                        </th>
                        <th class="sortable" data-sort="access_level">Permissão
                            <span></span>
                        </th>
                        <th class="sortable" data-sort="last_access">Ultimo Acesso
                            <span></span>
                        </th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Edição de Usuário -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="userIdEdit" name="userId">
                <div class="mb-3">
                    <label for="firstName" class="form-label">Nome *</label>
                    <input type="text" class="form-control" id="firstName" name="firstName">
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Sobrenome *</label>
                    <input type="text" class="form-control" id="lastName" name="lastName">
                </div>
                <div class="mb-3">
                    <label for="birth" class="form-label">Data de Nascimento *</label>
                    <input type="date" class="form-control" id="birth" name="birth">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail *</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="mb-3">
                    <label for="document" class="form-label">CPF</label>
                    <input type="text" inputmode="numeric" class="form-control" id="document" name="document">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="text" inputmode="numeric" class="form-control" id="phone" name="phone">
                </div>
                <div class="mb-3">
                    <label for="sex" class="form-label">Sexo</label>
                    <select class="form-select" id="sex" name="sex">
                        <option value="male">Masculino</option>
                        <option value="female">Feminino</option>
                        <option value="other">Outro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="accessLevel" class="form-label">Permissão *</label>
                    <select class="form-select" id="accessLevel" name="accessLevel" required>
                        <option value="view">View</option>
                        <option value="basic">Basic</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-updateUser" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Enviar Código de Recuperação -->
<div class="modal fade" id="sendRecoveryCodeModal" tabindex="-1" aria-labelledby="sendRecoveryCodeModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendRecoveryCodeModalLabel">Enviar Código de Recuperação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="userIdRecoveryCode" name="userId">
                <p>Deseja enviar um código de recuperação para o e-mail do usuário?</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-sendLink" class="btn btn-warning">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Remover Usuário -->
<div class="modal fade" id="removeUserModal" tabindex="-1" aria-labelledby="removeUserModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeUserModalLabel">Remover Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="userIdRemove" name="userId">
                <p>Deseja realmente remover este usuário?</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-removeUser" class="btn btn-danger">Remover</button>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById("searchInput");
    const searchButton = document.getElementById("search-btn");
    const userList = document.getElementById('user-list');

    function loadInteraction() {
        const editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id') ?? 0;
            var modalInput = editUserModal.querySelector('#userIdEdit');
            modalInput.value = userId;

            if (userId != 0) {
                fetch(`<?= $site_url ?>api/?op=user-get&id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('editUserModalLabel').innerHTML = 'Editar Usuário';
                        document.getElementById('firstName').value = data.data.firstName ?? "";
                        document.getElementById('lastName').value = data.data.lastName ?? "";
                        document.getElementById('email').value = data.data.email ?? "";
                        document.getElementById('phone').value = data.data.phone ?? "";
                        document.getElementById('document').value = data.data.document ?? "";
                        document.getElementById('birth').value = data.data.birth ?? "";
                        document.getElementById('sex').value = data.data.sex ?? "";
                        document.getElementById('accessLevel').value = data.data.access_level ?? "";
                    })
                    .catch(error => console.error('Erro ao buscar dados do usuário:', error));
            } else {
                document.getElementById('editUserModalLabel').innerHTML = 'Novo Usuário';
                document.getElementById('firstName').value = "";
                document.getElementById('lastName').value = "";
                document.getElementById('email').value = "";
                document.getElementById('phone').value = "";
                document.getElementById('document').value = "";
                document.getElementById('birth').value = "";
                document.getElementById('sex').value = "";
                document.getElementById('accessLevel').value = "basic";
            }

        });

        // Modal de Enviar Código de Recuperação
        const sendRecoveryCodeModal = document.getElementById('sendRecoveryCodeModal');
        sendRecoveryCodeModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var modalInput = sendRecoveryCodeModal.querySelector('#userIdRecoveryCode');
            modalInput.value = userId;
        });

        // Modal de Remover Usuário
        const removeUserModal = document.getElementById('removeUserModal');
        removeUserModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var modalInput = removeUserModal.querySelector('#userIdRemove');
            modalInput.value = userId;
        });

        document.getElementById("btn-sendLink").addEventListener("click", () => {
            fetch(`<?= $site_url ?>api/?op=user-send-recovery`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        userId: document.getElementById("userIdRecoveryCode").value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Link de recuperação enviado com sucesso!');
                    } else {
                        alert('Erro ao enviar o link de recuperação.');
                    }
                })
                .catch(error => console.error('Erro:', error));
        })

        document.getElementById("btn-removeUser").addEventListener("click", () => {
            fetch(`<?= $site_url ?>api/?op=user-delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: document.getElementById("userIdRemove").value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Usuario deletado com sucesso!');
                        window.location.reload();
                    } else {
                        alert('Erro ao deletar usuario.');
                    }
                })
                .catch(error => console.error('Erro:', error));
        })

        // Atualizar Nível de Acesso
        document.querySelectorAll(".input-change-level").forEach(element => {
            element.addEventListener("change", () => {
                const userId = element.dataset.userid;
                const accessLevel = element.value;

                fetch(`<?= $site_url ?>api/?op=user-update-access`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            userId: userId,
                            accessLevel: accessLevel
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Nível de acesso atualizado com sucesso!');
                            window.location.reload();
                        } else {
                            alert('Erro ao atualizar o nível de acesso.');
                        }
                    })
                    .catch(error => console.error('Erro:', error));
            });
        });

        document.getElementById("btn-updateUser").addEventListener("click", () => {
            idUser = document.getElementById('userIdEdit').value ?? 0;
            firstName = document.getElementById('firstName').value ?? "";
            lastName = document.getElementById('lastName').value ?? "";
            email = document.getElementById('email').value ?? "";
            phone = document.getElementById('phone').value ?? "";
            documentUser = document.getElementById('document').value ?? "";
            birth = document.getElementById('birth').value ?? "";
            sex = document.getElementById('sex').value ?? "";
            accessLevel = document.getElementById('accessLevel').value ?? "";

            if (!idUser) {
                alert("Usuario invalido!");
                return;
            }
            if (!firstName || !lastName) {
                alert("Nome do usuario esta incompleto!");
                return;
            }
            if (!email) {
                alert("E-Mail não informado!");
                return;
            }
            if (!accessLevel) {
                alert("Nivel de permissão não informado!");
                return;
            }
            let op = idUser == 0 ? "new" : "update";
            fetch(`<?= $site_url ?>api/?op=user-${op}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        idUser: idUser,
                        firstName: firstName,
                        lastName: lastName,
                        email: email,
                        phone: phone ?? "",
                        document: documentUser ?? "",
                        birth: birth ?? "",
                        sex: sex ?? "",
                        access_level: accessLevel
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Usuario atualizado com sucesso!');
                        window.location.reload();
                    } else {
                        alert('Erro ao atualizar o usuario.');
                    }
                })
                .catch(error => console.error('Erro:', error));
        });
    }

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
        const searchInput = document.getElementById("searchInput");
        const searchValue = searchInput.value.trim();
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');
        const currentOrder = urlParams.get('order');


        let newOrder = 'asc';
        if (currentSort === column && currentOrder === 'asc') {
            newOrder = 'desc';
        }

        window.history.pushState({}, '', `?page=users&sort=${column}&order=${newOrder}`);

        searchUser();
    }

    let isLoading = false;
    let currentPage = 0;

    window.addEventListener('scroll', function() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500 && !isLoading) {
            loadMoreUsers();
        }
    });

    document.addEventListener("DOMContentLoaded", () => {
        searchUser();
    })

    function searchUser() {
        search = (document.getElementById("searchInput").value).trim();
        currentPage = 0;
        userList.innerHTML = "<tr><td colspan='7' class='text-center'>Carregando...</td></tr>";
        loadMoreUsers();
        if (search.length > 0) {
            searchButton.innerHTML = "Limpar";
            searchButton.classList.add("btn-danger");
        } else {
            searchButton.innerHTML = "Search";
            searchButton.classList.remove("btn-danger");
        }
    }

    function loadMoreUsers() {
        isLoading = true;
        currentPage++;
        let search = (searchInput.value).length > 0 ? `&search=${searchInput.value}` : "";

        const urlParams = new URLSearchParams(window.location.search);
        const sortColumn = urlParams.get('sort') || 'firstName';
        const sortOrder = urlParams.get('order') || 'asc';

        fetch(`<?= $site_url ?>/api/?op=user-search&search=all&sort=${sortColumn}&order=${sortOrder}`)
            .then(response =>
                response.json())
            .then(data => {
                if (currentPage = 0) {
                    userList.innerHTML = "";
                }
                userList.innerHTML = "";
                if (data.content && data.content.length > 0) {
                    data.content.forEach(item => {
                        userList.appendChild(constructLineUserList(item));
                    });
                    window.history.pushState({}, '', `?page=users&sort=${sortColumn}&order=${sortOrder}`);
                } else {
                    userList.innerHTML = "<tr><td colspan='7' class='text-center'>Nenhum usuario encontrado.</td></tr>";
                }
                loadInteraction();
            })
            .catch(error => {
                console.log(error);
                userList.innerHTML = "<tr><td colspan='7' class='text-center'>Erro ao carregar usuarios.</td></tr>";
            })
    }


    function constructLineUserList(item) {
        const tr = document.createElement("tr");
        tr.innerHTML = `
                                    <td>${item.firstName.trim()} ${item.lastName.trim()}</td>
                                    <td data-label="E-Mail">${item.email}</td>
                                    <td data-label="Sexo">${item.sex? item.sex === "male" ? "Masculino" : item.sex ==="female"? "Feminino": "Outros": "Não informado"}</td>
                                    <td data-label="Permissão">
                                        <select class='form-select input-change-level' data-userid='${item.id}'>
                                            <option value='view'${item.access_level == "view" ? " selected" : ""}>View</option>
                                            <option value='basic'${item.access_level == "basic" ? " selected" : ""}>Basic</option>
                                            <option value='admin'${item.access_level == "admin" ? " selected" : ""}>Admin</option>
                                        </select>
                                    </td>
                                    <td data-label="Ultimo Acesso">${formatDate("Y-m-d H:i", item.last_access)}</td>
                                    <td>
                                        <span class='actions'>
                                            <button class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#editUserModal' data-user-id='${item.id}'><i class='bi bi-pencil-square'></i></button>
                                            <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#sendRecoveryCodeModal' data-user-id='${item.id}'><i class='bi bi-key-fill'></i></button>
                                            <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#removeUserModal' data-user-id='${item.id}'><i class='bi bi-trash-fill'></i></button>
                                        </span>
                                    </td>
                                    `;
        return tr;
    }

    function deleteUser(id) {
        if (confirm("Tem certeza que deseja apagar os dados deste usuario?")) {
            const formData = new FormData();
            formData.append("id", id);
            fetch(`<?= $site_url ?>/api/?op=user-delete`, {
                    method: 'DELETE',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert("Erro ao salvar alterações: " + data.message);
                    }
                })
                .catch(error => {
                    alert("Erro ao salvar alterações. Tente novamente.");
                });
        }
    }
    maskCpf();
    maskPhone();
</script>