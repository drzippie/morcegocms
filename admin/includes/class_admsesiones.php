<?php 

class  adm_sesiones  extends adm_class  {
    function adm_sesiones(  $html  ) {
        $this->oHtml  = $html ;
        global $DB, $DB_prefijo, $aArgumentos, $configCMS ;    
        // siempre borraremos las sesiones antiguas
            $ahora = $DB->DBTimeStamp( time() - _MORCEGO_MAX_SESSION_TIME );
            $recordset = $DB->execute( 'delete from ' .
                $configCMS -> get_var('dbprefijo') . 'sessions ' .
                " where iddate < {$ahora} " );
              
        
        
            if (!isset( $aArgumentos[1] )) {
                $aArgumentos[1] = '' ;
            }
            switch ($aArgumentos[1]){
                case 'list':
                    $this->show_sesiones();
                    break;
                default:
                    $this->show_sesiones();
            }
    }
    function show_sesiones() {
        /*
        $registros = (!isset($aArgumentos[2]) || $aArgumentos[2] < 20 ) ? 20  : $aArgumentos[2];
        $inicio = (!isset($aArgumentos[3])) ? 0 : $aArgumentos[3];
        */

        $AttrTable =  array( 
          'width' => '700', 
          'cellpadding' => '2', 
          'cellspacing' => '0',
          'class' => 'ruler');

        $HTML = new HtmlContainer( ) ;
        
        
        $tabla =& $HTML->add( 'TABLE',$AttrTable );
        $tr =& $tabla->add( 'TR' );
        $tr->add('th', '', 'Usuario');
        $tr->add('th', '', 'Página Actual');
        $tr->add('th', '', 'Fecha');
        $comando_sql = "select * from {$GLOBALS['DB_prefijo']}sessions";
        $resultado = $GLOBALS['DB']->execute( $comando_sql);
        // print_r( $resultado );
        while (!$resultado->EOF ) {
            $tr =& $tabla->add( 'TR' );
            $elementos = array();
            if ( substr($resultado->fields['content'], 0, 9) == 'user_name' ){
                $aElementos = explode( '";' ,  $resultado->fields['content']);
                for( $i     = 0; $i < (count( $aElementos) -1  ) ; $i ++ ) {
                    $aElementos2 = explode('|', $aElementos[$i] );
                    if ( isset( $aElementos2[1] )) {
                        $elementos[ $aElementos2[0]] = @unserialize( $aElementos2[1] . '";');
                    }
                } 
            }
            $tr->add('TD', '', isset( $elementos['user_name'] ) ? $elementos['user_name'] : 'Otra sesión');
            $tr->add('TD', '', isset( $elementos['pagina_actual'] ) ? $elementos['pagina_actual'] : 'Desconocida');
            $tr->add('TD', '', $resultado->fields['iddate']);
            
            $resultado->MoveNext();
        }

        $this->render( $HTML, 'Administración de sesiones (Usuarios en línea)');
    }

}



?>