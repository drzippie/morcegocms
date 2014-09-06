<?php

class morcegocms_functions_if  {
  function action( $cadena ) {
        $cadena = stripslashes( $cadena );
        $aCadena = explode(":", $cadena);
        $Formula = $aCadena[1];
        $Comparacion = $aCadena[2];
        $ValorComparacion = (empty($aCadena[3])) ? 0 : $aCadena[3];
        $SiVerdadero = $aCadena[4];
        $SiFalso     = $aCadena[5];
        $ValorFormula  =   morcegocms_utils::matheval( $Formula );
        switch ($Comparacion) {
            case '=':
                if ( $ValorFormula == $ValorComparacion ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;
            case '>':
                if ( $ValorFormula > $ValorComparacion ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                
            case '<':
                if ( $ValorFormula < $ValorComparacion ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;              
            case '<>':
                if ( $ValorFormula != $ValorComparacion ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                        
            case '>=':
                if ( $ValorFormula >= $ValorComparacion ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                        
            case '<=':
                if ( $ValorFormula <= $ValorComparacion ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                        
                
            default:
                $resultado = "<!-- Formula incorrecta -->";
        }
        return stripslashes( $resultado ) ;
    }

}
?>