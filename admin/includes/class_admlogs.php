<?php 

class  adm_logs  extends adm_class{
    function adm_logs(  $html  ) {
        $this->oHtml  = $html ;
        global $DB, $DB_prefijo, $aArgumentos ;    
            if (!isset( $aArgumentos[1] )) {
                $aArgumentos[1] = '' ;
            }
            switch ($aArgumentos[1]){
                case 'list':
                    $this->show_log();
                    break;
                case 'clean':
                    $this->clean_log();
                    $this->show_log();
                    break;
                default:
                    $this->show_log();
            }
    }
    function clean_log()  {
        global $DB, $DB_prefijo; 
        $comando_sql = "delete from {$DB_prefijo}log";
        $resultado = $DB->execute( $comando_sql);
        morcegocms_utils::log(  "INFO;LOGS;El registro de Logs ha sido borrado" );
    }
    
    function show_log() {
        global $DB, $DB_prefijo, $aArgumentos;
        $inicio = (!isset($aArgumentos[3])) ? 0 : $aArgumentos[3];
        $registros = (!isset($aArgumentos[2]) || $aArgumentos[2] < 20 ) ? 20  : $aArgumentos[2];
        
        // traemos los usuarios 
        
        $comando_sql = "select iduser, username from {$DB_prefijo}users";
        $resultado = $DB->execute( $comando_sql);
        $array_users = array( '0' => 'Anonymous' );
        while (!$resultado->EOF) {
            $array_users["{$resultado->fields['iduser']}"] = $resultado->fields['username'] ;
            $resultado->MoveNext();
        }
        $AttrTable =  array(  
          'cellpadding' => '0', 
          'cellspacing' => '0', 
          'class' => 'ruler wide');
        $HTML = new HtmlContainer( ) ;

        $tabla =& $HTML->add( 'TABLE',$AttrTable );
        /*
        $tr =& $tablaP->add( 'TR' );
        $tr->add( 'TD', array('class'=>'titulologs'), 'Log de MorcegoCMS' );
        */
        /*
        $tr =& $tablaP->add( 'TR' );
        $td =& $tr->add( 'TD');
        $tabla =& $td->add( 'TABLE',$AttrTable );
        */
        $tr =& $tabla->add( 'TR' );
        $tr->add('TH', '', 'FECHA');
        $tr->add('TH', '', 'USUARIO');
        $tr->add('TH', '', 'IP');
        $tr->add('TH', '', 'IDPAGINA'); 
        $tr->add('TH', '', 'TEXTO');
        $comando_sql = "select count(*) as total from {$DB_prefijo}log";
        $resultado = $DB->execute( $comando_sql);
        $total_registros = $resultado->fields['total'];
        $paginas = ceil( $total_registros / $registros ) ;
        if ( $inicio > ($paginas * $registros)) { $inicio = $paginas ; }
        $str_out = '';
        $comando_sql = "select *  from {$DB_prefijo}log order by date asc";
        $resultado = $DB->SelectLimit( $comando_sql , $registros, $inicio * $registros  );
        while (!$resultado->EOF) {
            $tr =& $tabla->add( 'TR' );
            $tr->add('TD','', $resultado->fields['date']);
			$userName = (isset($array_users[$resultado->fields['iduser']])) ? $array_users[$resultado->fields['iduser']] : 'Unknown';
            $tr->add('TD', '', $userName );
            $tr->add('TD', '', $resultado->fields['ip']) ;
            $tr->add('TD', '', $resultado->fields['idpagina'] . '&nbsp;' );
            
            $aError = explode(';',$resultado->fields['content'])  ;
            $imagen = new htmlcontainer();
            switch ($aError[0]) {
              case "INFO" :
                $imagen->add('img', array( 
                'width' => '12',
                'height' => '12',
                'src' => 'images/infosmall.png'
                
                ));
                break;
              case "ERROR":
                $imagen->add('img', array( 
                'width' => '12',
                'height' => '12',
                'src' => 'images/errorsmall.png'));
                break;
            }
            if (count( $aError) < 3 ) {
              // formato antiguo
              $elemento = '';
            } else {
              $elemento = $aError[1];
              $resultado->fields['content'] = $parametros = implode(';', array_slice( $aError, 2));
            
            }
	    
         
                $td =& $tr->add('TD');
                $td->add_object( $imagen );
                $td->add( 'strong', '', ' '.  $elemento ) ;
		if ( $elemento == 'MorcegoCMS' ) {
			$error = unserialize( $resultado->fields['content'] );
			$msgError = $error['msg'] ;
			$td->add_text( ' ' . $msgError  ) ;;
			$td->add( 'a', array(
				'href' => '#',
				'onclick' => "jsFunctions.popUp('./popups/error_log.php?id=" . $resultado->fields['idlog'] . "');"),
				'+ info'
				);
		}  else { 
			$td->add_text( ' ' . $resultado->fields['content']  );
                
		}
                  
                  
                
            
            $resultado->MoveNext();
        }
        
        $tr =& $tabla->add( 'TR' );
        $td =& $tr->add( 'th', array( 
          'colspan' => '5',
                'style' => 'text-align: left;padding-left: 20px; padding-right: 20px; vertical-align: middle;'), '' );
        $div =& $td->add( 'DIV', array( 'style' => 'float: left;'), 'Páginas');
        
        for ( $i = 0; $i < $paginas ; $i++) {
            
            $txtPagina = $i + 1;
            if ( $i == $inicio ) {
                $div->add ( '', ''," $txtPagina " );
            } else {
                $div->add ( 'A', array( 'href' => "{$_SERVER['PHP_SELF']}?$aArgumentos[0]/$aArgumentos[1]/$registros/$i"),
                    "[$txtPagina]" );
            }
        }
        $td->add( 'DIV', array( 'style' => 'float: right; vertical-align: middle'),
		'',
                CustomHTML::botonAdmin("Borrar Log" , 'Vaciar el log', '', "confirmar('¿ Desear borrar el log completamente ? Esta operación no se podrá deshacer.', '{$_SERVER['PHP_SELF']}?logs/clean' );"));
        
        // $page =& $this->oHtml->Elements[$this->oHtml->get_first_element_id('html')] ;
        // $body =& $page->Elements[$page->get_first_element_id('body')] ;
        
         $this->render( $HTML, 'Eventos del Log. Página ' . ($inicio+1)  . ' de ' . $paginas  , '#')  ; 

                              
    }

}



?>