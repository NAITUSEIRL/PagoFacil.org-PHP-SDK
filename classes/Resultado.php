<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ctala\transaccion\classes;

/**
 * Description of Resultado
 *
 * @author ctala
 */
class Resultado {

    public function pedidoRecibidoResponse(Response $response) {
        $fields = array(
            "Orden de compra Mall" => $response->ct_order_id_mall,
            "Código de autorización" => $response->ct_authorization_code,
            "Tipo de Pago" => $response->ct_payment_type_code,
            "Monto" => $response->ct_monto,
            "Final Tarjeta" => $response->ct_shares_number,
            "Cuotas" => $response->ct_shares_number,
            "Fecha Contable" => $response->ct_accounting_date,
            "Fecha Transacción" => $response->ct_transaction_date,
        );
        include '../templates/orden_recibida.php';
    }
    
    public function pedidoFallido($order_id,$order_id_mall) {
        include '../templates/orden_fallida.php';
    }

}
