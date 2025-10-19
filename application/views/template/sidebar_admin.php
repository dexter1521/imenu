        <!-- Sidebar Admin -->
        <ul class="navbar-nav bg-gradient-danger sidebar sidebar-dark accordion" id="accordionSidebar">

        	<!-- Sidebar - Brand -->
        	<a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo site_url('admin/dashboard'); ?>">
        		<div class="sidebar-brand-icon rotate-n-15">
        			<i class="fas fa-crown"></i>
        		</div>
        		<div class="sidebar-brand-text mx-3">Admin Panel</div>
        	</a>

        	<!-- Divider -->
        	<hr class="sidebar-divider my-0">

        	<!-- Nav Item - Dashboard -->
        	<li class="nav-item <?php echo ($this->uri->segment(2) == 'dashboard' || $this->uri->segment(2) == '') ? 'active' : ''; ?>">
        		<a class="nav-link" href="<?php echo site_url('admin/dashboard'); ?>">
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

        	<!-- Nav Item - Tenants -->
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
        		Financiero
        	</div>

        	<!-- Nav Item - Planes -->
        	<li class="nav-item <?php echo ($this->uri->segment(2) == 'planes_view') ? 'active' : ''; ?>">
        		<a class="nav-link" href="<?php echo site_url('admin/planes_view'); ?>">
        			<i class="fas fa-fw fa-layer-group"></i>
        			<span>Planes</span>
        		</a>
        	</li>

        	<!-- Nav Item - Suscripciones -->
        	<li class="nav-item <?php echo ($this->uri->segment(2) == 'suscripciones_view') ? 'active' : ''; ?>">
        		<a class="nav-link" href="<?php echo site_url('admin/suscripciones_view'); ?>">
        			<i class="fas fa-fw fa-calendar-check"></i>
        			<span>Suscripciones</span>
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
