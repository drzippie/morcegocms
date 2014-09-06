<?php

include_once( dirname(__FILE__) . '/morcegocms_functions_fichero.php'  );
include_once( dirname(__FILE__) . '/morcegocms_cls_boton.php'  );

class morcegocms_functions_boton {
  function action( $cadena ) {
      $aCadena = explode(":", $cadena);
      $idboton =& $aCadena[1] ;
      $texto = implode( ':', array_slice( $aCadena, 2, count( $aCadena ) - 2 ) );
      $file_boton = 'cache.boton.' . md5( $idboton . $texto ) . '.png' ;
          
      $resultado = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ?
          "<img src=\"" . $GLOBALS['configCMS']->get_var('rutaweb') .  "botones/" .  md5( $idboton . $texto ) . ".png\" border=\"0\" alt=\"{$texto}\"/>" : 
          "<img src=\"" . $GLOBALS['configCMS']->get_var('rutaweb') .  "lar/{$file_boton}\" border=\"0\" alt=\"{$texto}\"/>" 
          ; 
      if ( !file_exists( dirname( __FILE__ ) . '/../../lar/' . $file_boton )) {
          $comando_sql = "select * from {$GLOBALS['DB_prefijo']}botones where idboton = \"{$idboton}\"";
          $recordset = $GLOBALS['DB']->execute( $comando_sql );
          if ( $recordset->EOF ) {
              $value = '<!-- boton ' . $idboton . ' no encontraddo -->';
          } else {
              $aBoton = array (
                  'nombre'             =>  $idboton ,
                  'grafico'            => 1,
                  'cache'              => 1,
                  'ttf_size'           => $recordset->fields['ttfsize'],
                  'ancho'              => $recordset->fields['ancho'],
                  'color_texto'        => $recordset->fields['colortexto'] ,
                  'color_transparente' => $recordset->fields['colortransparente'],
                  'color_fondo'        => $recordset->fields['colorfondo'],
                  'correccion_x'       => $recordset->fields['correccionx'],
                  'correccion_y'       => $recordset->fields['correcciony']
              ) ;

              $aBoton['izquierda'] = morcegocms_functions_fichero::path( $recordset->fields['idimagenizquierda']);
              $aBoton['centro'] = morcegocms_functions_fichero::path( $recordset->fields['idimagencentro']);
              $aBoton['derecha'] = morcegocms_functions_fichero::path( $recordset->fields['idimagenderecha']);
              $aBoton['ttf'] = morcegocms_functions_fichero::path( $recordset->fields['idttf']);
              $boton = new cls_boton($aBoton, $texto );
              $resultado = $boton->render_boton();
              unset( $boton);
          }

      }  
      return $resultado ;
  
  }

}



?>