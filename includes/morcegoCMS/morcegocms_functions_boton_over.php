<?php
include_once( dirname(__FILE__) . '/morcegocms_functions_fichero.php'  );
include_once( dirname(__FILE__) . '/morcegocms_cls_boton.php'  );

class morcegocms_functions_boton_over {

  function action( $cadena ) {
      $aCadena = explode(":", $cadena);
      $idboton1 =& $aCadena[1] ;
      $idboton2 =& $aCadena[2] ;
      $texto = implode( ':', array_slice( $aCadena, 3, count( $aCadena ) - 3 ) );
      $file_boton1 = 'cache.boton.' . md5( $idboton1 . $texto ) . '.png' ;
      $file_boton2 = 'cache.boton.' . md5( $idboton2 . $texto ) . '.png' ;
     
      $resultado_1 = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ?
          "src=\"" . $GLOBALS['configCMS']->get_var('rutaweb') .  "botones/" .  md5( $idboton1 . $texto ) . ".png\"" : 
          "src=\"" . $GLOBALS['configCMS']->get_var('rutaweb') . "lar/{$file_boton1}\""  ;
      $resultado_2 = ( $GLOBALS['configCMS']->get_var('mod_rewrite')  == 'true') ?
          "src=\"./botones/" .  md5( $idboton2 . $texto ) . ".png\"" : 
          "src=\"lar/{$file_boton2}\""  ;
/*                
      $resultado_1 = "src=\"lar/{$file_boton1}\""; 
      $resultado_2 = "src=\"lar/{$file_boton2}\""; 
*/                
      
      if ( !file_exists( dirname( __FILE__ ) . '/../../lar/' . $file_boton1 )) {
          $comando_sql = "select * from {$GLOBALS['DB_prefijo']}botones where idboton = \"{$idboton1}\"";
          $recordset = $GLOBALS['DB']->execute( $comando_sql );
          if ( $recordset->EOF ) {
              $value = '<!-- boton ' . $idboton . ' no encontraddo -->';
          } else {
              $aBoton = array (
                  'nombre'             =>  $idboton1 ,
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
              $resultado_1 = $boton->render_boton();
              unset( $boton);
          }

      }
      if ( !file_exists( dirname( __FILE__ ) . '/../../lar/' . $file_boton2 )) {
          $comando_sql = "select * from {$GLOBALS['DB_prefijo']}botones where idboton = \"{$idboton2}\"";
          $recordset = $GLOBALS['DB']->execute( $comando_sql );
          if ( $recordset->EOF ) {
              $value = '<!-- boton ' . $idboton . ' no encontraddo -->';
          } else {
              $aBoton = array (
                  'nombre'             =>  $idboton2 ,
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
              $resultado_2 = $boton->render_boton();
              unset( $boton);
          }
      }
      preg_match ( "|src=\"(.*)\"|iUs", $resultado_1 ,$miresultado1 );
      preg_match ( "|src=\"(.*)\"|iUs", $resultado_2 ,$miresultado2 );
      $urlboton1 =& $miresultado1[1];
      $urlboton2 =& $miresultado2[1];
      $idboton = md5( $urlboton1 . $urlboton2 );
      $resultado = "<img src=\"{$urlboton1}\" name=\"{$idboton}\" ".    
          "onmouseover=\"this.src='{$urlboton2}';\" ".
          "onmouseout=\"this.src='{$urlboton1}';\" ".
          " border=\"0\" alt=\"{$texto}\"/>";
      return $resultado ;
  
  }
}


?>