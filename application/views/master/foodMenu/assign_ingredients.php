<link rel="stylesheet" href="<?php echo base_url();?>frequent_changing/css/custom_check_box.css">
<style>
.menu-list-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 18px;
    padding: 14px 12px 8px 12px;
    background: #fafbfc;
    min-height: 70px;
}
.menu-title {
    font-size: 0.93rem;
    font-weight: 600;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}
.menu-checkbox-group, .category-checkbox-group {
    display: flex;
    gap: 15px;
    align-items: center;
}
.menu-checkbox-group label.container,
.category-checkbox-group label.container {
    font-size: 0.85em;
    margin-bottom: 0;
    padding-left: 24px;
    min-height: 22px;
}
.menu-checkbox-group .checkmark,
.category-checkbox-group .checkmark {
    height: 16px;
    width: 16px;
    top: 2px;
}
.category-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top:30px;
    margin-bottom: 10px;
}
@media (max-width: 767px) {
    .col-md-6 { width:100%; }
    .category-header { flex-direction: column; align-items: flex-start; }
}
</style>
<section class="main-content-wrapper">
    <section class="content-header">
        <h3 class="top-left-header" style="font-size:1.1em;">
            Asignar Tipo de Ingrediente a Menú de Comidas
        </h3>
    </section>
    <div class="box-wrapper">
        <div class="table-box">
            <?php echo form_open(base_url() . 'FoodMenu/assign_submit', ['id' => 'food_menu_form']); ?>
            <div class="box-body">
                <?php if (empty($grouped_menus)): ?>
                    <div class="alert alert-info">No hay menús sin ingredientes.</div>
                <?php else: ?>
                    <?php foreach ($grouped_menus as $category => $menus): ?>
                        <?php $first_menu = reset($menus); $category_id = $first_menu->category_id ?: '0'; ?>
                        <div class="category-header">
                            <h4 style="font-size:2em;margin:0;"><?php echo htmlspecialchars($category); ?></h4>
                            <div class="category-checkbox-group">
                                <label class="container" style="font-size:0.85em;">Sin modificar
                                    <input type="checkbox"
                                        class="cat-option-checkbox"
                                        name="cat_option[<?php echo $category_id; ?>]"
                                        value="no_change"
                                        data-category="<?php echo $category_id; ?>"
                                    >
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container" style="font-size:0.85em;">Ingrediente
                                    <input type="checkbox"
                                        class="cat-option-checkbox"
                                        name="cat_option[<?php echo $category_id; ?>]"
                                        value="ingredient"
                                        data-category="<?php echo $category_id; ?>"
                                    >
                                    <span class="checkmark"></span>
                                </label>
                                <label class="container" style="font-size:0.85em;">Pre-producción
                                    <input type="checkbox"
                                        class="cat-option-checkbox"
                                        name="cat_option[<?php echo $category_id; ?>]"
                                        value="pre_production"
                                        data-category="<?php echo $category_id; ?>"
                                    >
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                        <?php foreach ($menus as $menu): ?>
                            <div class="col-md-6">
                                <div class="menu-list-item">
                                    <div class="menu-title"><?php echo htmlspecialchars($menu->name); ?></div>
                                    <div class="menu-checkbox-group">
                                        <label class="container" style="font-size:0.85em;">Sin modificar
                                            <input type="checkbox" 
                                                name="menu_option[<?php echo $menu->id; ?>]"
                                                value="no_change"
                                                class="option-checkbox"
                                                data-menu="<?php echo $menu->id; ?>"
                                                data-category="<?php echo $category_id; ?>"
                                            >
                                            <span class="checkmark"></span>
                                        </label>
                                        <label class="container" style="font-size:0.85em;">Ingrediente
                                            <input type="checkbox" 
                                                name="menu_option[<?php echo $menu->id; ?>]"
                                                value="ingredient"
                                                class="option-checkbox"
                                                data-menu="<?php echo $menu->id; ?>"
                                                data-category="<?php echo $category_id; ?>"
                                            >
                                            <span class="checkmark"></span>
                                        </label>
                                        <label class="container" style="font-size:0.85em;">Pre-producción
                                            <input type="checkbox"
                                                name="menu_option[<?php echo $menu->id; ?>]"
                                                value="pre_production"
                                                class="option-checkbox"
                                                data-menu="<?php echo $menu->id; ?>"
                                                data-category="<?php echo $category_id; ?>"
                                            >
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn bg-blue-btn">Guardar</button>
                <a class="btn bg-blue-btn" href="<?php echo base_url() ?>foodMenu/foodMenus">
                    Volver
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</section>

<script>
// Comportamiento tipo radio para los checkbox de menú (solo uno por menú)
document.querySelectorAll('.option-checkbox').forEach(function(item) {
    item.addEventListener('change', function() {
        if (this.checked) {
            var menuId = this.getAttribute('data-menu');
            document.querySelectorAll('.option-checkbox[data-menu="'+menuId+'"]').forEach(function(ck) {
                if (ck !== item) ck.checked = false;
            });
        }
    });
});
// Comportamiento tipo radio para los checkbox de categoría (solo uno por categoría)
document.querySelectorAll('.cat-option-checkbox').forEach(function(catItem) {
    catItem.addEventListener('change', function() {
        if (this.checked) {
            var catId = this.getAttribute('data-category');
            // Solo uno seleccionado por categoría
            document.querySelectorAll('.cat-option-checkbox[data-category="'+catId+'"]').forEach(function(ck) {
                if (ck !== catItem) ck.checked = false;
            });
            // Marcar todos los menús de la categoría con la opción seleccionada
            var value = this.value;
            document.querySelectorAll('.option-checkbox[data-category="'+catId+'"]').forEach(function(menuCk) {
                if(menuCk.value === value) {
                    menuCk.checked = true;
                } else {
                    menuCk.checked = false;
                }
            });
        }
    });
});

// Validación: al menos una opción por menú
document.getElementById('food_menu_form').onsubmit = function() {
    var valid = true;
    <?php //foreach ($grouped_menus as $menus): foreach ($menus as $menu): ?>
    // if (!document.querySelector('.option-checkbox[data-menu="<?php echo $menu->id; ?>"]:checked')) {
    //     valid = false;
    // }
    <?php //endforeach; endforeach; ?>
    // if (!valid) {
    //     alert("Debe seleccionar una opción para cada menú.");
    //     return false;
    // }
    return true;
};
</script>