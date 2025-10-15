<!-- Page Heading -->
<h1 class="h3 mb-4 text-gray-800">Configuración del Sistema</h1>

<div class="row">
    <!-- Configuración General -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Configuración General</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="nombre_negocio">Nombre del Negocio</label>
                        <input type="text" class="form-control" id="nombre_negocio" value="India Bonita">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono/WhatsApp</label>
                        <input type="text" class="form-control" id="telefono" value="7785131654">
                    </div>
                    <div class="form-group">
                        <label for="color_primario">Color Primario</label>
                        <input type="color" class="form-control" id="color_primario" value="#F50087">
                    </div>
                    <div class="form-group">
                        <label for="moneda">Moneda</label>
                        <select class="form-control" id="moneda">
                            <option value="MXN">Peso Mexicano (MXN)</option>
                            <option value="USD">Dólar Americano (USD)</option>
                            <option value="EUR">Euro (EUR)</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Configuración de Visualización -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Visualización del Menú</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="mostrar_precios" checked>
                            <label class="custom-control-label" for="mostrar_precios">Mostrar Precios</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="mostrar_imagenes" checked>
                            <label class="custom-control-label" for="mostrar_imagenes">Mostrar Imágenes</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="idioma">Idioma</label>
                        <select class="form-control" id="idioma">
                            <option value="es">Español</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notas">Notas Adicionales</label>
                        <textarea class="form-control" id="notas" rows="3" placeholder="Información adicional para el menú..."></textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <button type="button" class="btn btn-secondary">Cancelar</button>
    </div>
</div>
