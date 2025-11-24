<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Ventas del Ingrediente</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/datatable_custom/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/datatable_custom/buttons.dataTables.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3>Histórico de Ventas del Ingrediente: <?php echo $ingredient_name; ?></h3>
                <div class="table-responsive">
                    <table id="salesTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha de Venta</th>
                                <th>Acción</th>
                                <th>Producto Vendido</th>
                                <th>Cantidad Consumida</th>
                                <th>Precio Unitario</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales_history)): ?>
                                <?php foreach ($sales_history as $sale): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($sale->date_time)); ?></td>
                                        <td><a href="<?php echo base_url(); ?>Sale/view/<?php echo $sale->sale_id; ?>" class="btn btn-primary btn-sm" target="_blank">Ver Venta</a></td>
                                        <td><?php echo $sale->food_menu_name; ?></td>
                                        <td><?php echo number_format($sale->consumption, 2); ?> <?php echo $sale->unit_name; ?></td>
                                        <td><?php echo number_format($sale->menu_unit_price, 2); ?></td>
                                        <td><?php echo number_format($sale->total_sale_price, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No se encontraron ventas para este ingrediente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Volver</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url(); ?>assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/datatable_custom/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#salesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>
</html>