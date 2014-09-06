<?php
class morcegocms_functions_formula  {
/**
    *
    */ 
    function action( $cadena ) {
        $cadena = stripslashes( $cadena );
        $aCadena = explode(":", $cadena);
        $Formula = $aCadena[1];
        return morcegocms_utils::matheval( $Formula );
    }         

}
?>