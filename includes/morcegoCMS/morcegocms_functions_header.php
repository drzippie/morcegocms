<?php

class morcegocms_functions_header  {

  function action ( $cadena ) {
    $aCadena = explode(":", $cadena);
    $header = implode(':', array_slice( $aCadena, 1 ));
    header( $header );
    return '';
  }

}

?>