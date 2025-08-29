<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Facturas extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Facturas_model');
        $this->load->model('Sale_model');
        $this->load->model('Kitchen_model');
        $this->load->model('Waiter_model');
        $this->load->model('Master_model');
        $this->load->model('Inventory_model');
        $this->load->library('form_validation');
        // $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->Common_model->setDefaultTimezone();
        if (!$this->session->has_userdata('user_id') && $this->session->has_userdata('is_online_order')!="Yes") {
            redirect('Authentication/index');
        }

        if (!$this->session->has_userdata('outlet_id')) {
            $this->session->set_flashdata('exception_2', 'Please click on green Enter button of an outlet');
            redirect('Outlet/outlets');
        }
        
        // $is_waiter = $this->session->userdata('is_waiter');
        // $designation = $this->session->userdata('designation');
        // //check register is open or not
        // if($designation!="Waiter" && $this->session->has_userdata('is_online_order')!="Yes" && !isFoodCourt()){
        //     $user_id = $this->session->userdata('user_id');
        //     $outlet_id = $this->session->userdata('outlet_id');
        //     if($this->Common_model->isOpenRegister($user_id,$outlet_id)==0){
        //         $this->session->set_flashdata('exception_3', lang('register_open_msg'));
        //         if($this->uri->segment(2)=='registerDetailCalculationToShowAjax' || $this->uri->segment(2)=='closeRegister'){
        //             redirect('Register/openRegister');
        //         }else{
        //             $this->session->set_userdata("clicked_controller", $this->uri->segment(1));
        //             $this->session->set_userdata("clicked_method", $this->uri->segment(2));
        //             redirect('Register/openRegister');
        //         }

        //     }
        // }

        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }

    function tester($id) {
		$Numeraciones = $this->Facturas_model->getListaNumeracionesAct();
		echo datos_factura($id)->Vencimiento;
		echo "<br><br>";
		$vencimiento = datos_factura($id)->Vencimiento;
		$date = date_create("$vencimiento");
		echo date_format($date,"d/m/Y");
		echo "<br><br>";
		echo "<br><br>";
		echo "<pre>";
		var_dump(datos_factura($id));
		echo "</pre>";
    }

    function index() {
        //$Tipos = ;
        
        $data = array();
        $data['Tipos'] = $this->Facturas_model->getTipos();
        $data['Numeraciones'] = $this->Facturas_model->getListaNumeracionesAct();
		$data['sucursales'] =$this->Common_model->getAllOutlestByAssign();

        $data['main_content'] = $this->load->view('facturas/list', $data, TRUE);
        $this->load->view('userHome', $data);
    }
	
	function inactivos()
	{
		$data = array(
			'Numeraciones' => $this->Facturas_model->getListaNumeracionesInact(),
		);
		
        $data['main_content'] = $this->load->view('facturas/list_inactivos', $data, TRUE);
        $this->load->view('userHome', $data);
	}

	function db_rnc_dgii(){
		//$file = base_url("assets/DGII_RNC.TXT"); rnc_0.txt
		//$file = base_url().'assets/assets/DGII_RNC.TXT';
		$rnc = $this->input->post("rnc");
		//$rnc = '00110477981';
		//$rnc = '12345678';
		if (strlen($rnc) >= 9 && strlen($rnc) <= 11 && is_numeric($rnc)){

            if ($this->recorrer_txt($rnc) != false){
                $datos_cliente = $this->recorrer_txt($rnc);
            };
				
			if (!isset($datos_cliente)){
				echo json_encode('CRN/Cédula no encontrado.');
				//echo json_encode('No encontrado!');
			} else {
				//$datos_cliente = utf8_decode($datos_cliente);
				echo json_encode($datos_cliente,JSON_UNESCAPED_UNICODE);
				//echo $datos_cliente;
			};
			
			
		} else {
            $respuesta = 'Solo 9 a 11 carácteres numéricos.';
			echo json_encode($respuesta);
		};
	}

    public function recorrer_txt($rnc){
		
		$ArchivoLeer = base_url()."assets/media/DGII_RNC.TXT";
		
		if(touch($ArchivoLeer)){
			//
			$archivoID = fopen($ArchivoLeer, "r");
			//
			while( !feof($archivoID)){
				$linea = fgets($archivoID, 1024);
				if (strstr($linea,$rnc)){
					$datos_cliente = $linea;
				}
			}
			fclose($archivoID);
		};

		if (!isset($datos_cliente)){
			//echo json_encode('CRN/Cedula no encontrado.');
			return false;
		} else {
			$datos_cliente = utf8_encode($datos_cliente);
			return $datos_cliente;
			//echo $datos_cliente;
		};
	}

	function view_numeracion($id)

	{
		$data = array(
			'Numeraciones' => $this->Facturas_model->getListaFactxNumeracion($id),
			'Numeracion' => $this->Facturas_model->getNumeracion($id),
		);
		
        $data['main_content'] = $this->load->view('facturas/view_numeracion', $data, TRUE);
        $this->load->view('userHome', $data);
	}


	function desactivar_numeracion($id)
	{
		//echo $id;
		$data  = array(
			'estado' => "0",
		);
		$this->Facturas_model->update_numeracion($data,$id);
		redirect(base_url("facturas/"));
	}
	
	function activar_numeracion($id)
	{
		//echo $id;
		$data  = array(
			'estado' => "1",
		);
		$this->Facturas_model->update_numeracion($data,$id);
		redirect(base_url("facturas/"));
	}
	
	function add_numeracion()
	{
		$data = array();
        
        $data['Tipos'] = $this->Facturas_model->getTipos();
        $data['Numeraciones'] = $this->Facturas_model->getListaNumeracionesAct();
        $data['TipoDocumentos'] = $this->Facturas_model->getTipoDocumento();
		$data['sucursales'] =$this->Common_model->getAllOutlestByAssign();
		
        $data['main_content'] = $this->load->view('facturas/add_numeracion', $data, TRUE);
        $this->load->view('userHome', $data);
	}


	function store_numeracion(){
		// echo "<pre>";
		// var_dump($_POST);
		// echo "</pre>";

		$tipo_doc = $this->input->post("tipo_doc");
		$tipo = $this->input->post("tipo");
		if ($tipo > 0){
			$Tipo_fact = $this->Facturas_model->getTipo($tipo);
			if (!empty($Tipo_fact)) {
				$prefijo = $Tipo_fact->prefijo;
			} else {
				$prefijo = NULL;
			}
		} else {
			$prefijo = NULL;
		};

		$nombre = $this->input->post("nombre");
		$sucursal = $this->input->post("sucursal");
		$num_ini = $this->input->post("num_ini");
		$num_fin = $this->input->post("num_fin");
		if ($num_fin == "") {
			$num_fin = NULL;
		};
		$fecha_venc = $this->input->post("fecha_venc");
		if ($fecha_venc == ""){
			$fecha_venc = NULL;
		} else {
			$fecha_venc =  date("Y-m-d", strtotime($fecha_venc));
		};

		//$this->form_validation->set_rules("plannro", "Plan", "required|is_unique[planes.PLAN_NRO]");
		$this->form_validation->set_rules("tipo_doc", "Tipo de Documento", "required");
		$this->form_validation->set_rules("tipo", "Tipo de Factura", "required");
		$this->form_validation->set_rules("nombre", "Nombre", "required");
		$this->form_validation->set_rules("num_ini", "Número Inicial", "required");
		//$this->form_validation->set_rules('num_fin', 'Número Final','callback_correlativo_check[num_ini]');
		$this->form_validation->set_rules('num_fin', 'Número Final',"callback_correlativo_check[$num_ini]");

		if ($this->form_validation->run()) {
			$data  = array(
				'tipo_doc' => $tipo_doc,
				'tipo' => $tipo,
				'nombre' => $nombre,
				'num_ini' => $num_ini,
				'num_fin' => $num_fin,
				'num_sig' => $num_ini,
				'fecha_venc' => $fecha_venc,
				'prefijo' => $prefijo,
				'sucursal' => $sucursal,
				'estado' => "1"
			);

			if ($this->Facturas_model->save_numeracion($data)) {
				redirect(base_url("facturas/"));
			} else {
				$this->session->set_flashdata("error", "No se pudo guardar la informacion");
				redirect(base_url("facturas/add_numeracion/"));
			}
		} else {
			$this->add_numeracion();
		}
	}

	function correlativo_check($fin, $ini){
		if ($fin == NULL) {
			return TRUE;
		} else {
			if ($ini >= $fin){
				$this->form_validation->set_message('correlativo_check', 'El número final debe ser mayor al número inicial.');
				return FALSE;
			} else {
				return TRUE;
			};
		}
	}
	
	function num_sig_ini_check($sig, $ini){
		if ($sig < $ini){
			$this->form_validation->set_message('num_sig_ini_check', 'El número siguiente debe ser mayor al número inicial.');
			return FALSE;
		} else {
			return TRUE;
		};
	}

	function num_sig_fin_check($sig, $fin){
			if ($fin == NULL) {
				return TRUE;
			} else {
				if ($sig > $fin){
					$this->form_validation->set_message('num_sig_fin_check', 'El número siguiente debe estar en el rango correspondiente.');
					return FALSE;
				} else {
					return TRUE;
				};
			}
	}
		
	function edit_numeracion($id)
	{
		//echo $id;
		$data = array(
			'numeracion' => $this->Facturas_model->getNumeracion($id),
			'Numeraciones' => $this->Facturas_model->getListaNumeracionesAct(),
			'TipoDocumentos' => $this->Facturas_model->getTipoDocumento(),
			'Tipos' => $this->Facturas_model->getTipos(),
		);
		$data['sucursales'] =$this->Common_model->getAllOutlestByAssign();
        
        $data['main_content'] = $this->load->view('facturas/edit_numeracion', $data, TRUE);
        $this->load->view('userHome', $data);
	}
	
	function update_numeracion(){
		// echo "<pre>";
		// var_dump($_POST);
		// echo "</pre>";

		$id = $this->input->post("id");
		$tipo_doc = $this->input->post("tipo_doc");
		$sucursal = $this->input->post("sucursal");
		$tipo = $this->input->post("tipo");
		if ($tipo > 0){
			$Tipo_fact = $this->Facturas_model->getTipo($tipo);
			if (!empty($Tipo_fact)) {
				$prefijo = $Tipo_fact->prefijo;
			} else {
				$prefijo = NULL;
			}
		} else {
			$prefijo = NULL;
		};

		$nombre = $this->input->post("nombre");
		$num_ini = $this->input->post("num_ini");
		$num_fin = $this->input->post("num_fin");
		if ($num_fin == "") {
			$num_fin = NULL;
		};
		$fecha_venc = $this->input->post("fecha_venc");
		if ($fecha_venc == ""){
			$fecha_venc = NULL;
		} else {
			$fecha_venc =  date("Y-m-d", strtotime($fecha_venc));
		};
		$numeros = $num_ini . "," . $num_fin;
		$num_sig = $this->input->post("num_sig");

		$this->form_validation->set_rules("tipo_doc", "Tipo de Documento", "required");
		$this->form_validation->set_rules("tipo", "Tipo de Factura", "required");
		$this->form_validation->set_rules("nombre", "Nombre", "required");
		$this->form_validation->set_rules("num_ini", "Número Inicial", "required");
		$this->form_validation->set_rules('num_fin', 'Número Final',"callback_correlativo_check[$num_ini]");
		$this->form_validation->set_rules('num_sig', 'Número Siguiente',"required|callback_num_sig_ini_check[$num_ini]|callback_num_sig_fin_check[$num_fin]");

		if ($this->form_validation->run()) {
			$data  = array(
				'tipo_doc' => $tipo_doc,
				'tipo' => $tipo,
				'nombre' => $nombre,
				'num_ini' => $num_ini,
				'num_fin' => $num_fin,
				'fecha_venc' => $fecha_venc,
				'prefijo' => $prefijo,
				'num_sig' => $num_sig,
				'sucursal' => $sucursal,
			);

			if ($this->Facturas_model->update_numeracion($data,$id)) {
				redirect(base_url("facturas/"));
			} else {
				$this->session->set_flashdata("error", "No se pudo guardar la informacion");
				redirect(base_url("facturas/edit_numeracion/$id"));
			}
			
			// echo "<pre>";
			// var_dump($data);
			// echo "</pre>";
		} else {
			$this->edit_numeracion($id);
		}
	}
    
	function NumeracionesActivasByJson()
	{
		echo json_encode($this->Facturas_model->getListaNumeracionesAct());
	}
	
	function nueva_factura_venta($id,$numeracion_id){

        $numeracion = $this->Facturas_model->getNumeracion($numeracion_id);
		//nueva_factura_venta($id,$numeracion_id);
		if ($numeracion->estado == 1){

			var_dump($numeracion);
		} else {
			echo 'false';
		}
	}

	function store_factura_venta($datos){
		
		$numeracion = $this->Facturas_model->getNumeracion($datos['numeracion']);

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

		if ($this->Facturas_model->save_fact_compra($data)){
			$this->update_fact_numeracion($datos['numeracion']);
			return true; 
		} else {
			return false;
		};
	}
		
	private function update_fact_numeracion($id){
		$numeracion = $this->Facturas_model->getNumeracion($id);
		$numero_final = $numeracion->num_fin;
		$numero_sig = $numeracion->num_sig + 1;

		if ($numero_sig > $numero_final){
			$data = array(
				'estado' => '2',
			);
			$this->Facturas_model->update_numeracion($data,$id);
		} else {
			$data = array(
				'num_sig' => $numero_sig,
			);
			$this->Facturas_model->update_numeracion($data,$id);
		};

	}
	
	function reporte606(){
		$fecha_ini = $this->input->post("fecha_ini");
		$fecha_fin = $this->input->post("fecha_fin");
		$Numeraciones = $this->Facturas_model->getCompraxFecha($fecha_ini,$fecha_fin);

		$data = array(
			'fecha_ini' => $fecha_ini,
			'fecha_fin' => $fecha_fin,
			'Numeraciones' => $Numeraciones,
			'TipoPago' => TipoPago(),
			'TipoCyG' => TipoCyG(),
			'Tipos' => TipoNumeracion(),
		);
		
        $data['main_content'] = $this->load->view('facturas/reporte606', $data, TRUE);
        $this->load->view('userHome', $data);
	}

	function reporte607(){
		$fecha_ini = $this->input->post("fecha_ini");
		$fecha_fin = $this->input->post("fecha_fin");
		$Numeraciones = $this->Facturas_model->getFactxFecha($fecha_ini,$fecha_fin);

		$data = array(
			'fecha_ini' => $fecha_ini,
			'fecha_fin' => $fecha_fin,
			'Numeraciones' => $Numeraciones,
			'TipoIngresos' => TipoIngresos(),
			'Tipos' => TipoNumeracion(),
		);
		
        $data['main_content'] = $this->load->view('facturas/reporte607', $data, TRUE);
        $this->load->view('userHome', $data);
	}
	function txt_606(){
		$mes = $this->input->post("mes");
		$RNC_EMPRESA = escape_output($this->session->userdata('tax_registration_no')); //tax_id
		$indice = $this->input->post("indice");
		$rnc = $this->input->post("rnc");
		$tipo_ident = $this->input->post("tipo_ident");
		$TIPO_BIENES = $this->input->post("TIPO_BIENES");
		$ncf = $this->input->post("ncf");
		$NUMERO_COMPROBANTE_MODIFICADO = $this->input->post("NUMERO_COMPROBANTE_MODIFICADO");
		$fecha_comprobante = $this->input->post("fecha_comprobante");
		$dia_comprobante = $this->input->post("dia_comprobante");
		$fecha_pago = $this->input->post("fecha_pago");
		$dia_pago = $this->input->post("dia_pago");
		$MONTO_SERVICIOS = $this->input->post("MONTO_SERVICIOS");
		$MONTO_BIENES = $this->input->post("MONTO_BIENES");
		$monto_facturado = $this->input->post("monto_facturado");
		$itbis_facturado = $this->input->post("itbis_facturado");
		$ITBIS_RETENIDO = $this->input->post("ITBIS_RETENIDO");
		$ITBIS_PROPORCIONALIDAD = $this->input->post("ITBIS_PROPORCIONALIDAD");
		$ITBIS_LLEVADO_COSTO = $this->input->post("ITBIS_LLEVADO_COSTO");
		$ITBIS_ADELANTAR = $this->input->post("ITBIS_ADELANTAR");
		$ITBIS_PERCIBIDO = $this->input->post("ITBIS_PERCIBIDO");
		$ISR_TIPO_RETENCION = $this->input->post("ISR_TIPO_RETENCION");
		$ISR_RETENCION_RENTA = $this->input->post("ISR_RETENCION_RENTA");
		$ISR_PERCIBIDO = $this->input->post("ISR_PERCIBIDO");
		$IMPUESTO_SELECTIVO_CONSUMO = $this->input->post("IMPUESTO_SELECTIVO_CONSUMO");
		$OTROS_IMPUESTOS_TASAS = $this->input->post("OTROS_IMPUESTOS_TASAS");
		$MONTO_PROPINA_LEGAL = $this->input->post("MONTO_PROPINA_LEGAL");
		$Proporcionalidad = $this->input->post("Proporcionalidad");
		$tipo_pago = $this->input->post("tipo_pago");
		$SN = $this->input->post("SN");
		$TipoCyG = $this->input->post("TipoCyG");
		$numero_interno = $this->input->post("numero_interno");
		$codigo_iva = $this->input->post("codigo_iva");
		$TipoFact = $this->input->post("TipoFact");
		$tipo_ret = $this->input->post("tipo_ret");

		$data = array(
			'mes' => $mes,
			'RNC_EMPRESA' => $RNC_EMPRESA,
			'indice' => $indice,
			'rnc' => $rnc,
			'tipo_ident' => $tipo_ident,
			'TIPO_BIENES' => $TIPO_BIENES,
			'ncf' => $ncf,
			'NUMERO_COMPROBANTE_MODIFICADO' => $NUMERO_COMPROBANTE_MODIFICADO,
			'fecha_comprobante' => $fecha_comprobante,
			'dia_comprobante' => $dia_comprobante,
			'fecha_pago' => $fecha_pago,
			'dia_pago' => $dia_pago,
			'MONTO_SERVICIOS' => $MONTO_SERVICIOS,
			'MONTO_BIENES' => $MONTO_BIENES,
			'monto_facturado' => $monto_facturado,
			'itbis_facturado' => $itbis_facturado,
			'ITBIS_RETENIDO' => $ITBIS_RETENIDO,
			'ITBIS_PROPORCIONALIDAD' => $ITBIS_PROPORCIONALIDAD,
			'ITBIS_LLEVADO_COSTO' => $ITBIS_LLEVADO_COSTO,
			'ITBIS_ADELANTAR' => $ITBIS_ADELANTAR,
			'ITBIS_PERCIBIDO' => $ITBIS_PERCIBIDO,
			'ISR_TIPO_RETENCION' => $ISR_TIPO_RETENCION,
			'ISR_RETENCION_RENTA' => $ISR_RETENCION_RENTA,
			'ISR_PERCIBIDO' => $ISR_PERCIBIDO,
			'IMPUESTO_SELECTIVO_CONSUMO' => $IMPUESTO_SELECTIVO_CONSUMO,
			'OTROS_IMPUESTOS_TASAS' => $OTROS_IMPUESTOS_TASAS,
			'MONTO_PROPINA_LEGAL' => $MONTO_PROPINA_LEGAL,
			'Proporcionalidad' => $Proporcionalidad,
			'tipo_pago' => $tipo_pago,
			'SN' => $SN,
			'TipoCyG' => $TipoCyG,
			'numero_interno' => $numero_interno,
			'codigo_iva' => $codigo_iva,
			'TipoFact' => $TipoFact,
			'tipo_ret' => $tipo_ret,
		);
		//var_dump($_POST);

		$this->load->view('facturas/txt_606',$data);
	
	}
	
	function excel606(){
		$indice = $this->input->post("indice");
		$rnc = $this->input->post("rnc");
		$tipo_ident = $this->input->post("tipo_ident");
		$TIPO_BIENES = $this->input->post("TIPO_BIENES");
		$ncf = $this->input->post("ncf");
		$NUMERO_COMPROBANTE_MODIFICADO = $this->input->post("NUMERO_COMPROBANTE_MODIFICADO");
		$fecha_comprobante = $this->input->post("fecha_comprobante");
		$dia_comprobante = $this->input->post("dia_comprobante");
		$fecha_pago = $this->input->post("fecha_pago");
		$dia_pago = $this->input->post("dia_pago");
		$MONTO_SERVICIOS = $this->input->post("MONTO_SERVICIOS");
		$MONTO_BIENES = $this->input->post("MONTO_BIENES");
		$monto_facturado = $this->input->post("monto_facturado");
		$itbis_facturado = $this->input->post("itbis_facturado");
		$ITBIS_RETENIDO = $this->input->post("ITBIS_RETENIDO");
		$ITBIS_PROPORCIONALIDAD = $this->input->post("ITBIS_PROPORCIONALIDAD");
		$ITBIS_LLEVADO_COSTO = $this->input->post("ITBIS_LLEVADO_COSTO");
		$ITBIS_ADELANTAR = $this->input->post("ITBIS_ADELANTAR");
		$ITBIS_PERCIBIDO = $this->input->post("ITBIS_PERCIBIDO");
		$ISR_TIPO_RETENCION = $this->input->post("ISR_TIPO_RETENCION");
		$ISR_RETENCION_RENTA = $this->input->post("ISR_RETENCION_RENTA");
		$ISR_PERCIBIDO = $this->input->post("ISR_PERCIBIDO");
		$IMPUESTO_SELECTIVO_CONSUMO = $this->input->post("IMPUESTO_SELECTIVO_CONSUMO");
		$OTROS_IMPUESTOS_TASAS = $this->input->post("OTROS_IMPUESTOS_TASAS");
		$MONTO_PROPINA_LEGAL = $this->input->post("MONTO_PROPINA_LEGAL");
		$Proporcionalidad = $this->input->post("Proporcionalidad");
		$tipo_pago = $this->input->post("tipo_pago");
		$SN = $this->input->post("SN");
		$TipoCyG = $this->input->post("TipoCyG");
		$numero_interno = $this->input->post("numero_interno");
		$codigo_iva = $this->input->post("codigo_iva");
		$TipoFact = $this->input->post("TipoFact");
		$tipo_ret = $this->input->post("tipo_ret");

		$data = array(
			'indice' => $indice,
			'rnc' => $rnc,
			'tipo_ident' => $tipo_ident,
			'TIPO_BIENES' => $TIPO_BIENES,
			'ncf' => $ncf,
			'NUMERO_COMPROBANTE_MODIFICADO' => $NUMERO_COMPROBANTE_MODIFICADO,
			'fecha_comprobante' => $fecha_comprobante,
			'dia_comprobante' => $dia_comprobante,
			'fecha_pago' => $fecha_pago,
			'dia_pago' => $dia_pago,
			'MONTO_SERVICIOS' => $MONTO_SERVICIOS,
			'MONTO_BIENES' => $MONTO_BIENES,
			'monto_facturado' => $monto_facturado,
			'itbis_facturado' => $itbis_facturado,
			'ITBIS_RETENIDO' => $ITBIS_RETENIDO,
			'ITBIS_PROPORCIONALIDAD' => $ITBIS_PROPORCIONALIDAD,
			'ITBIS_LLEVADO_COSTO' => $ITBIS_LLEVADO_COSTO,
			'ITBIS_ADELANTAR' => $ITBIS_ADELANTAR,
			'ITBIS_PERCIBIDO' => $ITBIS_PERCIBIDO,
			'ISR_TIPO_RETENCION' => $ISR_TIPO_RETENCION,
			'ISR_RETENCION_RENTA' => $ISR_RETENCION_RENTA,
			'ISR_PERCIBIDO' => $ISR_PERCIBIDO,
			'IMPUESTO_SELECTIVO_CONSUMO' => $IMPUESTO_SELECTIVO_CONSUMO,
			'OTROS_IMPUESTOS_TASAS' => $OTROS_IMPUESTOS_TASAS,
			'MONTO_PROPINA_LEGAL' => $MONTO_PROPINA_LEGAL,
			'Proporcionalidad' => $Proporcionalidad,
			'tipo_pago' => $tipo_pago,
			'SN' => $SN,
			'TipoCyG' => $TipoCyG,
			'numero_interno' => $numero_interno,
			'codigo_iva' => $codigo_iva,
			'TipoFact' => $TipoFact,
			'tipo_ret' => $tipo_ret,
		);
		
		$this->load->view('facturas/excel606',$data);
	}

	function excel607(){
		$indice = $this->input->post("indice");
		$rnc = $this->input->post("rnc");
		$tipo_ident = $this->input->post("tipo_ident");
		$ncf = $this->input->post("ncf");
		$NUMERO_COMPROBANTE_MODIFICADO = $this->input->post("NUMERO_COMPROBANTE_MODIFICADO");
		$TIPO_INGRESO = $this->input->post("TIPO_INGRESO");
		$fecha_comprobante = $this->input->post("fecha_comprobante");
		$fecha_retencion = $this->input->post("fecha_retencion");
		$monto_facturado = $this->input->post("monto_facturado");
		$itbis_facturado = $this->input->post("itbis_facturado");
		$ITBIS_RETENIDO = $this->input->post("ITBIS_RETENIDO");
		$ITBIS_PERCIBIDO = $this->input->post("ITBIS_PERCIBIDO");
		$ISR_RETENCION_RENTA = $this->input->post("ISR_RETENCION_RENTA");
		$ISR_PERCIBIDO = $this->input->post("ISR_PERCIBIDO");
		$IMPUESTO_SELECTIVO_CONSUMO = $this->input->post("IMPUESTO_SELECTIVO_CONSUMO");
		$OTROS_IMPUESTOS_TASAS = $this->input->post("OTROS_IMPUESTOS_TASAS");
		$MONTO_PROPINA_LEGAL = $this->input->post("MONTO_PROPINA_LEGAL");
		$EFECTIVO = $this->input->post("EFECTIVO");
		$CHEQUE = $this->input->post("CHEQUE");
		$TARJETA = $this->input->post("TARJETA");
		$CREDITO = $this->input->post("CREDITO");
		$BONOS = $this->input->post("BONOS");
		$PERMUTA = $this->input->post("PERMUTA");
		$OTRAS_FORMAS = $this->input->post("OTRAS_FORMAS");
		$SN = $this->input->post("SN");
		$numero_interno = $this->input->post("numero_interno");

		$data = array(
			'indice' => $indice,
			'rnc' => $rnc,
			'tipo_ident' => $tipo_ident,
			'ncf' => $ncf,
			'NUMERO_COMPROBANTE_MODIFICADO' => $NUMERO_COMPROBANTE_MODIFICADO,
			'TIPO_INGRESO' => $TIPO_INGRESO,
			'fecha_comprobante' => $fecha_comprobante,
			'fecha_retencion' => $fecha_retencion,
			'monto_facturado' => $monto_facturado,
			'itbis_facturado' => $itbis_facturado,
			'ITBIS_RETENIDO' => $ITBIS_RETENIDO,
			'ITBIS_PERCIBIDO' => $ITBIS_PERCIBIDO,
			'ISR_RETENCION_RENTA' => $ISR_RETENCION_RENTA,
			'ISR_PERCIBIDO' => $ISR_PERCIBIDO,
			'IMPUESTO_SELECTIVO_CONSUMO' => $IMPUESTO_SELECTIVO_CONSUMO,
			'OTROS_IMPUESTOS_TASAS' => $OTROS_IMPUESTOS_TASAS,
			'MONTO_PROPINA_LEGAL' => $MONTO_PROPINA_LEGAL,
			'EFECTIVO' => $EFECTIVO,
			'CHEQUE' => $CHEQUE,
			'TARJETA' => $TARJETA,
			'CREDITO' => $CREDITO,
			'BONOS' => $BONOS,
			'PERMUTA' => $PERMUTA,
			'OTRAS_FORMAS' => $OTRAS_FORMAS,
			'SN' => $SN,
			'numero_interno' => $numero_interno,
		);
		
		$this->load->view('facturas/excel607',$data);
	}	
	function txt_607(){
		$mes = $this->input->post("mes");
		$RNC_EMPRESA = escape_output($this->session->userdata('tax_registration_no')); //tax_id
		$indice = $this->input->post("indice");
		$rnc = $this->input->post("rnc");
		$tipo_ident = $this->input->post("tipo_ident");
		$ncf = $this->input->post("ncf");
		$NUMERO_COMPROBANTE_MODIFICADO = $this->input->post("NUMERO_COMPROBANTE_MODIFICADO");
		$TIPO_INGRESO = $this->input->post("TIPO_INGRESO");
		$fecha_comprobante = $this->input->post("fecha_comprobante");
		$fecha_retencion = $this->input->post("fecha_retencion");
		$monto_facturado = $this->input->post("monto_facturado");
		$itbis_facturado = $this->input->post("itbis_facturado");
		$ITBIS_RETENIDO = $this->input->post("ITBIS_RETENIDO");
		$ITBIS_PERCIBIDO = $this->input->post("ITBIS_PERCIBIDO");
		$ISR_RETENCION_RENTA = $this->input->post("ISR_RETENCION_RENTA");
		$ISR_PERCIBIDO = $this->input->post("ISR_PERCIBIDO");
		$IMPUESTO_SELECTIVO_CONSUMO = $this->input->post("IMPUESTO_SELECTIVO_CONSUMO");
		$OTROS_IMPUESTOS_TASAS = $this->input->post("OTROS_IMPUESTOS_TASAS");
		$MONTO_PROPINA_LEGAL = $this->input->post("MONTO_PROPINA_LEGAL");
		$EFECTIVO = $this->input->post("EFECTIVO");
		$CHEQUE = $this->input->post("CHEQUE");
		$TARJETA = $this->input->post("TARJETA");
		$CREDITO = $this->input->post("CREDITO");
		$BONOS = $this->input->post("BONOS");
		$PERMUTA = $this->input->post("PERMUTA");
		$OTRAS_FORMAS = $this->input->post("OTRAS_FORMAS");
		$SN = $this->input->post("SN");
		$numero_interno = $this->input->post("numero_interno");

		$data = array(
			'mes' => $mes,
			'RNC_EMPRESA' => $RNC_EMPRESA,
			'indice' => $indice,
			'rnc' => $rnc,
			'tipo_ident' => $tipo_ident,
			'ncf' => $ncf,
			'NUMERO_COMPROBANTE_MODIFICADO' => $NUMERO_COMPROBANTE_MODIFICADO,
			'TIPO_INGRESO' => $TIPO_INGRESO,
			'fecha_comprobante' => $fecha_comprobante,
			'fecha_retencion' => $fecha_retencion,
			'monto_facturado' => $monto_facturado,
			'itbis_facturado' => $itbis_facturado,
			'ITBIS_RETENIDO' => $ITBIS_RETENIDO,
			'ITBIS_PERCIBIDO' => $ITBIS_PERCIBIDO,
			'ISR_RETENCION_RENTA' => $ISR_RETENCION_RENTA,
			'ISR_PERCIBIDO' => $ISR_PERCIBIDO,
			'IMPUESTO_SELECTIVO_CONSUMO' => $IMPUESTO_SELECTIVO_CONSUMO,
			'OTROS_IMPUESTOS_TASAS' => $OTROS_IMPUESTOS_TASAS,
			'MONTO_PROPINA_LEGAL' => $MONTO_PROPINA_LEGAL,
			'EFECTIVO' => $EFECTIVO,
			'CHEQUE' => $CHEQUE,
			'TARJETA' => $TARJETA,
			'CREDITO' => $CREDITO,
			'BONOS' => $BONOS,
			'PERMUTA' => $PERMUTA,
			'OTRAS_FORMAS' => $OTRAS_FORMAS,
			'SN' => $SN,
			'numero_interno' => $numero_interno,
		);
		
		$this->load->view('facturas/txt_607',$data);
	}
		
	function consultar_url_rnc(){
		$rnc = $this->input->post("rnc");
		//$rnc = '12345';

		// atributos a enviar mediante post, pueden ser cualquier otros
		$post = [
			'rnc' => $rnc,
		];

		$url = URL_consulta_rnc();
		//url de la pagina externa
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		// executar y obtener la repuesta
		$response = curl_exec($ch);

		//recoger errores por si falla
		$error = curl_error($ch);

		// cerrar la conexión o sesiones
		curl_close($ch);

		// puedes hacer lo que sea con la respuesta o el error
		//var_dump($response);
		echo $response;
		echo $error;
	}

	function listado_ventas(){
		$data['Tipos'] = $this->Facturas_model->getTipos();
		$tipo_comprobante = $this->input->post("tipo");
		$fecha_ini = $this->input->post("fecha_ini");
		$fecha_fin = $this->input->post("fecha_fin");
		$id_venta = $this->input->post("id_venta");

		$data['tipo_comprobante'] = $tipo_comprobante;
		$data['fecha_ini'] = $fecha_ini;
		$data['fecha_fin'] = $fecha_fin;
		$data['id_venta'] = $id_venta;

		if ($this->input->post("buscar") == 'buscar') {
			$data['Numeraciones'] = $this->Facturas_model->getFactxTipoxFecha($tipo_comprobante,$fecha_ini,$fecha_fin);
		};
		
		if ($this->input->post("busqueda_id") == 'busqueda_id') {
			$data['Numeraciones'] = $this->Facturas_model->getFactxID_Venta($id_venta);
		};
		
        $data['main_content'] = $this->load->view('facturas/listado_ventas', $data, TRUE);
        $this->load->view('userHome', $data);
	}

	function resumen_ventas(){
		$data['Tipos'] = $this->Facturas_model->getTipos();
		$data['AllNum'] = $this->Facturas_model->getListaNumeracionesAll();
		$NumID = $this->input->post("NumID");
		$tipo_comprobante = $this->input->post("tipo");
		$fecha_ini = $this->input->post("fecha_ini");
		$fecha_fin = $this->input->post("fecha_fin");
		$id_venta = $this->input->post("id_venta");

		$data['tipo_comprobante'] = $tipo_comprobante;
		$data['NumID'] = $NumID;
		$data['fecha_ini'] = $fecha_ini;
		$data['fecha_fin'] = $fecha_fin;
		$data['id_venta'] = $id_venta;

		if ($this->input->post("buscar") == 'buscar') {
			//$data['Numeraciones'] = $this->Facturas_model->getFactxNumxFecha($NumID,$fecha_ini,$fecha_fin);
			$data['Numeraciones'] = $this->Facturas_model->getFactxTipoxFecha($tipo_comprobante,$fecha_ini,$fecha_fin);
		};
		
		// if ($this->input->post("busqueda_id") == 'busqueda_id') {
		// 	$data['Numeraciones'] = $this->Facturas_model->getFactxID_Venta($id_venta);
		// };
		

        $data['main_content'] = $this->load->view('facturas/resumen_ventas', $data, TRUE);
        $this->load->view('userHome', $data);
	}
}