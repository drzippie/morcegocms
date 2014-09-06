<?php
class morcegocms_functions_menu extends morcegocms_common {
  var $pagina ;
  function morcegocms_functions_menu( ) {
    $this->pagina &= $GLOBALS['pagina'] ;
  
  }
  function action( $cadena ) {
    $resultado = '';
    
    $aCadena = explode( ':', $cadena );
    $idpagina =  ( isset($aCadena[1]) && !empty($aCadena[1] )) 
      ? $aCadena[1] 
      : $GLOBALS['pagina']->Campos['idpagina'];

    $idtemplate =  ( isset($aCadena[2]) && !empty($aCadena[2] )) 
      ? $aCadena[2] 
     :  '';
       
    if (empty( $idtemplate )) {
      return '<!-- ERROR: Plantilla de menú no especificada -->';
    }

    $ObjectID = 'menu.' . md5( 
      $idpagina . 
      implode( ':', $aCadena ) . 
      $GLOBALS['statsCMS']->User->idgroup) ; 

    $Serial = (_MORCEGO_CACHE_OBJECTS ) 
      ? $this->get_idobject( $ObjectID) 
      : false;
    
    if(  $Serial != false ) {
      $resultado =  $Serial ;
      unset(  $Serial );
    } else {
      /*
      En la versión 0.9.8.3 se le han añadido 2 parametros (opcionales)
          - maximo numero de elementos a mostrar 
          - pagina (empieza en 1)
      así:
          {menu:idpagina:template:10:1} nos mostrará las primeras 10 páginas hijas
          {menu:idpagina:template:25:2} nos mostrará las segundas 25 páginas hijas
      */
      $max_elements =  ( isset($aCadena[3]) && !empty($aCadena[3] )) ? $aCadena[3] : -1 ;
      $page_number =   ( isset($aCadena[4]) && !empty($aCadena[4] )) ? $aCadena[4] : -1 ;
      /*
        0.9.13;
      */
      
      /**
      * establecemos el filtro a realizar 
      * puede ser del tipo propiedad=valor|variable.nombre=valor
      *
      */
      $strFilter = ( isset($aCadena[5]) && !empty($aCadena[5] )) ? $aCadena[5] : '' ;
      $sqlFilter = '';
      if ( !empty( $strFilter )) {
        $aTmp = explode( '=', $strFilter ) ;
        if ( count( $aTmp ) == 2 ) {
          $sqlFilter = ' and variables like "%' .  $aTmp[0] . '=' . $aTmp[1] . '%" ';
        } else {
			$aTmp = explode( '<>', $strFilter ) ;
        	if ( count( $aTmp ) == 2 ) 
			{
				$sqlFilter = ' and variables not like "%' .  $aTmp[0] . '=' . $aTmp[1] . '%" ';
			}
		}
      } 
      
   
      $pagina_menu = new pagina ( $idpagina );
      
	
      if ( $pagina_menu->ok == 1 ) {
          $oencabezado =  new Template_morcegoCMS( 
            $pagina_menu,$GLOBALS['configCMS']->get_var('cachetimming'), 
            $idtemplate, 
	'content_header'  );
	
	
          $txtEncabezado = $oencabezado->parsear() ;
		
	 $resultado = '';
          unset( $oencabezado );
          $comando_sql = "select idpagina from {$GLOBALS['DB_prefijo']}paginas where " . 
            "uidparent = {$pagina_menu->Campos['uid']} ". $sqlFilter . 
            " and tipo = 0 and activa = 1 and idgroup <= " . 
              $GLOBALS['statsCMS']->User->idgroup  . 
              " and uid != 0 " .
              'and fecha <= ' . $GLOBALS['DB']->DBTimeStamp($GLOBALS['configCMS']->hoy) . ' ' . 
              " order by orden ";
          
          if ( $max_elements > 0 && $page_number > 0 ) {
              $recordset = $GLOBALS['DB']->SelectLimit( 
              $comando_sql, $max_elements, ($page_number - 1) * $max_elements );
	      
	      
          } else  {
	     
            $recordset = $GLOBALS['DB']->execute( $comando_sql );
	    
          }
	 
          while (!$recordset->EOF) {
            $detalle_pagina = new pagina ( $recordset->fields['idpagina'], true, false, true );
            $odetalle = new Template_morcegoCMS( 
              $detalle_pagina,
              $GLOBALS['configCMS']->get_var('cachetimming'), 
              $idtemplate, 
              'content');
            $resultado .= $odetalle->parsear() ;
            unset( $odetalle);
            unset( $detalle_pagina);
            $recordset->MoveNext();
          }
	  
	  
          $opie =  new Template_morcegoCMS( 
            $pagina_menu,$GLOBALS['configCMS']->get_var('cachetimming'), 
            $idtemplate, 
            'content_footer');
          $resultado = ( !empty( $resultado )) 
            ? $txtEncabezado . $resultado . $opie->parsear() 
            : '';
          unset( $pagina_menu );
          unset( $opie );
          $this->save_unserialized( $ObjectID, $resultado);
      } else  {     
        morcegocms_utils::log( 'ERROR;MENU;No existe el menu: ' . $idtemplate );
        $resultado = '<!-- Menu not found -->';
      }   
    }  
  
    return $resultado ;
  }



}

?>