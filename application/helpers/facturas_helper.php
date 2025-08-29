<?php

function rellenar_num($n){
    
    switch (true) {
        case ($n >= 1 && $n <= 9) : return '0000000' . $n; break;
        case ($n >= 10 && $n < 100) : return '000000' . $n; break;
        case ($n >= 100 && $n < 1000) : return '00000' . $n; break;
        case ($n >= 1000 && $n < 10000) : return '0000' . $n; break;
        case ($n >= 10000 && $n < 100000) : return '000' . $n; break;
        case ($n >= 100000 && $n < 1000000) : return '00' . $n; break;
        case ($n >= 1000000 && $n < 10000000) : return '0' . $n; break;
        case ($n >= 10000000) : return $n;
    }

}

function TipoNumeracion()
{
    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    return $CI->Facturas_model->getTipos();
}

function Ult_Compra()
{
    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    return $CI->Facturas_model->getUlt_Compra();
}

function TipoPago()
{
    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    return $CI->Facturas_model->getTipoPago();
}

function TipoCyG()
{
    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    return $CI->Facturas_model->getTipoCyG();
}

function TipoIngresos()
{
    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    return $CI->Facturas_model->getTipoIngresos();
}
    
function NumeracionesActivas()
{
    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    return $CI->Facturas_model->getListaNumeracionesActBySuc();
}

function verificar_compra($id){
    
    $CI =& get_instance();
    $CI->load->model('Facturas_model');

    $factura_compra = $CI->Facturas_model->getCompraxFactura($id);

    return $factura_compra;
}

function facturar_compra($id,$factura_info){

    $CI =& get_instance();
    $CI->load->model('Facturas_model');

    $proveedor = $CI->Facturas_model->getProveedor($factura_info['id_proveedor']);
    $factura_num = $CI->Facturas_model->getTipo($factura_info['numeracion_tipo']);
    
    $compra_id = $id;
    $numeracion_tipo = $factura_info['numeracion_tipo'];
    $ncf = $factura_num->prefijo . rellenar_num($factura_info['ncf']);
    $fecha_venc = date('Y-m-d',strtotime($factura_info['fecha_venc']));
    $nombre = $proveedor->name;
    $rnc = $proveedor->rnc;
    $fecha_comprobante = date($factura_info['fecha_comprobante']);
    $tipo_ident = $proveedor->tipo_ident;
    $tipo_cyg = $factura_info['tipo_cyg'];
    $tipo_pago = $factura_info['tipo_pago'];
    
    if (verificar_compra($id) == NULL){

        $data = array(
            'numeracion_tipo' => $numeracion_tipo,
            'compra_id' => $compra_id,
            'ncf' => $ncf,
            'nombre' => $nombre,
            'rnc' => $rnc,
            'tipo_ident' => $tipo_ident,
            'fecha_venc' => $fecha_venc,
            'fecha_comprobante' => $fecha_comprobante,
            'tipo_pago' => $tipo_pago,
            'tipo_cyg' => $tipo_cyg,
            'fecha_pago' => NULL,
            'estado' => '1',
        );

        if ($CI->Facturas_model->save_compra($data)){
            return 'Insertado correctamente';
        } else {
            return 'Error al insertar en la base de datos';
        }; 
    } else {
        $factura_existente = verificar_compra($id);
        
        $data = array(
            'numeracion_tipo' => $numeracion_tipo,
            'compra_id' => $compra_id,
            'ncf' => $factura_info['ncf'],
            'nombre' => $nombre,
            'rnc' => $rnc,
            'tipo_ident' => $tipo_ident,
            'fecha_venc' => $fecha_venc,
            'fecha_comprobante' => $fecha_comprobante,
            'tipo_pago' => $tipo_pago,
            'tipo_cyg' => $tipo_cyg,
            'fecha_pago' => NULL,
            'estado' => '1',
        );

        if ($CI->Facturas_model->update_compra($factura_existente->id,$data)){
            return 'Modificado correctamente';
        } else {
            return 'Error al editar en la base de datos';
        }; 
    };
}

function datos_factura($id){
    
    $CI =& get_instance();
    $CI->load->model('Facturas_model');

    $factura_venta = $CI->Facturas_model->getVentaxFactura($id);

    return $factura_venta;
}

function nueva_factura_venta($id,$numeracion_id){

    $CI =& get_instance();
    $CI->load->model('Facturas_model');
    
    if (NumeracionesActivas() != NULL) :
        if ($numeracion_id != 0){
            $Sale = $CI->Facturas_model->getSaleInfo($id);
            $numeracion = $CI->Facturas_model->getNumeracion($numeracion_id);
            $Factura_existente = $CI->Facturas_model->getVentaxFactura($id);

            if ($numeracion->estado == 1){
                if ($Factura_existente == NULL){
                    $data  = array(
                        'id' => $id,
                        'cliente_id' => $Sale->customer_id,
                        'Nombre_id' => $Sale->customer_name,
                        'RNC_id' => $Sale->customer_rcn,
                        'numeracion' => $numeracion_id,
                        'tipo_ingreso' => 2,
                        'fecha' => $Sale->sale_date,
                        'tipo_doc' => $Sale->tipo_ident,
                    );

                    if($CI->Facturas_model->store_factura_venta($data)){
                        return TRUE;
                    } else {
                        return FALSE;
                    };
                } else {
                    return FALSE;
                };
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        };
    else :
        return FALSE;
    endif ;
}

function verificar_prefijo($prefijo){
    
    if ($prefijo == 'B01'){
        $resultado = 1;
    } elseif ($prefijo == 'B02'){
        $resultado = 2;
    } elseif ($prefijo == 'B03'){
        $resultado = 3;
    } elseif ($prefijo == 'B04'){
        $resultado = 4;
    }  elseif ($prefijo == 'B11'){
        $resultado = 5;
    }  elseif ($prefijo == 'B12'){
        $resultado = 6;
    }  elseif ($prefijo == 'B13'){
        $resultado = 7;
    }  elseif ($prefijo == 'B14'){
        $resultado = 8;
    }  elseif ($prefijo == 'B15'){
        $resultado = 9;
    }   elseif ($prefijo == 'B16'){
        $resultado = 10;
    }   elseif ($prefijo == 'B17'){
        $resultado = 11;
    }   else {
        $resultado = 12;
    };

    return $resultado;
}

if ( ! function_exists('consulta_rnc_url')){
    function consulta_rnc_url(){
        return base_url("facturas/consultar_url_rnc");
        //return 'http://localhost/consulta_rnc/consultas/db_rnc_dgii/';
    }
}

if ( ! function_exists('URL_consulta_rnc')){
    function URL_consulta_rnc(){
        //return base_url("facturas/db_rnc_dgii");
        return 'https://consultas-rnc.visuallytecnologic.com/consultas/db_rnc_dgii/';
    }
}


    

