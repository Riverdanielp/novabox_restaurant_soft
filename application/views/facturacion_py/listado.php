<section class="main-content-wrapper">
    <section class="content-header" style="height: 80px;">
        <h3 class="top-left-header">Listado de Facturas Electrónicas</h3>
        <a href="<?php echo base_url('Facturacion_py/formulario'); ?>" class="btn btn-info float-end">
            <i data-feather="plus"></i> Crear Factura Manual
        </a>
        <a href="<?php echo base_url('Facturacion_py/sync_estados_pendientes'); ?>" class="btn btn-primary float-end">
            <i data-feather="refresh-cw"></i> Sincronizar Estados Pendientes
        </a>
    </section>

    <!-- Filtros -->
    <div class="box-wrapper">
        <div class="table-box">
                    
            <!-- Manejo de mensajes de error -->
            <?php if ($this->session->flashdata('error_custom')): ?>
                <div class="alert alert-danger  fade show" role="alert">
                    <i data-feather="alert-circle"></i>
                    <strong>Error:</strong> <?php echo escape_output($this->session->flashdata('error_custom')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Manejo de mensajes de éxito -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success  fade show" role="alert">
                    <i data-feather="check-circle"></i>
                    <strong>Éxito:</strong> <?php echo escape_output($this->session->flashdata('success')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Manejo de mensajes de información -->
            <?php if ($this->session->flashdata('info')): ?>
                <div class="alert alert-info  fade show" role="alert">
                    <i data-feather="info"></i>
                    <strong>Información:</strong> <?php echo escape_output($this->session->flashdata('info')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Manejo de mensajes de advertencia -->
            <?php if ($this->session->flashdata('warning')): ?>
                <div class="alert alert-warning  fade show" role="alert">
                    <i data-feather="alert-triangle"></i>
                    <strong>Advertencia:</strong> <?php echo escape_output($this->session->flashdata('warning')); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <h4><i data-feather="filter"></i> Filtros</h4>
            <?php echo form_open(base_url('Facturacion_py/listado'), ['method' => 'GET']); ?>
            <div class="row">
                <div class="col-md-2"><input type="date" name="fecha_inicio" class="form-control" value="<?php echo $filters['fecha_inicio']; ?>"></div>
                <div class="col-md-2"><input type="date" name="fecha_fin" class="form-control" value="<?php echo $filters['fecha_fin']; ?>"></div>
                <div class="col-md-2">
                    <select name="sucursal_id" class="form-control select2">
                        <option value="">Todas las Sucursales</option>
                        <?php foreach($sifen_sucursales as $suc): ?>
                        <option value="<?php echo $suc->id; ?>" <?php echo ($filters['sucursal_id'] == $suc->id) ? 'selected' : ''; ?>><?php echo $suc->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="usuario_id" class="form-control select2">
                        <option value="">Todos los Usuarios</option>
                         <?php foreach($usuarios as $user): ?>
                        <option value="<?php echo $user->id; ?>" <?php echo ($filters['usuario_id'] == $user->id) ? 'selected' : ''; ?>><?php echo $user->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="<?php echo base_url('Facturacion_py/listado'); ?>" class="btn btn-secondary">Limpiar</a>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="box-wrapper">
        <div class="table-box">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th># Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>CDC</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($facturas as $factura): ?>
                        <tr>
                            <td><?php echo $factura->numero_formateado; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($factura->fecha)); ?></td>
                            <td><?php echo $factura->cliente_nombre; ?></td>
                            <td><?php echo $factura->usuario_nombre; ?></td>
                            <td>
                                <span class="badge 
                                    <?php if($factura->estado == 2) echo 'bg-success'; 
                                          elseif($factura->estado == 4) echo 'bg-danger'; 
                                          else echo 'bg-warning'; ?>">
                                    <?php echo $factura->estado_descripcion; ?>
                                </span>
                            </td>
                            <td><?php echo $factura->cdc; ?></td>
                            <td class="text-center">
                                <a href="<?php echo base_url('Facturacion_py/formulario/'.$factura->id); ?>" class="btn btn-sm btn-info"><i data-feather="eye"></i> Ver</a>
                                <a href="<?php echo base_url('Facturacion_py/logs/'.$factura->id); ?>" class="btn btn-sm btn-secondary"><i data-feather="terminal"></i> Logs</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                         <?php echo $links; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>