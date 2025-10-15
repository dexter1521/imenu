        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo site_url('app/dashboard'); ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="sidebar-brand-text mx-3">iMenu</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'dashboard') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('app/dashboard'); ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Gestión
            </div>

            <!-- Nav Item - Categorías -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'categorias') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('app/categorias'); ?>">
                    <i class="fas fa-fw fa-list"></i>
                    <span>Categorías</span>
                </a>
            </li>

            <!-- Nav Item - Productos -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'productos') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('app/productos'); ?>">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Productos</span>
                </a>
            </li>

            <!-- Nav Item - Pedidos -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'pedidos') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('app/pedidos'); ?>">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Pedidos</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Configuración
            </div>

            <!-- Nav Item - Ajustes -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'ajustes') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('app/ajustes'); ?>">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Ajustes</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->
