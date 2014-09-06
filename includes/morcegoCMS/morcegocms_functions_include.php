<?php
class morcegocms_functions_include {
  var $template;
  function morcegocms_functions_include( &$template ) {
    $this->template =& $template ;
  
  }
  
  function action( $cadena )   {
     $aCadena = explode(":", $cadena);
     return  $this->template -> dbtemplate -> read_template($aCadena[1]);
                break;
  }
}
?>