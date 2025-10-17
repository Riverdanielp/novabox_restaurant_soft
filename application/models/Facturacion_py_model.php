<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Facturacion_py_model extends CI_Model {

    private function _get_facturas_query($filters = []) {
        $this->db->select("
            fe.tipo_documento, fe.id, fe.fecha, fe.estado, fe.cdc, fe.id_sale,
            CONCAT_WS('-', 
                LPAD(suc.codigo_establecimiento, 3, '0'), 
                LPAD(pe.codigo_punto, 3, '0'), 
                LPAD(fe.numero, 7, '0')
            ) as numero_formateado,
            fc.razon_social as cliente_nombre,
            fu.nombre as usuario_nombre,
            est.descripcion as estado_descripcion
        ");
        $this->db->from('py_facturas_electronicas fe');
        $this->db->join('py_factura_cliente fc', 'fe.cliente_id = fc.id', 'left');
        $this->db->join('py_factura_usuario fu', 'fe.usuario_id = fu.id', 'left');
        $this->db->join('py_sifen_sucursales suc', 'fe.sucursal_id = suc.id', 'left');
        $this->db->join('py_sifen_puntos_expedicion pe', 'fe.punto_expedicion_id = pe.id', 'left');
        $this->db->join('py_sifen_documentos_estados est', 'fe.estado = est.id', 'left');

        // Aplicar filtros
        if (!empty($filters['fecha_inicio'])) {
            $this->db->where('DATE(fe.fecha) >=', $filters['fecha_inicio']);
        }
        if (!empty($filters['fecha_fin'])) {
            $this->db->where('DATE(fe.fecha) <=', $filters['fecha_fin']);
        }
        if (!empty($filters['sucursal_id'])) {
            $this->db->where('fe.sucursal_id', $filters['sucursal_id']);
        }
        if (!empty($filters['punto_id'])) {
            $this->db->where('fe.punto_expedicion_id', $filters['punto_id']);
        }
        if (!empty($filters['usuario_id'])) {
            $this->db->where('fe.usuario_id', $filters['usuario_id']);
        }
        if (isset($filters['estado_id']) && $filters['estado_id'] !== '') {
            $this->db->where('fe.estado', $filters['estado_id']);
        }
        if (!empty($filters['tipo_documento'])) {
            $this->db->where('fe.tipo_documento', $filters['tipo_documento']);
        }
        $allowed_sort = ['fecha','cliente_nombre','usuario_nombre','numero_formateado','estado'];
        if (isset($filters['sort']) && in_array($filters['sort'], $allowed_sort)) {
            $sort_field = $filters['sort'];
            $order = (isset($filters['order']) && strtolower($filters['order']) == 'asc') ? 'ASC' : 'DESC';
            $this->db->order_by($sort_field, $order);
        } else {
            $this->db->order_by('fe.fecha', 'DESC');
        }
    }

    public function get_facturas_list($limit, $offset, $filters = []) {
        $this->_get_facturas_query($filters);
        // $this->db->order_by('fe.fecha', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_facturas($filters = []) {
        $this->_get_facturas_query($filters);
        return $this->db->count_all_results();
    }

    public function get_factura_completa($id) {
        $factura = $this->db->get_where('py_facturas_electronicas', ['id' => $id])->row();
        if ($factura) {
            $factura->cliente = $this->db->get_where('py_factura_cliente', ['id' => $factura->cliente_id])->row();
            $factura->items = $this->db->get_where('py_factura_items', ['factura_id' => $id])->result();
            $factura->condicion = $this->db->get_where('py_factura_condiciones', ['factura_id' => $id])->row();
            $factura->usuario = $this->db->get_where('py_factura_usuario', ['id' => $factura->usuario_id])->row();
            if ($factura->condicion) {
                $factura->condicion->entregas = $this->db->get_where('py_factura_condicion_entregas', ['condicion_id' => $factura->condicion->id])->result();
            }
        }
        return $factura;
    }
    
    public function get_auditoria_logs($factura_id){
        $this->db->select('pa.*, u.full_name as usuario_nombre');
        $this->db->from('py_facturas_auditoria pa');
        $this->db->join('tbl_users u', 'pa.usuario_id = u.id', 'left');
        $this->db->where('pa.factura_id', $factura_id);
        $this->db->order_by('pa.fecha_modificacion', 'DESC');
        $this->db->order_by('pa.id', 'DESC');
        return $this->db->get()->result();
    }

    
    public function buscar_py_clientes($term) {
        if (empty($term)) return [];
        return $this->db
            ->like('razon_social', $term)
            ->or_like('ruc', $term)
            ->limit(10)
            ->get('py_factura_cliente')
            ->result();
    }

    public function buscar_py_items($term) {
        if (empty($term)) return [];
        return $this->db
            ->select('codigo, descripcion, precio_unitario, iva_tipo, iva')
            ->like('descripcion', $term)
            ->or_like('codigo', $term)
            ->group_by(['codigo', 'descripcion']) // Agrupar para obtener items únicos
            ->limit(10)
            ->get('py_factura_items')
            ->result();
    }

    public function buscar_py_usuarios($term) {
        if (empty($term)) return [];
        return $this->db
            ->like('nombre', $term)
            ->or_like('documento_numero', $term)
            ->limit(10)
            ->get('py_factura_usuario')
            ->result();
    }

}