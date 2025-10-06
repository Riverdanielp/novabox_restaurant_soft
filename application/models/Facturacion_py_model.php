<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Facturacion_py_model extends CI_Model {

    private function _get_facturas_query($filters = []) {
        $this->db->select("
            fe.id, fe.fecha, fe.estado, fe.cdc, fe.id_sale,
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
        if (!empty($filters['estado_id'])) {
            $this->db->where('fe.estado', $filters['estado_id']);
        }
    }

    public function get_facturas_list($limit, $offset, $filters = []) {
        $this->_get_facturas_query($filters);
        $this->db->order_by('fe.fecha', 'DESC');
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
        return $this->db->where('factura_id', $factura_id)->order_by('fecha_modificacion', 'DESC')->get('py_facturas_auditoria')->result();
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