<?php

class adm_utils  extends adm_class{

    function adm_utils( $html  ) {
        $this->parameters =& $GLOBALS['aArgumentos'] ;
        $this->oHtml = $html;

        if (!isset( $this->parameters[1] )) {
            $this->parameters[1] = '' ;
        }
        switch ( $this->parameters[1] ) {
            
            case 'phpinfo':
                $this->phpinfo();
                break;
            case 'dbinfo' :
                $this->dbinfo();
                break;
            case 'reordenar' :
                $this->reordenar();
                break;
            // temporal!
            case 'archivos_no_utilizados' :
                $this->archivos_no_utilizados() ;
                break;
                
            case 'morcegoCMS' :
                $this->morcegoCMS();
                break;
            default:
                echo "";
        }
    }
    function phpinfo( ) {
	ob_start();
        phpinfo();
	$out = ob_get_clean();
	ob_end_flush() ;
	$HTML = new HTMLContainer();
	$HTML->add_text( $out ) ;
	$this->render( $HTML, 'PHP Info ', '');
    }
    /** 
    * Muestra información sobre morcegoCMS
    *
    */
    function morcegoCMS() {
        $HTML = new HTMLContainer();
        $ul =& $HTML->add('ul');
        $ul->add( 'li', '',  'MorcegoCMS ' . _MORCEGO_VERSION . ' - '  .  _MORCEGO_COPYRIGHT);
       

        $ul->add( 'li', '', 'Version de Administración: ' ._ADM_VERSION );
        
        $ul->add( 'li', '', 'ADODB: ' . $GLOBALS['ADODB_vers'] );
        
        
        $this->render( $HTML, 'Acerca de MorcegoCMS', '');

        
    
    
    }
    
    
    function dbinfo() {
        global $DB;
       
        
        $HTML = new HTMLContainer();
        
        
        $comando_sql = "show table status like '{$GLOBALS['DB_prefijo']}%'";
        $recordset = $GLOBALS['DB']->execute( $comando_sql );
        
        $table =& $HTML->add('table', array( 
          'cellspacing' => '0', 
          'cellpadding' => '2',
          'class' => 'ruler'));
        
        $tr =& $table->add('tr' );
        $tr->add( 'th', array('rowspan' => '2'), 'Tabla');
        $tr->add( 'th', array('rowspan' => '2'), 'Tipo');
        $tr->add( 'th', array('rowspan' => '2'), 'Filas');
        $tr->add( 'th', array('colspan' => '2'), 'Tamaño');
        
        
        $tr =& $table->add('tr' );
        $tr->add( 'th','', 'Datos');
        $tr->add( 'th', '', 'Índices');
        
        
        $data_length = 0;
        $index_length = 0 ;
        while (!$recordset->EOF) {
        
          $tr =& $table->add('tr' );
            $tr->add( 'td', '', $recordset->fields['Name']);
            
            $tr->add( 'td','',$recordset->fields['Type']);
            
            
            $tr->add( 'td',array('class' => 'derecha' ), 
              number_format( $recordset->fields['Rows'], 0, ',', '.'));
            $tr->add( 'td',array('class' => 'derecha'), 
              number_format ( $recordset->fields['Data_length'], 0, ',', '.' )  .
              ' bytes');
            $tr->add( 'td',array('class' => 'derecha'), 
              number_format ( $recordset->fields['Index_length'], 0, ',', '.' ) . ' bytes');
            
            $data_length += $recordset->fields['Data_length'];
            $index_length += $recordset->fields['Index_length'];
            $recordset->MoveNext();
        }
        $tr =& $table->add('tr' );
        $tr->add( 'th', array( 'colspan' => '3') , '&nbsp;');
        $tr->add( 'th',array( 'class' => 'derecha') , number_format ( $data_length, 0, ',', '.' ) . ' bytes');
        $tr->add( 'th',array( 'class' => 'derecha') , number_format ($index_length, 0, ',', '.' ) . ' bytes');
        
        
        
     
        $this->render( $HTML, 'Información de la base de datos', '');

        
    }
    function reordenar() {
        // reordena (reorganiza)  la base de datos de páginas //
        
        $HTML = new HTMLContainer(); 
        
        global $pagina ;
        global $tipoboton ;
        global $aArgumentos;
        global $pagina;
        global $idmenu;
        global $DB_conexion;
        global $DB_prefijo;
        global $DB;
        $pagina = $_SERVER["PHP_SELF"];
        // creamos el recordset con todos los uidpagina
        $comando_sql = "select uid from " . $DB_prefijo . "paginas" ;
        $recordset_uids = $DB->execute("$comando_sql") ;
        $TotalRecs = 0;
        while (!$recordset_uids->EOF) {
              $uid      = $recordset_uids->fields['uid'];
              $TotalRecs++;
              // restablecemos el uidroot
              $uidroot = morcegocms_utils::uidrootfromuid( $uid );
              $comando_sql = "update " . $DB_prefijo . "paginas set " .
                 "uidroot = $uidroot where uid = $uid ";
              $DB->execute ( $comando_sql );
              // buscamos todos los hijos
              for ( $tipo=0; $tipo <=2; $tipo++ ) {
                  
                  $orden = 1;
                  $comando_sql = "select uid from " . $DB_prefijo . "paginas where uidparent = $uid  and uid != 0 and tipo={$tipo} order by -activa, orden" ;
                  $recordset_uidhijos = $DB->execute("$comando_sql") ;
                  while (!$recordset_uidhijos->EOF) {
                         $uidhija = $recordset_uidhijos->fields['uid'];
                        $comando_sql = "update " . $DB_prefijo . "paginas set orden = $orden where uid= $uidhija";
                        
                        $DB->execute("$comando_sql") ;
                        $orden++;
                        $recordset_uidhijos->MoveNext();
                  }
               }
              $recordset_uids->MoveNext();
        }
        $HTML->add_object( customHTML::DialogBox( _DIALOG_INFO , 'Reorganización de la base de datos', 
            "La base de datos de páginas ha sido reorganizada. total páginas existentes en la base de datos: {$TotalRecs}",
            customHTML::BotonBack() ));
        $this->render( $HTML, 'Reorganización de la base de datos', '');
    }
    
    function archivos_no_utilizados() {
    /**
    *    Esta función nos buscará todos los archivos que no están siendo utilizados para permitir su borrado y así
    *    Evitar tener la base de datos ocupada con información no relevante. Antes de este borrado se debe hacer un 
    *    backup externo de toda la base de datos.
    *    
    *    Es una Prueba de concepto: 
    *    
    *    @date: 9 Feb 2004:
    *    @author: Antonio Cortés <antonio@antoniocortes.com>
    *
    **/
        $HTML = new HTMLContainer();
        /**
        * Primero localizaremos todos los ficheros utilizados en las plantillas 
        *  idtemplate, content, content_header, content_footer
        **/
        $comando_sql = "select content, content_header, content_footer from {$GLOBALS['DB_prefijo']}templates ";
        
        /* $ficheros contendrá todos los ficheros en uso */
        $ficheros = array() ;
        
        $recordset = $GLOBALS['DB']->execute( $comando_sql );
        
        while (!$recordset->EOF) {
            $texto  =& $recordset->fields['content'];
            preg_match_all( '|{codigo\:url_fichero\:(.*)}|ieUs', $texto, $resultado ); 
            
            $encontrados =&  $resultado[1] ;
            foreach ( $encontrados as $valor ) {
                if ( !in_array( $valor, $ficheros )) {  $ficheros[] = $valor ; }
            }
            $texto  =& $recordset->fields['content_header'];
            preg_match_all( '|{codigo\:url_fichero\:(.*)}|ieUs', $texto, $resultado ); 
            $encontrados =&  $resultado[1] ;
            foreach ( $encontrados as $valor ) {
                if ( !in_array( $valor, $ficheros )) {  $ficheros[] = $valor ; }
            }

            $texto  =& $recordset->fields['content_footer'];
            preg_match_all( '|{codigo\:url_fichero\:(.*)}|ieUs', $texto, $resultado ); 
            $encontrados =&  $resultado[1] ;
            foreach ( $encontrados as $valor ) {
                if ( !in_array( $valor, $ficheros )) {  $ficheros[] = $valor ; }
            }
            $recordset->MoveNext();
        }
        
        
        $comando_sql = "select texto, descripcion from {$GLOBALS['DB_prefijo']}paginas ";
        $recordset = $GLOBALS['DB']->execute( $comando_sql );
        
        while (!$recordset->EOF) {
            $texto  =& $recordset->fields['texto'];
            preg_match_all( '|{codigo\:url_fichero\:(.*)}|ieUs', $texto, $resultado ); 
            $encontrados =&  $resultado[1] ;
            foreach ( $encontrados as $valor ) {
                if ( !in_array( $valor, $ficheros )) {  $ficheros[] = $valor ; }
            }
            
            $texto  =& $recordset->fields['descripcion'];
            preg_match_all( '|{codigo\:url_fichero\:(.*)}|ieUs', $texto, $resultado ); 
            $encontrados =&  $resultado[1] ;
            foreach ( $encontrados as $valor ) {
                if ( !in_array( $valor, $ficheros )) {  $ficheros[] = $valor ; }
            }

            $recordset->MoveNext();
        }
        // ahora en $ficheros tenemos todos los ficheros que están siendo utilizados
        
        // traemos todos los ficheros que tenemos en la base de datos
        
        $comando_sql = "select idfile from {$GLOBALS['DB_prefijo']}files where category != 'botones' and internal != 1";
        $recordset = $GLOBALS['DB']->execute( $comando_sql );
        
        $no_utilizados = array();
        while (!$recordset->EOF) {
            if ( !in_array( $recordset->fields['idfile'], $ficheros )) {  
                $no_utilizados[] = $recordset->fields['idfile'] ; 
            }
            $recordset->MoveNext();
        }
        echo "<pre>Ficheros no utilizados en las plantillas y paginas: \n";
        print_r( $no_utilizados ) ;
        echo '</pre>';
        
        
        
        
        // asort( $ficheros );
        /*
        echo "<pre>Ficheros utilizados en las plantillas y paginas: \n";
        print_r( $ficheros ) ;
        echo '</pre>';
        */
        $this->render( $HTML, 'Información de la base de datos', '');
        
    }    
    
    
}



?>