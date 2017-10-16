<?php

/*
 * The MIT License
 *
 * Copyright 2016 Cristian Tala Sánchez < http://cristiantala.cl >.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ctala\transaccion\classes;

/**
 * Description of Transaccion
 *
 * @author ctala
 */
class Transaccion {
    /*
     * Estas son las variables básicas de una transaccion.
     */

    public $ct_order_id;
    //Esta variable es la que usaremos como "idSession" de transbank
    public $ct_token_tienda;
    public $ct_monto;
    public $ct_token_service;
    public $ct_email;
    private $ct_firma;
    //Estas Variables están desde la version 0.2

    public $ct_currency = "CLP";
    public $ct_api_version = "0.2";
    //Esta es la variable con la que firmaremos el mensaje
    private $ct_token_secret;

    /**
     * Este objeto crea una transaccion con la informacion básica.
     * En caso de necesitar agregar más variables se recibe un arreglo "extra"
     * el cual agregara el valor a las variables existentes.
     * @param type $ct_order_id Número de orden.
     * @param type $ct_token_tienda Número único de la orden. No es el id.
     * @param type $ct_monto Monto relacionado a la transacción
     * @param type $ct_token_service Monto relacionado al servicio en Pago Fácil
     * @param type $ct_email Email del cliente que realiza la compra.
     * @param type $extra Otras variables a ingresar
     */
    function __construct($ct_order_id, $ct_token_tienda, $ct_monto, $ct_token_service, $ct_email, $extra = array()) {
        $this->ct_order_id = $ct_order_id;
        $this->ct_token_tienda = $ct_token_tienda;
        $this->ct_monto = $ct_monto;
        $this->ct_token_service = $ct_token_service;
        $this->ct_email = $ct_email;

        foreach ($extra as $key => $value) {
            if (property_exists(get_class(), $key)) {
                $this->{$key} = $value;
            }
        }
    }

    function setCt_token_secret($ct_token_secret) {
        $this->ct_token_secret = $ct_token_secret;
    }

    /*
     * La firma se agrega al arreglo una vez firmado.
     */

    function getArrayResponse() {
        $arreglo = $this->getVarsFromClass();
        $arreglo["ct_firma"] = $this->firmarArreglo($arreglo);
        return $arreglo;
    }

    function getArray() {
        $resultado = [
            "ct_order_id" => $this->ct_order_id,
            "ct_token_tienda" => $this->ct_token_tienda,
            "ct_monto" => $this->ct_monto,
            "ct_token_service" => $this->ct_token_service,
            "ct_email" => $this->ct_email
        ];

        ksort($resultado);
        return $resultado;
    }

    function firmarArreglo($arreglo) {


        //Ordeno Arreglo
        ksort($arreglo);
        //Concateno Arreglo
        $mensaje = $this->concatenarArreglo($arreglo);

        //Firmo Mensaje
        $mensajeFirmado = $this->firmarMensaje($mensaje, $this->ct_token_secret);

        //Guardo y retorno el mensaje firmado
        $this->ct_firma = $mensajeFirmado;
        return $mensajeFirmado;
    }

    /**
     * Esta funcion retorna true si las firmas corresponden
     * Si no corresponden revisa de manera legacy.
     * @param type $arreglo Arreglo con los valores de CTs, puede ser $_POST
     * @param type $firma Con la cual se comparara
     * @return boolean
     */
    function firmarYComparar($arreglo, $firma) {
        $arregloFiltrado = $this->getCTs($arreglo);
        $arregloFirmado = $this->firmarArreglo($arregloFiltrado);
        $comparar = $this->compararFirmas($firma);

        if ($comparar == true) {
            return true;
        } else {
            $legacyFirmado = $this->firmarArreglo($this->getArray());
            if ($this->compararFirmas($firma)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Retorna arreglo basado en todas las variables que parten con CT
     * excepto la firma
     * @param type $array
     * @return type
     */
    function getCTs($array) {
        $resultado = array();
        //Se obtienen solo los datos que inician en ct_
        foreach ($array as $key => $value) {
            if (!substr_compare("ct_", $key, 0, 3)) {
                if ($key != "ct_firma") {
                    $resultado[$key] = $value;
                }
            }
        }
        return $resultado;
    }

    /**
     * Retorna las variables publicas del objeto.
     * @return type
     */
    function getVarsFromClass() {

        $variables = get_object_vars($this);
        unset($variables["ct_token_secret"]);
        unset($variables["ct_firma"]);

        return $variables;
    }

    /**
     * 
     * @param type $firmaAComparar Firma que debe de corresponder.
     */
    function compararFirmas($firmaAComparar) {
        if ($this->ct_firma == $firmaAComparar) {
            return true;
        } else {
            return false;
        }
    }

    function firmarMensaje($mensaje, $claveCifrado) {
        $mensajeFirmado = hash_hmac('sha256', $mensaje, $claveCifrado);
        return $mensajeFirmado;
    }

    public function concatenarArreglo($arreglo) {
        $resultado = "";

        foreach ($arreglo as $field => $value) {
            $resultado .= $field . $value;
        }

        return $resultado;
    }

    function setCt_source($ct_source) {
        $this->ct_source = $ct_source;
    }

    function setCt_currency($ct_currency) {
        $this->ct_currency = $ct_currency;
    }

    function setCt_order_id($ct_order_id) {
        $this->ct_order_id = $ct_order_id;
    }

    function setCt_token_tienda($ct_token_tienda) {
        $this->ct_token_tienda = $ct_token_tienda;
    }

    function setCt_monto($ct_monto) {
        $this->ct_monto = $ct_monto;
    }

    function setCt_token_service($ct_token_service) {
        $this->ct_token_service = $ct_token_service;
    }

    function setCt_email($ct_email) {
        $this->ct_email = $ct_email;
    }

}
