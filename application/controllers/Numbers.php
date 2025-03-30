<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Numbers extends Cl_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
    }

    /**
     * Lista de números
     */
    public function numbers() {
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id'); // Filtrar por sucursal

        $data = array();
        // $data['numbers'] = $this->Common_model->getAllByCompanyIdAndOutlet($company_id, $outlet_id, "tbl_numeros");
        // Obtener todos los números activos
        $this->db->where("outlet_id", $outlet_id);
        $this->db->where("del_status", "Live");
        // $this->db->order_by("name", "ASC");
        $data['numbers'] = $this->db->get("tbl_numeros")->result();

        $data['main_content'] = $this->load->view('master/number/numbers', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    /**
     * Agregar números secuenciales
     */
    public function addNumbers() {
        $company_id = $this->session->userdata('company_id');
        $outlet_id = $this->session->userdata('outlet_id');
        $requested_quantity = $this->input->post('quantity', TRUE);
    
        // Obtener los números existentes, incluyendo su estado y si tienen ventas
        $this->db->where("outlet_id", $outlet_id);
        // $this->db->order_by("name", "ASC");
        $numbers = $this->db->get("tbl_numeros")->result();
    
        $current_count = count($numbers);
        $active_numbers = array_filter(is_array($numbers) ? $numbers : [], fn($n) => $n->del_status == "Live");
        $active_count = count($active_numbers);
    
        // Si la cantidad solicitada es menor a los números activos, desactivar los sobrantes
        if ($requested_quantity < $active_count) {
            $excess_numbers = array_slice($active_numbers, $requested_quantity);
            foreach ($excess_numbers as $num) {
                if (!$num->sale_id) { // Solo desactivar si no tiene venta
                    $this->db->where("id", $num->id)->update("tbl_numeros", ["del_status" => "Deleted"]);
                }
            }
        }
    
        // Si hay números eliminados y la cantidad pedida es mayor, reactivar primero los eliminados
        $reactivated = 0;
        foreach ($numbers as $num) {
            if ($num->del_status == "Deleted" && $reactivated < ($requested_quantity - $active_count)) {
                $this->db->where("id", $num->id)->update("tbl_numeros", ["del_status" => "Live"]);
                $reactivated++;
            }
        }
    
        // Si todavía faltan números, agregarlos nuevos
        $needed = $requested_quantity - ($active_count + $reactivated);
        $new_numbers = [];
        if ($needed > 0) {
            for ($i = $current_count + 1; $i <= $current_count + $needed; $i++) {
                $new_numbers[] = [
                    'name' => (string)$i,
                    'outlet_id' => $outlet_id,
                    'company_id' => $company_id,
                    'del_status' => 'Live'
                ];
            }
            if (!empty($new_numbers)) {
                $this->Common_model->batchInsert('tbl_numeros', $new_numbers);
            }
        }
    
        $this->session->set_flashdata('exception', lang('numbers_updated'));
        redirect('numbers/numbers');
    }
    
}
?>
