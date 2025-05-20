<style>
    /* Menu Lateral */
    .side-menu {
        position: fixed;
        top: 0;
        right: -300px;
        width: 300px;
        height: 100vh;
        background-color: #1a1a1a;
        color: #fff;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        transition: right 0.3s ease-in-out;
        display: flex;
        flex-direction: column;
    }

    .side-menu.open {
        right: 0;
    }

    .menu-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #333;
    }

    .menu-header h3 {
        margin: 0;
        font-size: 1.2rem;
    }

    .close-menu {
        background: none;
        border: none;
        color: #fff;
        font-size: 1.5rem;
        cursor: pointer;
    }

    .menu-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-nav li a {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: #fff;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .menu-nav li a:hover {
        background-color: #333;
    }

    .menu-nav li a i {
        margin-right: 15px;
        font-size: 1.2rem;
    }

    /* Overlay */
    .menu-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .menu-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Responsivo - Menu ocupa toda a tela no mobile */
    @media (max-width: 768px) {
        .side-menu {
            width: 100%;
            right: -300%;
        }
    }
</style>

<!-- Overlay (fundo escuro) -->
<div id="menuOverlay" class="menu-overlay"></div>


<!-- Menu Lateral -->
<aside id="sideMenu" class="side-menu">
    <div class="menu-header">
        <button class="close-menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="menu-nav">
        <ul>
            <li>
                <a href="<?= $site_url ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Inicio</span>
                </a>
                <a href="<?= $site_url ?>?page=hosts">
                    <i class="bi bi-search"></i>
                    <span>Lista de Hosts</span>
                </a>
                <a href="<?= $site_url ?>?page=dedicated">
                    <i class="bi bi-search"></i>
                    <span>Clientes Dedicados</span>
                </a>
                <a href="<?= $site_url ?>?page=mail-history">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Envios Recentes</span>
                </a>
                <?php
                if (!$userData || $userData["access_level"] == "admin") {
                ?>
                    <a href="<?= $site_url ?>?page=users">
                        <i class="bi bi-people"></i>
                        <span>Usuarios</span>
                    </a>
                    <a href="<?= $site_url ?>?page=config">
                        <i class="bi bi-gear"></i>
                        <span>Configurações</span>
                    </a>
                <?php
                }
                if (isset($_SESSION["idUser"])) {
                    echo '<a href="' . $site_url . '?logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sair</span>
                        </a>';
                } else {
                    echo '<a href="' . $site_url . '/login/">
                            <i class="bi bi-door-open-fill"></i>
                            <span>Entrar</span>
                        </a>';
                }
                ?>
            </li>
        </ul>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sideMenu = document.getElementById('sideMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const closeMenuBtn = document.querySelector('.close-menu');

        // Abrir menu
        menuToggle.addEventListener('click', function() {
            sideMenu.classList.add('open');
            menuOverlay.classList.add('active');
        });

        // Fechar menu (botão X)
        closeMenuBtn.addEventListener('click', function() {
            sideMenu.classList.remove('open');
            menuOverlay.classList.remove('active');
        });

        // Fechar menu (clicar no overlay)
        menuOverlay.addEventListener('click', function() {
            sideMenu.classList.remove('open');
            menuOverlay.classList.remove('active');
        });
    });
</script>