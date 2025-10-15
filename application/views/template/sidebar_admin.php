        <!-- Sidebar Admin -->
        <ul class="navbar-nav bg-gradient-danger sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo site_url('admin/tenants_view'); ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Admin Panel</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'tenants_view') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('admin/tenants_view'); ?>">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Tenants</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Configuración
            </div>

            <!-- Nav Item - Planes -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'planes_view') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('admin/planes_view'); ?>">
                    <i class="fas fa-fw fa-layer-group"></i>
                    <span>Planes</span>
                </a>
            </li>

            <!-- Nav Item - Pagos -->
            <li class="nav-item <?php echo ($this->uri->segment(2) == 'pagos_view') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo site_url('admin/pagos_view'); ?>">
                    <i class="fas fa-fw fa-dollar-sign"></i>
                    <span>Pagos</span>
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
