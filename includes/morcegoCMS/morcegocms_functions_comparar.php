<?php
class morcegocms_functions_comparar {
  function action( $cadena ) {
        $cadena = stripslashes( $cadena );
        $aCadena = explode(":", $cadena);
        $ValorComparacion1 = $aCadena[1];
        $Comparacion = $aCadena[2];
        $ValorComparacion2 = $aCadena[3];
        $SiVerdadero = $aCadena[4];
        $SiFalso     = $aCadena[5];
        switch ($Comparacion) {
            case '=':
                if ( $ValorComparacion1 == $ValorComparacion2 ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;
            case '>':
                if ( $ValorComparacion1 > $ValorComparacion2 ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                
            case '<':
                if ( $ValorComparacion1 < $ValorComparacion2 ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;              
            case '<>':
                if ( $ValorComparacion1 <> $ValorComparacion2 ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                        
            case '>=':
                if ( $ValorComparacion1 >= $ValorComparacion2 ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                        
            case '<=':
                if ( $ValorComparacion1 <= $ValorComparacion2 ) {
                    $resultado = $SiVerdadero ;
                } else {
                    $resultado = $SiFalso ;
                }
                break;                        
                
            default:
                $resultado = "<!-- comparación incorrecta -->";
        }
        
        // morcegocms_utils::log('DEBUG;comparar;' . $cadena );

        return stripslashes($resultado) ;
    }
}
?>