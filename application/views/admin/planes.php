<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Planes</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Plan
    </a>
</div>

<!-- Planes Cards -->
<div class="row">
    <!-- Plan Free -->
    <div class="col-lg-4 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">Plan Free</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h2 class="font-weight-bold">$0.00</h2>
                    <p class="text-muted">por mes</p>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> 5 Categorías</li>
                    <li><i class="fas fa-check text-success"></i> 50 Productos</li>
                    <li><i class="fas fa-times text-danger"></i> Con publicidad</li>
                    <li><i class="fas fa-check text-success"></i> Soporte básico</li>
                </ul>
                <div class="text-center">
                    <a href="#" class="btn btn-outline-info">Editar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Pro -->
    <div class="col-lg-4 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-header bg-success text-white">
                <h6 class="m-0 font-weight-bold">Plan Pro</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h2 class="font-weight-bold">$199.00</h2>
                    <p class="text-muted">por mes</p>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> 20 Categorías</li>
                    <li><i class="fas fa-check text-success"></i> 300 Productos</li>
                    <li><i class="fas fa-check text-success"></i> Sin publicidad</li>
                    <li><i class="fas fa-check text-success"></i> Soporte prioritario</li>
                    <li><i class="fas fa-check text-success"></i> Análisis avanzados</li>
                </ul>
                <div class="text-center">
                    <a href="#" class="btn btn-outline-success">Editar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Nuevo Plan -->
    <div class="col-lg-4 mb-4">
        <div class="card border-left-primary shadow h-100 text-center">
            <div class="card-body d-flex align-items-center justify-content-center">
                <div>
                    <i class="fas fa-plus fa-3x text-primary mb-3"></i>
                    <h5>Crear Nuevo Plan</h5>
                    <a href="#" class="btn btn-primary">Agregar Plan</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Planes -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Todos los Planes</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio Mensual</th>
                        <th>Límite Categorías</th>
                        <th>Límite Productos</th>
                        <th>Publicidad</th>
                        <th>Tenants Activos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><span class="badge badge-info">Free</span></td>
                        <td>$0.00</td>
                        <td>5</td>
                        <td>50</td>
                        <td><span class="text-warning">Sí</span></td>
                        <td>0</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">Editar</a>
                            <a href="#" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><span class="badge badge-success">Pro</span></td>
                        <td>$199.00</td>
                        <td>20</td>
                        <td>300</td>
                        <td><span class="text-success">No</span></td>
                        <td>1</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">Editar</a>
                            <a href="#" class="btn btn-sm btn-danger">Eliminar</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
