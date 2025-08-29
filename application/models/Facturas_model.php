<?php

class Facturas_model extends CI_Model {
    
    public function getListaNumeracionesAll()
    {
        $this->db->select("n.*,t.nombre as Tipo, d.nombre as TipoDoc,o.outlet_name as Sucursal");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_doc d", "n.tipo_doc = d.id", "left");
        $this->db->join("tbl_outlets o", "n.sucursal = o.id", "left");
        $this->db->order_by("n.tipo", 'ASC');
        $this->db->order_by("n.num_ini", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }
	
    public function getListaNumeracionesAct()
    {
        $this->db->select("n.*,t.nombre as Tipo, d.nombre as TipoDoc,o.outlet_name as Sucursal");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_doc d", "n.tipo_doc = d.id", "left");
        $this->db->join("tbl_outlets o", "n.sucursal = o.id", "left");
        $this->db->where("n.estado", "1");
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }
		
    public function getListaNumeracionesActBySuc()
    {
        $sucursal = $this->session->userdata('outlet_id');
        $this->db->select("n.*,t.nombre as Tipo, d.nombre as TipoDoc,o.outlet_name as Sucursal");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_doc d", "n.tipo_doc = d.id", "left");
        $this->db->join("tbl_outlets o", "n.sucursal = o.id", "left");
        $array_suc = array('0',$sucursal);
        $this->db->where_in("n.sucursal", $array_suc);
        $this->db->where("n.estado", "1");
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }

    public function getListaFacturasConsumo()
    {
        $this->db->select("n.*,t.nombre as Tipo, d.nombre as TipoDoc,o.outlet_name as Sucursal");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_doc d", "n.tipo_doc = d.id", "left");
        $this->db->join("tbl_outlets o", "n.sucursal = o.id", "left");
        $this->db->where("n.estado", "1");
        $this->db->where("n.id", "2");
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }
	
    public function getListaNumeracionesInact()
    {
        $this->db->select("n.*,t.nombre as Tipo, d.nombre as TipoDoc,o.outlet_name as Sucursal");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_doc d", "n.tipo_doc = d.id", "left");
        $this->db->join("tbl_outlets o", "n.sucursal = o.id", "left");
        $this->db->where("n.estado", "2");
        $this->db->or_where("n.estado", "0");
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }

    public function getNumeracion($id)
    {
        $this->db->select("n.*,t.nombre as Tipo, d.nombre as TipoDoc,o.outlet_name as Sucursal");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_doc d", "n.tipo_doc = d.id", "left");
        $this->db->join("tbl_outlets o", "n.sucursal = o.id", "left");
        $this->db->where("n.id", $id);
        $resultados = $this->db->get();
        return $resultados->row();
    }
            
	public function getDetalle_Facturacion($id){
		$this->db->select("v.*,n.prefijo as Prefijo,n.fecha_venc as Vencimiento,t.nombre as Tipo");
		$this->db->from("phppos_facturas_list_fact v");
        $this->db->join("phppos_facturas_numeracion n", "v.numeracion_id  = n.id", "left");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->where("v.id", $id);
		$resultados = $this->db->get();
			return $resultados->row();
	}

    public function getDisponibilidadNumeracion($id)
    {
        $this->db->select("n.*");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->where("n.estado", "1");
        $this->db->where("n.tipo", $id);
        $resultados = $this->db->get();
        //return $resultados->result();
        if (count($resultados->result()) > 0) {
            return true;
        } else {
            return false;
        };
    }
	    	
    public function getNumeracionesxTipo($id)
    {
        $this->db->select("n.*");
        $this->db->from("phppos_facturas_numeracion n");
        $this->db->where("n.estado", "1");
        $this->db->where("n.tipo", $id);
        $resultados = $this->db->get();
        return $resultados->result();
    }

    public function getTipoDocumento()
    {
        $this->db->select("n.*");
        $this->db->from("phppos_facturas_tipo_doc n");
        $this->db->where("n.estado", "1");
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }	
	
    public function getTipos()
    {
        $this->db->select("n.*");
        $this->db->from("phppos_facturas_tipo n");
        $this->db->where("n.estado", "1");
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->result();
    }
    	
    public function getTipo($id)
    {
        $this->db->select("n.*");
        $this->db->from("phppos_facturas_tipo n");
        $this->db->where("n.id", $id);
        $this->db->order_by("n.id", 'ASC');
        $resultados = $this->db->get();
        return $resultados->row();
    }
	
    public function save_numeracion($data)
    {
        return $this->db->insert("phppos_facturas_numeracion", $data);
    }
    
    public function update_numeracion($data,$id)
    {
        $this->db->where("id", $id);
        return $this->db->update("phppos_facturas_numeracion", $data);
    }
         
	public function getUlt_Compra(){
		$this->db->select("c.*");
		$this->db->from("tbl_purchase c");
        $this->db->order_by("c.id", 'DESC');
        $this->db->limit(1);
		$resultados = $this->db->get();
			return $resultados->row();
	}
                  	
    public function getTipoPago()
    {
        $this->db->select("p.*");
        $this->db->from("phppos_facturas_tipo_pago p");
        $resultados = $this->db->get();
        return $resultados->result();
    }
                  	
    public function getTipoCyG()
    {
        $this->db->select("c.*");
        $this->db->from("phppos_facturas_tipo_cyg c");
        $resultados = $this->db->get();
        return $resultados->result();
    }
                    	
    public function getTipoIngresos()
    {
        $this->db->select("c.*");
        $this->db->from("phppos_facturas_tipo_ingreso c");
        $resultados = $this->db->get();
        return $resultados->result();
    } 

    public function getCompraxFactura($id){
		$this->db->select("v.*,t.nombre as Tipo,c.nombre as TipoCyG,p.nombre as TipoPago");
		$this->db->from("phppos_facturas_list_compras v");
        //$this->db->join("phppos_facturas_numeracion n", "v.numeracion_id  = n.id");
        $this->db->join("phppos_facturas_tipo t", "v.numeracion_tipo = t.id", "left");
        $this->db->join("phppos_facturas_tipo_cyg c", "v.tipo_cyg = c.id", "left");
        $this->db->join("phppos_facturas_tipo_pago p", "v.tipo_pago = p.id", "left");
        $this->db->where("v.compra_id", $id);
		$resultados = $this->db->get();
			return $resultados->row();
	}
    
    public function save_compra($data)
    {
        return $this->db->insert("phppos_facturas_list_compras", $data);
    }
	    
    public function update_compra($id,$data)
    {
        $this->db->where("id", $id);
        return $this->db->update("phppos_facturas_list_compras", $data);
    }
    	
    public function getProveedor($id)
    {
        $this->db->select("n.*");
        $this->db->from("tbl_suppliers n");
        $this->db->where("n.id", $id);
        $resultados = $this->db->get();
        return $resultados->row();
    }
    
    public function getSaleInfo($sales_id) {
        //$outlet_id = $this->session->userdata('outlet_id');
        $result = $this->db->query("SELECT s.*,u.full_name,c.name as customer_name,c.gst_number as customer_rcn,c.tipo_ident,m.name,tbl.name as table_name
          FROM tbl_sales s
          LEFT JOIN tbl_customers c ON(s.customer_id=c.id)
          LEFT JOIN tbl_users u ON(s.user_id=u.id)
          LEFT JOIN tbl_payment_methods m ON(s.payment_method_id=m.id)
          LEFT JOIN tbl_tables tbl ON(s.table_id=tbl.id) 
          WHERE s.id=$sales_id AND s.del_status = 'Live'")->row();
        return $result;
    }	 
      
    public function save_fact_venta($data)
    {
        return $this->db->insert("phppos_facturas_list_fact", $data);
    }
  
	public function getVentaxFactura($id){
		$this->db->select("v.*,n.prefijo as Prefijo,n.fecha_venc as Vencimiento,t.nombre as Tipo");
		$this->db->from("phppos_facturas_list_fact v");
        $this->db->join("phppos_facturas_numeracion n", "v.numeracion_id  = n.id", "left");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->where("v.sale_id", $id);
		$resultados = $this->db->get();
			return $resultados->row();
	}
   
	public function getListaFactxNumeracion($id){
		$this->db->select("l.*,n.prefijo as Prefijo,n.fecha_venc as Vencimiento,t.nombre as Tipo");
		$this->db->from("phppos_facturas_list_fact l");
        $this->db->join("phppos_facturas_numeracion n", "l.numeracion_id  = n.id", "left");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->where("l.numeracion_id", $id);
		$resultados = $this->db->get();
			return $resultados->result();
	}
     
    function store_factura_venta($datos){
        
        $numeracion = $this->getNumeracion($datos['numeracion']);

        $numero = $numeracion->num_sig;

        $data  = array(
            'numeracion_id' => $datos['numeracion'],
            'numero' => $numero,
            'sale_id' => $datos['id'],
            'cliente_id' => $datos['cliente_id'],
            'nombre' => $datos['Nombre_id'],
            'rnc' => $datos['RNC_id'],
            'tipo_ingreso' => $datos['tipo_ingreso'],
            'fecha_comprobante' => $datos['fecha'],
            'tipo_ident' => $datos['tipo_doc'],
            'estado' => '1',
        );

        if ($this->save_fact_venta($data)){
            $this->update_fact_numeracion($datos['numeracion']);
            return true; 
        } else {
            return false;
        };
    }
    
	function update_fact_numeracion($id){
        $numeracion = $this->getNumeracion($id);
        $numero_final = $numeracion->num_fin;
        $numero_sig = $numeracion->num_sig + 1;
    
        if ($numero_sig > $numero_final && $numero_final != NULL){
            $data = array(
                'estado' => '2',
            );
            $this->update_numeracion($data,$id);
        } else {
            $data = array(
                'num_sig' => $numero_sig,
            );
            $this->update_numeracion($data,$id);
        };
    }
    	    
	public function getCompraxFecha($ini,$fin){
		$this->db->select("v.*,t.nombre as Tipo,r.*,c.nombre as TipoCyG,p.nombre as TipoPago");
		$this->db->from("phppos_facturas_list_compras v");
        //$this->db->join("phppos_facturas_numeracion n", "v.numeracion_id  = n.id");
        $this->db->join("phppos_facturas_tipo t", "v.numeracion_tipo = t.id", "left");
        $this->db->join("tbl_purchase r", "v.compra_id = r.id", "left");
        $this->db->join("phppos_facturas_tipo_cyg c", "v.tipo_cyg = c.id", "left");
        $this->db->join("phppos_facturas_tipo_pago p", "v.tipo_pago = p.id", "left");
        $this->db->where("v.fecha_comprobante >=", $ini);
        $this->db->where("v.fecha_comprobante <=", $fin);
		$resultados = $this->db->get();
			return $resultados->result();
	}

	public function getFactxFecha($ini,$fin){
		$this->db->select("l.*,s.*,n.prefijo as Prefijo,n.fecha_venc as Vencimiento,t.nombre as Tipo,t.id as id_Tipo,m.name as TipoPago");
		$this->db->from("phppos_facturas_list_fact l");
        $this->db->join("phppos_facturas_numeracion n", "l.numeracion_id  = n.id", "left");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("tbl_sales s", "l.sale_id  = s.id", "left");
        $this->db->join("tbl_payment_methods m", "s.payment_method_id  = m.id", "left");
        $this->db->where("l.fecha_comprobante >=", $ini);
        $this->db->where("l.fecha_comprobante <=", $fin);
		$resultados = $this->db->get();
			return $resultados->result();
	}
    
	public function getFactxTipoxFecha($tipo,$ini,$fin){
		$this->db->select("l.*,s.*,n.prefijo as Prefijo,n.fecha_venc as Vencimiento,t.nombre as Tipo,t.id as id_Tipo,m.name as TipoPago");
		$this->db->from("phppos_facturas_list_fact l");
        $this->db->join("phppos_facturas_numeracion n", "l.numeracion_id  = n.id", "left");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("tbl_sales s", "l.sale_id  = s.id");
        $this->db->join("tbl_payment_methods m", "s.payment_method_id  = m.id", "left");
        if ($tipo != 'all'){
            $this->db->where("t.id", $tipo);
        };
        $this->db->where("l.fecha_comprobante >=", $ini);
        $this->db->where("l.fecha_comprobante <=", $fin);
        $this->db->order_by("l.id", 'DESC');
		$resultados = $this->db->get();
			return $resultados->result();
	}
    
	public function getFactxID_Venta($id_venta){
		$this->db->select("l.*,s.*,n.prefijo as Prefijo,n.fecha_venc as Vencimiento,t.nombre as Tipo,t.id as id_Tipo,m.name as TipoPago");
		$this->db->from("phppos_facturas_list_fact l");
        $this->db->join("phppos_facturas_numeracion n", "l.numeracion_id  = n.id", "left");
        $this->db->join("phppos_facturas_tipo t", "n.tipo = t.id", "left");
        $this->db->join("tbl_sales s", "l.sale_id  = s.id");
        $this->db->join("tbl_payment_methods m", "s.payment_method_id  = m.id", "left");
        $this->db->where("l.sale_id", $id_venta);
		$resultados = $this->db->get();
			return $resultados->result();
	}

	public function getItems_Venta($id_venta){
		$this->db->select("l.*");
		$this->db->from("tbl_sales_details l");
        $this->db->where("l.sales_id", $id_venta);
		$resultados = $this->db->get();
			return $resultados->result();
	}

}