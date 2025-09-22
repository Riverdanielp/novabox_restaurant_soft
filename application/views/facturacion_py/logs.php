<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header">Logs de Auditoría para Factura #<?php echo $factura_id; ?></h3>
    </section>

    <div class="box-wrapper">
        <div class="table-box">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Acción</th>
                            <th>Respuesta JSON</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($log->fecha_modificacion)); ?></td>
                            <td>
                                <span class="badge <?php echo ($log->tipo_accion == 'API_EXITO') ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $log->tipo_accion; ?>
                                </span>
                            </td>
                            <td>
                                <pre style="white-space: pre-wrap; word-wrap: break-word;"><?php echo json_encode(json_decode($log->json_backup), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="box-footer">
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>Facturacion_py/listado"><i data-feather="corner-up-left"></i> Volver al Listado</a>
            </div>
        </div>
    </div>
</section>