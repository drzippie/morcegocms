<?php

class morcegocms_functions_server {
  function action( $cadena ) {
    $aCadena = explode(":", $cadena);
    return  ( isset( $_SERVER[ $aCadena[1] ])) ?  $_SERVER[ $aCadena[1] ] : '';
  }
}

?>