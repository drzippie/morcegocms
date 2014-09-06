<?php
/**
*   Cambios: 2003/08/20 : Se le ha quitado la extensión .html a las plantillas y se ha utilizado el constructor html
*
*
*
*
*/
class adm_templates  extends adm_class{
	var $aPrefijos = array (
	'template' 	=> 'template',
	'content'	=> 'content',
	'include'	=> 'include',
	'funcion'	=> 'funcion',
        'menu'	        => 'menu'
	);
	var $aExtensiones = array (
	'html' => 'html'
	);
        
        function adm_templates( $html)  {
            $this->parameters =& $GLOBALS['aArgumentos'] ;
            $this->oHtml = $html;
	    $this->oHead = new htmlcontainer() ;
	    $this->oHead->add( html_admin::css(  'css/templates.css' )); 
	    
	    
            if (!isset( $this->parameters[1] )) {
                $this->parameters[1] = 'list' ;
            }
            if (!isset( $this->parameters[2] )) {
                $this->parameters[2] = 'template' ;
            }
            if ( $this->parameters[1] == 'list' && !isset( $this->aPrefijos[ $this->parameters[2]])) {
               $this->parameters[2] = 'template' ;
            }
            switch (  $this->parameters[1] ) {
                case "list";
                    $this->show_templates( $this->parameters[2] );
                    break;
                case "delete";
                    $this->delete_template( $this->parameters[2] );
                    break;
                case "add";
                    $this->add_template( $this->parameters[2] );
                    break;
                case "edit";
                    $this->edit_template( $this->parameters[2] );
                    break;
                case "add_ok";    
                    $this->add_template_ok();
                    break;
                case "edit_ok";
                    $this->edit_template_ok();
                    break;
                case "stats":
                    $this->show_stats( $this->parameters[2]);
                    break;
            }
            
        }
        
	function show_templates( $template_prefix ) {
		global $configCMS;
		global  $DB, $DB_prefijo;
                $comando_sql = "select idtemplate, lastmodified, descripcion  from {$DB_prefijo}templates where idtemplate like \"{$template_prefix}%\" order by lastmodified desc";
                $recordset = $DB->execute($comando_sql) ;
        
            $html = new htmlcontainer();
            $form =& $html->add( 'form', array( 'name' => 'lista' ));
            $form->add( '', '', 'Tipo Plantilla:');
            $form->add( '', '', $this->combo_template_prefix( $template_prefix, 'prefijo', "window.location.href = 'admin.php?q=templates/list/' +document.lista.prefijo.value;"));
            $form->add (CustomHTML::botonAdmin("Crear Plantilla" , 'Crear una nueva plantilla', '#', "window.location.href='admin.php?q=templates/add/'+ document.lista.prefijo.value  ;" ) );
            $html->add( 'br');
            $table =& $html->add( 'table', array( 
            
            'class'       => 'ruler wide'));
            $tr =& $table->add( 'tr');
                $tr->add( 'th', '', '&nbsp;' );
                $tr->add( 'th', '', 'Plantilla');
                $tr->add( 'th', '', '# Uso' );
                $tr->add( 'th', '', 'fecha');
                $tr->add( 'th', '', '&nbsp;');
				$tdClass = 'non' ;
                while ( !$recordset->EOF) {
                    $numpaginas = $this->show_num_template_pages($recordset->fields['idtemplate']); 
					$tdClass = ($tdClass === 'non') ? false : 'non' ;
                    $tr =& $table->add( 'tr', array( 'class' => $tdClass));
                        $td =& $tr->add( 'td' );
                        
                        $td->add_object( 
                                CustomHTML::Boton16x16(_ADM_BOTON_EDITAR,
                                '#',
                                'Editar Plantilla',
                                '',
                                "window.location.href='?q=templates/edit/{$recordset->fields['idtemplate']}'")) ;
                        $tr->add( 'td', '', $recordset->fields['idtemplate'] ); 
                        
                        $td =& $tr->add( 'td', array( 'class' => 'centro' ));
                          $td->add( 'a', array( 
                            'href'      => "?q=templates/stats/{$recordset->fields['idtemplate']}",
                            'title'     => 'Ver estadísticas de uso de la plantilla',
                            'class'     => 'fnegrita'),
                            $numpaginas );
                        
                        // $tr->add( 'td', array( 'class' => 'centro' ), $numpaginas );
                        
                        $tr->add( 'td', '', $recordset->fields['lastmodified'] );
                        
                        $td =& $tr->add( 'td') ;
                        /*
						if (!empty( $recordset->fields['descripcion'] )) {
                          $td->add_object( 
                          CustomHTML::boton16x16(_ADM_BOTON_INFO,  
                              'javascript:void(0);' , 
                              'Ver notas',
                              '',
                              "return overlib('" .  addslashes( nl2br( $recordset->fields['descripcion'] )) . 
                                  "', STICKY, WIDTH, 300,  CAPTION, '{$recordset->fields['idtemplate']}', CENTER);"
                              )) ;
                        }
						*/ 
                        if ($numpaginas == 0 ) {
                           $td->add_object( CustomHTML::boton16x16(_ADM_BOTON_BORRAR, 
                                '#', 'Borrar Plantilla', '',
                                "confirmar('¿ Desea Borrar la plantilla, esta acción no se podrá deshacer ?','admin.php?q=templates/delete/{$recordset->fields['idtemplate']}');"));
                        }
                  if ( count( $td->Elements ) == 0  ) {
                    $td->add_text( '&nbsp');
                  }                        
                    $recordset->MoveNext();
                }
                $this->render( $html, 'Administración de plantillas', '' );
            
	}
	    
    function edit_template( $idtemplate) {
       $this->form_edit( 'edit', $idtemplate, '' );
    }

    function form_edit( $action = "edit", $idtemplate = '', $prefix = '' ) {
        global $configCMS;
        $dbtemplate = new cls_dbtemplates;
        $crypt = new MorcegoCrypt;
        $content_header = '';
        $content_footer = '';
        $descripcion = '';
        if (substr( $idtemplate, 0, 5 ) == 'menu_') {
            $prefix = 'menu';
        }
        $html = new htmlcontainer();
        $form =& $html->add( 'form', array(
            'name'      => 'editar',
            'method'    => 'post',
            'class'     => 'solapas',
	    'onsubmit' => "return false;" ,
            'action'    => "admin.php?q=templates/{$action}_ok" ));
        $form->add( 'input', array( 'type'   => 'hidden', 'name'   => 'accion', 'value'  =>  $action ));
        if ( $action == 'edit') {

            $content = htmlentities( $dbtemplate->read_template( $idtemplate )  );
           
            $descripcion = $dbtemplate->read_descripcion( $idtemplate );
           
            $form->add( 'input', array( 'type'   => 'hidden', 'name'   => 'fichero', 'value'  =>  $idtemplate ));
            $form->add( 'input', array( 'type'   => 'hidden', 'name'   => 'nombre',  'value'  => '' ));                
            $form->add( 'br' );
            $form->add( '', '', "Nombre: " );
            $form->add( 'strong', '',  $idtemplate );
            $form->add( 'br' );
        } else {
            $content = '';
            
            $form->add( 'input', array( 'type'   => 'hidden', 'name'   => 'prefix', 'value'  =>  $prefix ));
            $form->add( 'input', array( 'type'   => 'hidden', 'name'   => 'extension', 'value'  =>  'html' ));
            $form->add( 'br' );
            $form->add( '', '', "Nombre: " );
            $form->add( 'strong', '', $prefix . '_' )  ;
            $form->add( 'input', array( 'type'   => 'text',   'name'   => 'nombre', 'value'  =>  '', 'style' => 'width: 200px;' ));
            // $form->add('strong', '',  '.html' );
            $form->add( 'br' );
        }
        $divBotones =& $form->add('div', array( 'class' => 'barra-botones' ) );
        
	$divSolapas =& $form->add('div', array('id' => 'divSolapas' ));
		
	
	
        // $table =& $form->add( 'table');
        if ( $prefix == 'menu' ) {
		$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
		$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Encabezado'  ));
		$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
		$solapa =& $div2->add('DIV' ) ;
	
	        $contentfooter = ( $action == 'edit') ? 
			htmlentities( $dbtemplate->read_template_footer( $idtemplate )) :
			'' ;
		$contentheader = ( $action == 'edit') ? 
			htmlentities( $dbtemplate->read_template_header( $idtemplate )  ) :
			'';
		$fieldset =& $solapa->add('div');    
		$fieldset->add( 'textarea', array( 
			'name' => 'contentheader',
			'id' => 'contentheader'),
			$contentheader);
        } 
        $divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
		$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Cuerpo'  ));
		$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
		$solapa =& $div2->add('DIV' ) ;
		$fieldset =& $solapa->add('div');    
		$fieldset->add( 'textarea', array( 
			'name' => 'content',
			    'id'   => 'content'
			    ),
			$content);
	if ( $prefix == 'menu' ) {
		$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
		$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Pie'  ));
		$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
		$solapa =& $div2->add('DIV' ) ;        
		$fieldset =& $solapa->add('div');    
		$fieldset->add( 'textarea', array( 
			'name' => 'contentfooter',
			'id'    => 'contentfooter'
			),
			$contentfooter);
	}
	$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
	$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Descripción'  ));
	$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
	$solapa =& $div2->add('DIV' ) ;
        
        $fieldset =& $solapa->add('div');    
 
        $fieldset->add('textarea', array( 
		'name' => 'descripcion' ,
		'id' => 'descripcion'),
		$descripcion );

         
        $divBotones->add( CustomHTML::botonAdmin("Grabar y Salir" , 'Modifica/Crea la plantilla', '#', 
            "check_edttemplate( ['{$action}' ,'{$prefix}_' +  document.editar.nombre.value  , 'save'    ]);" ) );
        $divBotones->add( CustomHTML::botonAdmin("Grabar y Continuar" , 'Graba los cambios y continua la edición', '#', 
            "check_edttemplate( ['{$action}' ,'{$prefix}_' +  document.editar.nombre.value  , 'continue'    ]);" ) ) ;            
        $divBotones->add( CustomHTML::botonAdmin('Cancelar', "Cancelar Edición", "", "history.go(-1)") );


        $this->render( $html ,  ( $action == 'edit') ? 'Modificación de plantillas' : 'Alta de plantillas', '');
    }

	function delete_template( $idtemplate) {
		global $aArgumentos ;
		global $configCMS;
                global $DB, $DB_prefijo;
		if ( $this->show_num_template_pages($idtemplate) > 0 ||    
                    $this->show_num_include_pages( $idtemplate ) > 0 ||
                    $this->show_num_include_templates( $idtemplate ) > 0  ) {
			$html = customHTML::DialogBox( _DIALOG_STOP , 'ERROR: Borrado de Plantillas',
			'No se puede borrar la plantilla especificada, esta plantilla'.
			' está siendo actualmente utilizada en páginas y/u otras plantillas'.
			'<br/><br/>', 
                        CustomHTML::botonAdmin("Volver" , 'Volver', '#', "history.go(-1);" )
                        ); 
			$this->render( $html);
		
		} else {
			$comando_sql = "delete from {$DB_prefijo}templates where idtemplate = '{$idtemplate}'  ";
                        $DB->execute( $comando_sql );
                        
                        $aTMP = explode( '_', $idtemplate );
                        
                        $this->show_templates($aTMP[0]);
                        die();
		}
               
	}
	
	
	function edit_template_ok() {
            global $DB, $DB_prefijo;
            $idtemplate = $_POST['fichero'];
            $contentheader =  (!isset( $_POST['contentheader'])) ? '':  $_POST['contentheader'];
            $contentfooter =  (!isset( $_POST['contentfooter'])) ? '':  $_POST['contentfooter'];
            $content = $_POST['content'];
            $descripcion = $_POST['descripcion'];
            
            if ( get_magic_quotes_gpc() == 1 ) {
                $content       = stripslashes($content);
                $contentheader = stripslashes($contentheader);
                $contentfooter = stripslashes($contentfooter);
                $descripcion   = stripslashes($descripcion);
            }
            $DB->Replace($GLOBALS['configCMS']->get_var('dbprefijo') . 'templates ' , 
                array( 
                'content'        => $content,
                'content_header' => $contentheader,
                'content_footer' => $contentfooter,
                'descripcion'    => $descripcion,
                'iduser'         => $_SESSION['iduser'],
                'idtemplate'     => $idtemplate,
                'lastmodified'   => $GLOBALS['DB']->DBTimeStamp( time()) ),
                'idtemplate',
                true    );
            morcegocms_utils::EmptyCacheObjects();
            if ( $_POST['accion'] == 'continue' ) {
                header("Location: ?q=templates/edit/{$idtemplate}" );
                die();
            } else {
                $aIdtemplate = explode('_', $idtemplate );
                $prefix =& $aIdtemplate[0] ;
                header("Location: admin.php?q=templates/list/{$prefix}");
                die();
            }
	}
        
        function show_stats( $idtemplate ) {
            $html = new htmlcontainer();
            $html->add( 'h2', '', 'Estadísticas de la plantilla: '. $idtemplate );
            $html->add( 'br' );
            $table =& $html->add( 'table', array( 
                
                'class'         => 'ruler wide'));
            $tr =& $table->add( 'tr');
                $tr->add( 'th', '', 'Elemento' );
                $tr->add( 'th', '', '&nbsp;');

            $aTD = array( 'style' => 'text-align: right; font-weight: bolder;');
            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Usuario última modificación: ');
                $aUser = $this->show_user( $idtemplate) ;
                $td =& $tr->add('td', $aTD,$aUser[1] );
                if( $aUser[0] != -1 ) {
                    $td->add('', '', '&nbsp;');
                    $a =& $td->add('a', array(
                        'href' => "admin.php?q=edt_user/{$aUser[0]}",
                        'title' => "Editar perfil del usuario" ));
                    $a->add('img', array( 
                        'width' => '16',
                        'height' => '16',
                        'src' => _ADM_BOTON_EDITAR,
                        'border' => 0,
                        'alt' => 'Editar Usuario'));
                }
            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Fecha de última modificación: ');
                $tr->add('td', $aTD, $this->show_date( $idtemplate));
            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Tamaño de la plantilla en bytes: ');
                $tr->add('td', $aTD, $this->show_size( $idtemplate));

            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Paginas asociadas a esta plantilla: ');
                $td =& $tr->add('td', $aTD, $this->show_num_template_pages( $idtemplate));
            $tr =& $table->add('tr');
            $td =& $tr->add('td', array( 'colspan' => '2' ));
            $array = $this->TemplateUsage( $idtemplate,  $class = "pages" ) ;
            $td->add('br') ;
            
            $td->add( 'strong', '', 'Páginas ');
            foreach ( $array as $element ) {
              $td->add_text( ' : ');
              $td->add('a', array(
                'href' => 'edtpaginas.php?/' . $element['uid'],
                'title' => 'Editar: ' . $element['titulo']
                
                ), $element['idpagina']);
            
            }
            
            
            
            
                
                
            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Paginas que incluyen esta plantilla: ');
                $tr->add('td', $aTD, $this->show_num_include_pages( $idtemplate));
                
            $tr =& $table->add('tr');
            $td =& $tr->add('td', array( 'colspan' => '2' ));
            $array = $this->TemplateUsage( $idtemplate,  $class = "templates" ) ;
            $td->add('br') ;
            
            $td->add( 'strong', '', 'Páginas ');
            foreach ( $array as $element ) {
              $td->add_text( ' : ');
              $td->add('a', array(
                'href' => 'edtpaginas.php?/' . $element['uid'],
                'title' => 'Editar: ' . $element['titulo']
                
                ), $element['idpagina']);
            
            }
            
                
                
            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Plantillas que incluyen esta plantilla: ');
                $tr->add('td', $aTD, $this->show_num_include_templates( $idtemplate));        
            
              $tr =& $table->add('tr');
            $td =& $tr->add('td', array( 'colspan' => '2' ));
            $array = $this->TemplateUsage( $idtemplate,  $class = "includes" ) ;
            $td->add('br') ;
            
            $td->add( 'strong', '', 'Plantillas ');
            foreach ( $array as $element ) {
              $td->add_text( ' : ');
              $td->add('a', array(
                'href' => '?q=templates/edit/' . $element['idtemplate'],
                'title' => 'Editar: ' . $element['idtemplate']
                
                ), $element['idtemplate']);
            
            }
            
            
            $tr =& $table->add('tr');
                $tr->add('td' , '', 'Plantillas en el caché de objetos: ');
                $tr->add('td', $aTD, $this->show_cache_objects( $idtemplate));        


            $html->add('br');
            $html->add(CustomHTML::botonAdmin("Volver" , 'Volver', '#', "history.go(-1);" ) );
            $this->render( $html);
        }
        
	/**
	* @return string
	* @param template string
	*/
	function show_date( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select lastmodified from {$DB_prefijo}templates where idtemplate ='{$template}'";
		$recordset = $DB->execute( $comando_sql);
		$numpaginas = $recordset->fields['lastmodified'];
		return  $numpaginas;
	}
        
        /**
	* @return array
	* @param template string
	*/
	function show_user( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select iduser from {$DB_prefijo}templates where idtemplate ='{$template}'";
		$recordset = $DB->execute( $comando_sql);
		$iduser = $recordset->fields['iduser'];
                $comando_sql = "select username from {$DB_prefijo}users where iduser ='{$recordset->fields['iduser']}'";
                $recordset = $DB->execute( $comando_sql);
                $iduser = (!isset( $recordset->fields['username'] )) ? -1 : $iduser ;
                $username = (!isset( $recordset->fields['username'] )) ? '<I>*Desconocido*</I>' : $recordset->fields['username'];
		return  array( $iduser,  $username  );
	}
        /**
        * @return array
        * @param idtemplate string, class string
        * @desc  Nos devuelve un array asociativo con las paginas/plantillas/... que incluyen esa página
        */
        
        
        
        function TemplateUsage( $template,  $class = "pages" ) {
          $array = array();
          switch ( $class ) {
            case 'includes':
              $comando_sql = "select idtemplate  from {$GLOBALS['DB_prefijo']}templates where ".
                "content like '%{include:{$template}}%' " .
                "or content_header like '%{include:{$template}}%' " .
                "or content_footer like '%{include:{$template}}%' " .
                "or content like '%{menu:%:{$template}}%' " .
                "or content_header like '%{menu:%:{$template}}%' ".
                "or content_footer like '%{menu:%:{$template}}%'" ;
              break;
            case 'templates':
              $comando_sql = "select idpagina, titulo, activa, uid from {$GLOBALS['DB_prefijo']}paginas where " .
                "texto like '%{include:{$template}}%' " .
                "or texto like '%{menu:%:{$template}}%'";
              break;
            case 'pages':
              $comando_sql = "select idpagina, titulo, activa, uid from {$GLOBALS['DB_prefijo']}paginas where " .
                "template ='{$template}'";
              break;
            default :
              $comando_sql = "select idpagina, titulo, activa, uid from {$GLOBALS['DB_prefijo']}paginas where " .
                "template ='{$template}'";
              break;
          }
          $recordset = $GLOBALS['DB']->execute( $comando_sql);
          while ( !$recordset->EOF ) {
            $array[]  = $recordset->fields ;
            $recordset->MoveNext();
          }
        
          return $array ;
        
        
        }
        
        
        
	/**
	* @return integer
	* @param template string
	* @desc Nos devuelve el número de páginas en las que el template está siendo utilizado.
	*/
	function show_num_template_pages( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select count(*) as total from {$DB_prefijo}paginas where template ='{$template}'";
		$recordset = $DB->execute( $comando_sql);
		$numpaginas = $recordset->fields['total'];
		return  $numpaginas;
	}
        	/**
	* @return integer
	* @param template string
	* @desc Nos devuelve el número de páginas que hacen un include de esta página.
	*/
	function show_num_include_pages( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select count(*) as total from {$DB_prefijo}paginas where texto like '%{include:{$template}}%' " .
                    "or texto like '%{menu:%:{$template}}%'";
		$recordset = $DB->execute( $comando_sql);
		$numpaginas = $recordset->fields['total'];
		return  $numpaginas;
	}
        /**
	* @return integer
	* @param template string
	* @desc Nos devuelve el número de plantillas que hacen un include de esta página.
	*/
	function show_num_include_templates( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select count(*) as total from {$DB_prefijo}templates where content like '%{include:{$template}}%' " .
                    "or content_header like '%{include:{$template}}%' or content_footer like '%{include:{$template}}%' " .
                    "or content like '%{menu:%:{$template}}%' " .
                    "or content_header like '%{menu:%:{$template}}%' or content_footer like '%{menu:%:{$template}}%'" ;
		$recordset = $DB->execute( $comando_sql);
		$numpaginas = $recordset->fields['total'];
		return  $numpaginas;
	}
        
        /**
	* @return integer
	* @param template string
	* @desc 
	*/
	function show_cache_objects( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select count(*) as total from {$DB_prefijo}objects  where " .
                    "idobject like 'template.%.{$template}.%' or " .
                    "idobject like 'menu.%.{$template}.%' ";
		$recordset = $DB->execute( $comando_sql);
		return $recordset->fields['total'];
	}

        
        
        /**
	* @return integer
	* @param template string
	* @desc Nos devuelve el número de páginas en las que el template está siendo utilizado.
	*/
	function show_size( $template) {
		global  $DB, $DB_prefijo;
		$comando_sql = "select content, content_header, content_footer from {$DB_prefijo}templates where idtemplate ='{$template}'";
		$recordset = $DB->execute( $comando_sql);
		return  strlen( $recordset->fields['content'] ) + 
                        strlen($recordset->fields['content_header'] ) +  
                        strlen( $recordset->fields['content_footer']) ;
	}
        
        
	/**
	* @return integer
	* @desc Nos muestra un formulario para la creación de un nuevo template.
	*/
	function  add_template( $prefix)  {
            $this->form_edit( 'add', '', $prefix );
	}
	function add_template_ok() {
            $_POST['fichero'] = "{$_POST['prefix']}_{$_POST['nombre']}";
            $this->edit_template_ok();  
        }

    function combo_template_prefix($value, $control_name, $onchange = '' ) {
        $html = new HtmlContainer();
        $select =& $html->add('select', array( 'name' => $control_name, 'onchange' => $onchange ));
        foreach ( $this->aPrefijos  as $variable => $valor ) {
            if ( $valor == $value ) {
                $select->add( 'option', array( 'value' => $valor, 'selected'  => true), $variable);
            } else {
                $select->add( 'option', array('value' => $valor ), $variable) ;
            }
        }
        return $html->render();
    
    }
    
    function html_select_prefijo( $value ) {
     	reset( $this->aPrefijos);
        $html = new HtmlContainer();
        $select =& $html->add('select', array( 'name' => 'prefijo'));
        while (list ($key, $val) = each ($this->aPrefijos )) {
            if ( "$value" == $key ) {
                $select->add( 'option', array( 'value'     => $key, 'selected'  => true), $val);
            } else {
                $select->add( 'option', array('value' => $key), $val) ;
            }
     	}
     	return $html->render();
     }
     function html_select_extension( $value ) {
     	reset( $this->aExtensiones);
     	$str_out = '<select name="extension">';
     	while (list ($key, $val) = each ($this->aExtensiones )) {
     		$str_out .= "<option value='{$key}'";
     		if ( "$value" == $key ) {
     			$str_out .= ' selected ';
     		}
     		$str_out .= ">{$val}</option>";
     	}
     	return $str_out;
     }
    }
	
	
	
?>
