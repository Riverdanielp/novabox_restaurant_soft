<style>
    .number_buttons {
        width: 80px;
        height: 80px;
    }
</style>
<section class="main-content-wrapper">
    <?php
    if ($this->session->flashdata('exception')) {
        echo '<section class="alert-wrapper">
        <div class="alert alert-success alert-dismissible fade show"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body">
        <p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>

    <section class="content-header">
        <div class="row">
            <div class="col-sm-12">
                <h2 class="top-left-header">Números</h2>
            </div>
        </div>
    </section>

    <div class="box-wrapper">
        <div class="table-box">
            <div class="row">
                <div class="col-sm-12">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNumbersModal">
                        Agregar-Editar Números
                    </button>
                    <hr>
                </div>
            </div>
            <div class="table-responsive">
                <div class="d-flex flex-wrap gap-2">
                    <?php if (empty($numbers)): ?>
                        <h5>Aun no existen números</h5>
                    <?php else : ?>
                        <?php foreach ($numbers as $number): ?>
                            <button class="btn btn-lg number_buttons
                                <?php echo ($number->sale_id) ? 'btn-danger' : 'btn-success'; ?>" 
                                disabled>
                                <?php echo escape_output($number->name); ?>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal para agregar números -->
<div class="modal fade" id="addNumbersModal" tabindex="-1" aria-labelledby="addNumbersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Números</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url('numbers/addNumbers'); ?>" method="POST">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="1000" 
                        value="<?php echo count($numbers); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
