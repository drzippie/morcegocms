<?php 
include_once( dirname( __FILE__ ) .  '/class_paginas.php');
class  adm_paginas extends adm_class{

	
	function adm_paginas( $html = null  ) {
		
		$this->parameters =& $GLOBALS['aArgumentos'] ;
		if ( $html == null ) {
			return ;
		}
		$this->oHtml = $html;
		/* Añadimos el html especifico de esta parte
		*/
		$this->oHead = new htmlcontainer() ;
		$this->oHead->add( html_admin::css(  'css/pages.css' ));
		
		if (!isset( $this->parameters[1] )) {
			$this->parameters[1] = 'list' ;
		}
		switch ( $this->parameters[1]  )  {
			case 'buscar' : 
				$this->buscar();
				break;
			case 'add' : 
				$this->nuevo();
				break;
			case 'borrar' : 
				$this->borrar_pagina();
				break;
			case 'edit' :
				$this->editar();
				break;
			case 'add_lang' :
				$this->add_lang();
				break;
			case 'edt_lang' :
				$this->edt_lang();
				break;				
			case 'form' :
				$this->procesar_formulario();
				break;	
			case 'delimage':
				$this->borrarimagen();
				break;
			case 'delicono':
				$this->borraricono();
				break;
			case 'mover':
				$this->mover_pagina();
				break;
			case 'duplicar':
				$this->duplicar_pagina();
				break;
			case 'duplicar_ok':
				$this->duplicar_ok_pagina();
				break;
			case 'mover_ok':
				$this-> mover_ok_pagina();
				break;
			case 'arbol':
				$this->arbol_paginas();
				break;
			case 'cambiar_orden':
				$this->cambiar_orden();
				break;
			case 'goto':
				$this->goTo();
				break;
			default:
				$this->list_pages() ;
				break;
		
		}

	}
	function goTo() {
		if ( isset($_POST['iraidpagina']) && !empty($_POST['iraidpagina'])) {
			$uid = morcegocms_utils::uidfromidpagina( $_POST['iraidpagina']);
			if (!empty( $uid) ) {
				Header( "Location: admin.php?q=paginas/list/{$uid}");
			} else {
				Header( "Location: admin.php?q=paginas/list");
			}
		} else {
			Header( "Location: admin.php?q=paginas/list");
		}
		die();
	}
	
	function nuevo() {
	
		global $aArgumentos, $pagina, $tipoboton;
		global $uidparent;
		$head = new htmlcontainer();
		$head->add(  html_admin::js(  'js/elements/pagesedt.js' ) ) ;
		
		$this->render( $this->form_edit() ,
			'Alta de Páginas', '', null, $head );

    	}

function procesar_formulario() {
	if (isset($_POST['accion'])) $accion = $_POST['accion'] ;
	if (!empty($accion)) {
		switch ($accion) {
		case 'edit':
			$resultado = $this->edit_ok( );
			$uidparent = $_POST['uidparent'];
			break;
		case 'add_lang':
			$resultado = $this->add_lang_ok();
			$uidparent = $_POST['uidparent'];
			break;            
		case 'edt_lang':
			$resultado = $this->edt_lang_ok();
			$uidparent = $_POST['uidparent'];
			break;            
		
		case 'add':
			$resultado = $this->add_ok();
			break;
		case "saveandcontinue":
			$url_retorno = "admin.php?q=paginas/edit/{$_POST['uid']}";
			$this->edit_ok( $url_retorno);
			break;
		case 'del_pagina_ok':
			$this->del_pagina_ok( $_POST['uid']);
			die();
			break;
		}
	} else {
		header( 'Location: admin.php?q=paginas' ) ;
		die();
	}

}

	function mover_ok_pagina() {
		$html = new htmlcontainer() ;
		$destino = $_POST['destino'];
		$iddestino =  morcegocms_utils::uidfromidpagina( $destino );
		$uid = $_POST['uid'];
		// comprobamos que no sea el mismo uid destino al origen
		if ( $iddestino == $uid ) {
			$html->add( customHTML::DialogBox( _DIALOG_STOP , 'Error: Movimiento de Páginas',
			'No se puede mover la página a si misma, indique un idpagina válido<br/><br/>'.
			'<input type="reset" value="Volver" onclick="javascript:history.back();">'	
			));
			die( $this->render( $html, 'ERROR'  ));
			
		}
		if ( $iddestino > 0 || $destino == 'INDEX') {
			global $DB, $DB_prefijo;
			if ( $iddestino  == 0 ) {
				$uidroot = $uid ;
			} else {
				$uidroot = morcegocms_utils::uidrootfromuid( $iddestino);
			}
			$comando_sql = 'update ' . $DB_prefijo . "paginas set ".
				"uidparent = $iddestino, ".
				"uidroot = $uidroot " .
				"where uid = $uid ";
			$DB->execute($comando_sql) ;
			$html->add( customHTML::DialogBox( _DIALOG_INFO ,  'Movimiento de Páginas',
				'La página ha sido movida correctamente<br/><br/>'.
				"<input type=\"reset\" value=\"Volver\" onclick=\"window.location.href='admin.php?q=paginas/list/{$iddestino}' ;\">"	
				))	;		
			die( $this->render( $html, 'Mover Páginas'  ));
			
		} else {
			$html->add( customHTML::DialogBox( _DIALOG_STOP , 'Error: Movimiento de Páginas',
				'El IDPagina especificado no existe, compruebe las mayúsculas y minúsculas<br/><br/>'.
				'<input type="reset" value="Volver" onclick="javascript:history.back();">'	
				));
			die( $this->render( $html, 'ERROR'  ));
		}
	}

	function duplicar_pagina( ) {
		$uid = (int)  $this->parameters[2] ;
  		$formulario = new htmlobject( 'form', array(
			'name' => 'formulario',
			'method' => 'post',
			'action' => 'admin.php?q=paginas/duplicar_ok'));
		$form = new htmlcontainer();
		$form->add( 'input', array(
			'type' => 'hidden',
			'name' => 'uid',
			'value' => $uid )
			);
		$form->add_text('IDPágina para la copia: ' );
		$form->add( 'input', array(
			'type' => 'text',
			'name' => 'destino',
			'value' => '' )
			);
		$form->add('br');
		$form->add('em', '', 'Debe indicar un identificador de página válido y único');
		
		
		$botones = new htmlobject( 'div' );
		$botones->add('input', array(
			'type' => 'submit',
			'value' => 'Duplicar' ));
		$botones->add_text(' ' );
		$botones->add('input', array(
			'type' => 'reset',
			'value' => 'Volver',
			'onclick' => 'javascript:history.back();'  ));
		$formulario->add(customHTML::DialogBox( 2, 'Duplicar Página', $form, $botones ));
		echo $this->render(  $formulario, 'Duplicar Página');
		die();	
	}


	function duplicar_ok_pagina() {
		$html = new htmlcontainer();
		global $DB, $DB_prefijo;
		$aCampos = array ( 
			'idgroup'          => 'n',
			'tipo'          => 'n',
			'titulo'        => 'c',
			'texto'         => 'b',
			'text_align'    => 'c',
			'enlace'        => 'c',       
			'img_mimetype' => 'c',
			'uidroot'       => 'n',
			'img_content'   => 'b',
			'uidparent'     => 'n',
			'textohijas'    => 'c',
			'textolink'     => 'c',
			
			'activa'        => 'n',
			'template'      => 'c',
			'img_align'     => 'c',
			'icono_content' => 'b',
			'icono_align'   => 'c',
			'icono_mimetype' => 'c',
			'descripcion'   => 'c',
			'variables'     => 'c'
			);
		$destino = $_POST['destino'];
		$uid = $_POST['uid'];
		$comando_sql = "select * from {$DB_prefijo}paginas where uid = ${uid}";
		$recordset = $DB->execute( $comando_sql );
		
		$uidparent = $recordset->fields['uidparent'];
		$comando_sql = "select max(orden)+ 1 as orden, max(uid) +1 as uid from {$DB_prefijo}paginas where uidparent = {$uidparent}";
		$recordset2 = $DB->execute( $comando_sql ) ;
		$orden = $recordset2->fields['orden'];
		
		$comando_sql = "select max(uid) +1 as uid from {$DB_prefijo}paginas ";
		$recordset2 = $DB->execute( $comando_sql ) ;
		$uid = $recordset2->fields['uid'];
		
		$bSQL = '"%s",';
		$cSQL = '"%s",' ;
		$nSQL = '%s,' ;
		
		$sql_out = "insert into {$DB_prefijo}paginas (";
		reset( $aCampos ) ;
		foreach ($aCampos as $nombre_campo => $tipo_campo) {
			$sql_out .= "{$nombre_campo }, " ;
		} 
		$sql_out .= ' orden, idpagina, uid) values (';
		reset( $aCampos ) ;
		foreach ($aCampos as $nombre_campo => $tipo_campo) {
			if ($tipo_campo  == 'b' ) {
				$recordset->fields[$nombre_campo] = addslashes($recordset->fields[$nombre_campo]);
			}
			$tipo_campo .= 'SQL';
			$sql_out .= sprintf( $$tipo_campo, $recordset->fields[$nombre_campo]);
		} 
		$sql_out .=   "{$orden}, \"{$destino}\", {$uid})";
		//  die ( $sql_out );
		$resultado = $DB->execute( $sql_out );
		$html->add( customHTML::DialogBox( _DIALOG_INFO , 'Duplicación de páginas',
			'La página ha sido duplicada correctamente<br/><br/>'.
			"<input type=\"reset\" value=\"Volver\" onclick=\"window.location.href='admin.php?q=paginas/list/{$uidparent}' ;\">"	
			));
		die( $this->render( $html, 'duplicado de Páginas' ));
	}

	function mover_pagina( ) {
		$uid = (int)  $this->parameters[2] ;
		$formulario = new htmlobject( 'form', array(
				'name' => 'formulario',
				'method' => 'post',
				'action' => 'admin.php?q=paginas/mover_ok'));
		
		$form =& new htmlcontainer();
		
		$form->add( 'input', array(
			'type' => 'hidden',
			'name' => 'uid',
			'value' => $uid )
			);
		$form->add_text('ID Página destino: ' );
		$form->add( 'input', array(
			'type' => 'text',
			'name' => 'destino',
			'value' => '' )
			);
		$form->add('br');
		$form->add('br');
		$form->add('em', '', 'Ponga INDEX (en mayúsculas) si desea mover <br />la página a la principal');
		
		
		$botones = new htmlobject( 'div' );
		$botones->add('input', array(
			'type' => 'submit',
			'value' => 'Mover' ));
		$botones->add_text(' ' );
		$botones->add('input', array(
			'type' => 'reset',
			'value' => 'Volver',
			'onclick' => 'javascript:history.back();'  ));
		
		$formulario->add( customHTML::DialogBox( 2, 'Mover Página', $form, $botones ) );
		// return $formulario ;	
		echo $this->render(  $formulario, 'Mover Páginas');
		
		die();
	
	}



	function borrar_pagina(){
		$uidparent = (( isset( $this->parameters[2] ) ? $this->parameters[2] : 0));
		$html = new htmlcontainer();
		// include_once ("includes/class_admpaginas.php");
		// $obj_adm = new adm_paginas; 
		$array_hijos = $this->array_descendientes( $uidparent );
		$titulo = morcegocms_utils::titulofromuid( $uidparent);
		$idpagina = morcegocms_utils::idpaginafromuid( $uidparent);
		$str_out = "¿ Desea Borrar la página [{$idpagina}] \"{$titulo}\" ?";
		$total_descendientes = count($array_hijos);
		$form =& $html->add('form', array('name' =>'formulario', 'method' => 'post', 'action' => 'admin.php?q=paginas/form'));
		$form->add('','', "¿ Desea Borrar la página [{$idpagina}] \"{$titulo}\" ?");
		if ( $total_descendientes > 0 ) {
			$form->add('BR');
			$form->add('BR');
			$form->add('', '', "Atención: Esta página tiene un total de [{$total_descendientes}] descendientes" );
		}
		$form->add( 'input', array( 
		    'type'      => 'hidden',
		    'name'      => 'accion',
		    'value'     => 'del_pagina_ok'));
		$form->add( 'input', array(
		    'type'      => 'hidden',
		    'name'      => 'uid',
		    'value'     => "{$uidparent}"));            
		$botones = new htmlcontainer();
		$botones->add_object( CustomHTML::BotonAdmin( 'Aceptar', 'Borrar Página(s)', '',  "document.formulario.submit();")) ; 
		$botones->add_object( CustomHTML::BotonAdmin( 'Cancelar', 'Cancelar', '',  "history.go(-1);")) ; 
		
		echo $this->render(CustomHTML::DialogBox( _DIALOG_QUESTION , 'Borrado de páginas', $html , $botones) , 
			'Borrado de Páginas');
		die();
		
	}
	/**
	* añade una nueva página
	*
	* Esta funcion recibe mediante post los valores de una página nueva. Si no se le
	* indica un idpagina este será aleatorio.
	*
	2002-12-04 : Si se especifica un idpagina vacío este será sustituido por
	un valor único aleatorio.
	* @return nada
	*
	*/
	function add_ok() {
		global $DB, $DB_prefijo ;
		global $varsCMS;
		/*
		Ponemos la variable post-activa en un formato 0-1
		*/
		$_POST['activa'] = (empty($_POST['activa'])) ? '0' : '1' ;
		$_POST['texto'] = str_replace( "http://{$_SERVER['SERVER_NAME']}", "", $_POST['texto']);
		$uidparent = $_POST['uidparent'];
		/* determinamos el raiz de esta página uidroot */
		$uidroot = ( $uidparent != 0 ) ? morcegocms_utils::uidrootfromuid( $uidparent ) : $uidparent ;
		/* Establecemos el orden que llevará la página dentro de su padre (el último)  */
		if ( $_POST['orden'] == 'last') {
			$comando_sql = 'select max(orden)+1 as maxorden from ' . $DB_prefijo . 'paginas ' . "where uidparent = $uidparent " ;
			$recordset = $DB->Execute($comando_sql);
			$orden = $recordset->fields['maxorden'];
			if (empty($orden)) { $orden = 1; }
		} else {
			$orden = '1';
			// movemos todas las páginas anteriores.
			$comando_sql = "update {$DB_prefijo}paginas set orden = orden + 1 where tipo = {$_POST['tipo']} and uidparent = {$uidparent}"; 
			$DB->execute( $comando_sql );
		}
		$comando_sql = 'select max(uid)+1 as maxuid from ' . $DB_prefijo . 'paginas';
		$recordset = $DB->Execute($comando_sql);
		$uid = $recordset->fields['maxuid'];
		if (empty($uid)) { $uid = 1  ;}
		
		if (empty( $_POST['idpagina']) ) { 
			$_POST['idpagina'] = md5( $_POST['titulo'] . $_POST['texto'] . "$orden $uidparent" );
		} else {
		// quitamos caracteres especiales 0.7.0.5
			$_POST['idpagina']  = ereg_replace( '[[:blank:]|[:cntrl:]|[\,]]{1,}', '_',  $_POST['idpagina'] );
			$_POST['idpagina']  = str_replace( '!', '_',   $_POST['idpagina'] );
		}
		$_POST['variables'] = str_replace( "\r", '', $_POST['variables'] );
		$aFecha = explode('/', $_POST['fechamod'] );
		$fecha = (empty( $_POST['fechamod'])) ? time() : mktime(0,0,0, $aFecha[1], $aFecha[0], $aFecha[2])  ;
		/*  Utilizamos replace porque si no existe lo crea   */
		$this->save_page( array( 
			'uid'            => $uid,
			'idpagina'       => $_POST['idpagina'],
			'titulo'         => ( !get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['titulo']) : $_POST['titulo'] ,
			'texto'          => ( !get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['texto']) : $_POST['texto'] ,
			'uidparent'      => $_POST['uidparent'],
			'uidroot'        => $uidroot,
			'img_mimetype'   => '',
			'img_width'      => 0,
			'img_height'     => 0,
			'img_content'    => '',
			'fecha'          => $GLOBALS['DB']->DBTimeStamp( $fecha ) ,
			'tipo'           => $_POST['tipo'],
			'enlace'         => $_POST['enlace'],
			'textolink'      => $_POST['textolink'],
			'textohijas'     => $_POST['textohijas'],
			'template'       => $_POST['template'],
			
			'activa'         => $_POST['activa'],
			'orden'          => $orden,
			'img_align'      => $_POST['img_align'],
			'icono_align'    => $_POST['icono_align'],
			'variables'      => ( !get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['variables']) : $_POST['variables'],
			'descripcion'    => ( !get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['descripcion']): $_POST['descripcion'],
			'idgroup'        => $_POST['idgroup'],
			'iduser'         => $_SESSION['iduser']
			), $uid  );
		$divactivo = $_POST['divactivo'];
		header( "Location: ./admin.php?q=paginas/list/$uidparent" );
		die();
	}
	
	
	

	
	function save_page( $array , $uid ) {
		global $DB, $DB_prefijo, $varsCMS ;
		/*
		    Para altas y para modificaciones ... recibe un array con los datos ... 
		*/
		$DB->Replace( $DB_prefijo . 'paginas ' , 
		    $array, 'uid', true );
		/* ahora añadimos la imagen en si ... si ha sido subida    */
		if (isset($_FILES['imagen']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
		    $path_fichero = dirname( __FILE__ ) . '/../../' . $varsCMS->path_repository  . '/cache.imagen.upload'; 
		    move_uploaded_file ($_FILES['imagen']['tmp_name'] , $path_fichero);
		    $DB->updateblobfile( $DB_prefijo . 'paginas',
			'img_content',
			$path_fichero,
			"uid = {$uid}");
		    unlink( $path_fichero);
		    $DB->Replace( $DB_prefijo . 'paginas ' , 
			array( 
			   'uid'            => $uid,
			   'img_mimetype'   => $_FILES['imagen']['type']
			   ),
			'uid',
			true );
		}
		/*   ahora añadimos icono en si ... si ha sido subido  */
		if (isset($_FILES['icono']) && is_uploaded_file($_FILES['icono']['tmp_name'])) {
		    $path_fichero = dirname( __FILE__ ) . '/../../' . $varsCMS->path_repository  . '/cache.icono.upload'; 
		    move_uploaded_file ($_FILES['icono']['tmp_name'], $path_fichero);
		    $DB->updateblobfile( $DB_prefijo . 'paginas',
			'icono_content',
			$path_fichero,
			"uid = {$uid}");
		    unlink( $path_fichero);
		    $DB->Replace( $DB_prefijo . 'paginas ' , 
			array( 
			   'uid'            => $uid,
			   'icono_mimetype'   => $_FILES['icono']['type']
			   ),
			'uid',
			true );
		}
	
	}
	
	
	
	function edit_ok( $url_retorno = '' ) {
		global $DB, $DB_prefijo ;
		global $varsCMS;
		global $statsCMS;
		// por problemas con el editor visual quitamos el servidor y protocolo:
		$_POST['texto'] = str_replace( "http://{$_SERVER['SERVER_NAME']}", "", $_POST['texto']);
		$_POST['texto'] = str_replace( 
		  dirname( $_SERVER['PHP_SELF'] ) . '/',
		  '', $_POST['texto']);
		       
		
		$_POST['activa'] = (empty($_POST['activa'])) ?  '0' : '1' ;
		$_POST['variables'] = str_replace( "\r", '', $_POST['variables'] );
		if (empty( $_POST['idpagina'])) { 
		    $_POST['idpagina'] = md5( $_POST['titulo'] . $_POST['texto'] . "$orden $uidparent" );
		} else {
			// quitamos caracteres especiales 0.7.0.5 ... si han cambiado el idpagina
			
	
		    if ( $_POST['oldidpagina'] != $_POST['idpagina'] ) {
		    $_POST['idpagina']  = ereg_replace( '[[:blank:]|[:cntrl:]|[\,]]{1,}', '_',  $_POST['idpagina'] );
		    $_POST['idpagina']  = str_replace( '!', '_',   $_POST['idpagina'] );
			// $_POST['idpagina']  = ereg_replace( '[ |[:cntrl:]|[:punct:]]{1,}', '_',  $_POST['idpagina'] );
		    }
		}
		
		$aFecha = explode('/', $_POST['fechamod'] );
		$fecha = (empty( $_POST['fechamod'])) ? time() : mktime(0,0,1, $aFecha[1], $aFecha[0], $aFecha[2])  ;
		
		
		
		$this->save_page( array( 
		       'uid'            => $_POST['uid'],
		       'idpagina'       => $_POST['idpagina'],
		       'titulo'         => ( get_magic_quotes_gpc() == 0 ) ? addslashes($_POST['titulo']) : $_POST['titulo'] ,
		       'texto'          =>  ( get_magic_quotes_gpc() == 0 ) ? addslashes($_POST['texto']) : $_POST['texto'] ,
		       'fecha'          => $GLOBALS['DB']->DBTimeStamp( $fecha ),
		       'tipo'           => $_POST['tipo'],
		       'enlace'         => $_POST['enlace'],
		       'textolink'      => $_POST['textolink'],
		       'textohijas'     => $_POST['textohijas'],
		       'template'       => $_POST['template'],
		       
		       'activa'         => $_POST['activa'],
		       'img_align'      => $_POST['img_align'],
		       'icono_align'    => $_POST['icono_align'],
		       'variables'      => ( get_magic_quotes_gpc() == 0 ) ? addslashes($_POST['variables']) : $_POST['variables'],
		       'descripcion'    => ( get_magic_quotes_gpc() == 0 ) ? addslashes($_POST['descripcion']): $_POST['descripcion'],
		       'idgroup'        => $_POST['idgroup'],
		       'iduser'         => $_SESSION['iduser']
		       ),
		       $_POST['uid']);
		/*
		Vemos el uidparent ... y vamos a su página y div (divactivo)
		*/
		$comando_sql = 'select uidparent from ' . $DB_prefijo . 'paginas ' . 'where uid= ' . $_POST['uid'];
		$recordset = $DB->execute($comando_sql) ;
		$uidparent = $recordset->fields['uidparent'];
		$divactivo = $_POST['divactivo'];
		if (empty( $url_retorno) ) {
		    $url_retorno = "admin.php?q=paginas/list/{$uidparent}";
		}
		/*
		 borramos el objeto serializado ... si existe ... 
		*/
		morcegocms_utils::EmptyCacheObjects();    
		die(  "<script>window.location.href = '{$url_retorno}';</script>" );
	}
	function list_pages() {
		global $idpagina;
		global $tipoboton;
		global $uidparent;
		global $pagina;
		global $DB, $DB_prefijo ;
		global $aArgumentos;
		
		$uidparent = (( isset( $this->parameters[2] ) ? $this->parameters[2] : 0));
		$solo_activas = (isset($_SESSION['filtro']) &&  $_SESSION['filtro']== 'on')?  1 : 0 ;
		$HTML = new HtmlContainer( ) ;
		$Div =& $HTML->add( 'DIV', array( 'class' => 'fleft'));
		$Div->add_object($this->menu_editar_paginas() );
		$oPaginas = new cls_paginas($DB, $DB_prefijo, $uidparent); 
		/*
		Traemos ahora el número de elementos hijos 
		*/
		$comando_sql = "select tipo, activa, count(*) as total from {$DB_prefijo}paginas where uidparent = $uidparent and uid != 0  group by tipo, activa";
		$recordset = $DB->execute( $comando_sql );
		$aHijas = array( 
			'0' => array( '0'=> 0, '1'=>0 ),
			'1' => array( '0'=> 0, '1'=>0 ),
			'2' => array( '0'=> 0, '1'=> 0 )
		);
		while (!$recordset->EOF ) {
			$aHijas["{$recordset->fields['tipo']}"]["{$recordset->fields['activa']}"] = $recordset->fields['total'] ;
			$recordset->MoveNext();
		}
		
		$divSolapas =& $Div->add('div', array('id' => 'divSolapas' ));
		
		$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
		$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Páginas hijas [' .  $aHijas['0']['1'] . '/' . $aHijas['0']['0'] . ']'  ));
		$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
		$div2->add('DIV', array(),$oPaginas->tabla_paginas( $solo_activas , 1  ) ) ;
		
		$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
		$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Contenidos Hijos [' .  $aHijas['1']['1'] . '/' . $aHijas['1']['0'] . ']'  ));
		$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
		$div2->add('DIV', array(),$oPaginas->tabla_contenidos( $solo_activas , 1  ) ) ;
		$this->render( $HTML, 'Administración de Páginas', '' , "",
			new htmlobject( 'script', array (
				'type'      => 'text/javascript',
				'src'       => 'js/elements/pagelist.js') )  );
	}
 
function menu_editar_paginas() {
		global $uidparent, $aArgumentos, $uidparent, $DB, $DB_prefijo, $pagina;
		$tmpuidparent = $uidparent;
		$html = new htmlcontainer();
		$divBase =& $html->add( 'div',array( 'class' => 'div-menu-2' )); 
		$form =& $divBase->add( 'FORM', array(
			'name' => 'filtrar',
			
			'method' => 'post',
			'align' => 'right'));
		$form->add( 'a', array(
			'href' => 'admin.php?q=paginas/buscar'),
			'[Buscar]');
		$form->add_text('&nbsp;&nbsp;');
		$form->add_text( ' Filtro de Páginas: ');
		$select =& $form->add('SELECT', array(
			'name' => 'filtro',
			'onchange' => 'document.filtrar.submit();'));
		if ($_SESSION['filtro'] == 'on') {
			$select->add('option', array(
				'value' => 'on'), 'Mostrar Solo Activas');
			$select->add('option', array(
				'value' => 'off'), 'Mostrar Todas');                    
		} else {
			$select->add('option', array(
				'value' => 'off'), 'Mostrar Todas') ;                   
			$select->add('option', array(
				'value' => 'on'), 'Mostrar Solo Activas');
		}   
		$divBase =& $html->add( 'div',array( 'class' => 'div-linea-2' )); 
		$divBase->add_text('Está en: ');        
		// buscamos el padre ... y otros valores
		$comando_sql = "select uid, activa, idpagina, uidparent, titulo from " . $DB_prefijo . "paginas where uid = $tmpuidparent" ;
		$recordset = $DB->Execute($comando_sql) ;
		$activa = $recordset->fields['activa'];
		$tmpuidparent = $recordset->fields['uidparent'];
		$TmpArray = array(  array( $recordset->fields['uid'], $recordset->fields['idpagina'], $recordset->fields['titulo'])    );
		while ($tmpuidparent  != 0) {
			/*
			Creamos un array con los descendientes de la página actual
			*/
			$comando_sql = "select uid, idpagina, uidparent, titulo from " . $DB_prefijo . "paginas where uid = $tmpuidparent" ;
			$recordset = $DB->Execute($comando_sql) ;
			$TmpArray[] = array( $recordset->fields['uid'], $recordset->fields['idpagina'], $recordset->fields['titulo'] );
			$tmpuidparent =& $recordset->fields['uidparent'];
		}
		if ($uidparent  != 0) {
			$TmpArray[] = array( 0, 'index',  'Inicio' );
		}
		for ( $i = ( count( $TmpArray) - 1 ); $i > 0 ; $i-- ) {
			$div =& $divBase->add('span', array('class' => 'fbold' )  );
			$div->add( 'a', array(
				'href' => 'admin.php?q=paginas/list/' . $TmpArray[ $i ][0],
				'title' => 'Ir a la página ' .  '[' . $TmpArray[ $i ][1] . '] ' ,
				'class' => 'lista'), /*'[' . $TmpArray[ $i ][1] . '] ' .  */$TmpArray[ $i ][2] . ' '  );
			$div->add_text(  ' &raquo; ' );           
		}
		$div =& $divBase->add('span', array('class' => 'fbold fmedium' )  );
		$div->add_text( '[' . $TmpArray[ $i ][1] . '] ' . $TmpArray[ $i ][2] . '&nbsp;&nbsp;' );
		$div->add_text(  '&nbsp;' );           
		$div->add_object( CustomHTML::Boton16x16( 
			_ADM_BOTON_EDITAR , 
			"admin.php?q=paginas/edit/{$TmpArray[ $i ][0]}",  
			'Editar Página', '', '', false  ) );
		if ( $activa == 1) {
			$div->add_text( '&nbsp;');
			$div->add_object( CustomHTML::boton16x16( 
				_ADM_BOTON_VISUALIZAR , 
				"/?{$TmpArray[ $i ][1]}",  
				'Ver en el navegador',
				'visualizar',
				false,
				'top') );
		}
		$div->add_object( cls_Paginas::div_idiomas( $TmpArray[ $i ][0] , false )); 
		$html->add( 'br' );
		$html->add( 'div', array( 'class'=>"clear"));
		// menu para mover (orden de las páginas  )
		
//		$html->add_object( customHTML::divMover() );
		// menu opciones para páginas
		//$html->add('script', '', "Element.hide( $( 'divMenuPagina' ));Element.hide( $( 'divmover' ));" );
		return $html ;
	} 
 
	function buscar() {
		$HTML  = new htmlcontainer();
		$array_tipos = array( 
			'0'=> 'Página',
			'1'=> 'Contenido',
			'2' => 'Noticia');
		$div =& $HTML->add( 'div', '', '');
		$form =& $div->add( 'form', array(
			'method'        => 'post',
			'action'        => 'admin.php?q=paginas/buscar'));
		$form->add_text( 'Cadena: ' );
		$form->add('input', array(
			'type' => 'text',
			'name' => 'cadena_busqueda',
			'value' =>  isset( $_POST['cadena_busqueda']) ? htmlentities( stripslashes( $_POST['cadena_busqueda']) ) : '' ));
		$form->add_text('&nbsp;');
		$form->add( 'input', array(
			'type' => 'submit',
			'value' => 'buscar'));
		$form->add('i', '', ' Se buscará la cadena en el campo TEXTO de la página' );
		if ( isset( $_POST['cadena_busqueda'] ) && !empty($_POST['cadena_busqueda'])) {
			$resultado =& $div->add('div');
			$resultado->add( 'h2', '', 'Resultado de la búsqueda');
			$comando_sql = "select idpagina, uid, uidparent, titulo, activa, tipo from {$GLOBALS['DB_prefijo']}paginas where texto like \"%" 
				. addslashes( $_POST['cadena_busqueda']) . "%\"";
			$recordset = $GLOBALS['DB']->execute( $comando_sql );
			$AttrTable =  array(  'cellpadding' => '2', 'cellspacing' => '0', 'class' => 'ruler', 'width' => '700');
			$tabla =& $resultado->add( 'TABLE',$AttrTable );
			$tr =& $tabla->add( 'TR');
			$tr->add( 'TH', '', 'Tipo');
			$tr->add( 'TH', '', '&nbsp;');
			$tr->add( 'TH', array( 'class' => 'fleft'), '&nbsp;[idpagina] Título');
			while (!$recordset->EOF) {
				if ($recordset->fields['activa'] == 1) {
					$activa_css = 'paginaOn';
				} else {
					$activa_css = 'paginaOff';
				}
				$tr =& $tabla->add('TR') ;
				$td =& $tr->add( 'TD');
				$td->add_text( $array_tipos[ "{$recordset->fields['tipo']}" ]);
				$td =& $tr->add( 'TD');
				$td->add('A', array(
					'class' => 'miniboton',
					'href' => "admin.php?q=paginas/edit/{$recordset->fields['uid']}",
					'title' => 'Editar Página !!! ',
					'style' => 'Layout: Block' ), 'EDITAR' );
				$td =& $tr->add( 'TD', array(
					'class' => 'fleft' ));
				$a =& $td->add('A', array( 
					'class' => $activa_css,
					'href' => "admin.php?q=paginas/list/{$recordset->fields['uid']}"), "[{$recordset->fields['idpagina']}]" );
				$a->add('STRONG', '', " {$recordset->fields['titulo']}");
				$recordset->MoveNext();
			}
		}
		$this->render( $HTML, 'Buscador de Páginas', '' );
	}

 
	function editar( ) {
		
		$uid = (int)  $this->parameters[2];
		$comando_sql = "select idpagina from " . $GLOBALS['DB_prefijo'] . "paginas where uid=$uid";
		$recordset = $GLOBALS['DB']->execute($comando_sql) ;
		/*
		creamos un objeto con los datos de la página ... se pasará al formulario como
		unico parámetro
		*/
		$GLOBALS['statsCMS']->vars['lang'] = '';
		$objpagina2 = new pagina ($recordset->fields['idpagina']);
		
		$head = new htmlcontainer();
		$head->add(  html_admin::js(  'js/elements/pagesedt.js' ) ) ;
		
		
		$this->render( $this->form_edit('edit', $objpagina2) ,
			'Modificación de Páginas - Modificando: ['  
			. morcegocms_utils::idpaginafromuid( $uid ) . ']', '', null, $head );

	}
 
	function add_lang() {
		$uid = (int)  $this->parameters[2];
		$comando_sql = "select idpagina from " . $GLOBALS['DB_prefijo'] . "paginas where uid=$uid";
		$recordset = $GLOBALS['DB']->execute($comando_sql) ;
		/*
		creamos un objeto con los datos de la página ... se pasará al formulario como
		unico parámetro
		*/
		$objpagina2 = new pagina ($recordset->fields['idpagina']);
		$head = new htmlcontainer();
		$head->add(  html_admin::js(  'js/elements/pagesedt.js' ) ) ;
		echo $this->render( $this->form_edit('add_lang', $objpagina2) ,
			'Creando página en otro idioma de: ['  
			. morcegocms_utils::idpaginafromuid( $uid ) . ']', '', null, $head );
		die();
	} 

	function edt_lang() {
		$uid = (int)  $this->parameters[2];
		$comando_sql = "select idpagina from " . $GLOBALS['DB_prefijo'] . "paginas where uid=$uid";
		$recordset = $GLOBALS['DB']->execute($comando_sql) ;
		/*      
		creamos un objeto con los datos de la página ... se pasará al formulario como
		unico parámetro
		*/
		$GLOBALS['statsCMS']->vars['lang'] =  $this->parameters[3];
		
		$objpagina2 = new pagina ($recordset->fields['idpagina']);
		$head = new htmlcontainer();
		$head->add(  html_admin::js(  'js/elements/pagesedt.js' ) ) ;
		echo $this->render( $this->form_edit('edt_lang', $objpagina2) ,
			'Editando idioma de: ['  
			. morcegocms_utils::idpaginafromuid( $uid ) . ']', '', null, $head );
		die();
	}

	function edt_lang_ok( $url_retorno = '' ) {
		global $DB, $DB_prefijo ;
		global $varsCMS;
		global $statsCMS;
		// por problemas con el editor visual quitamos el servidor y protocolo:
		$_POST['texto'] = str_replace( "http://{$_SERVER['SERVER_NAME']}", "", $_POST['texto']);
		$_POST['activa'] = (empty($_POST['activa'])) ?  '0' : '1' ;
		$_POST['variables'] = str_replace( "\r", '', $_POST['variables'] );
		$aFecha = explode('/', $_POST['fechamod'] );
		$fecha = (empty( $_POST['fechamod'])) ? time() : mktime(0,0,1, $aFecha[1], $aFecha[0], $aFecha[2])  ;
		$GLOBALS['DB']->Replace( $GLOBALS['DB_prefijo'] . 'paginas_lang' , 
			array( 
				'uid'            => $_POST['uid'],
				'lang'           => $_POST['lang'],
				'idpagina'       => $_POST['oldidpagina'],
				'titulo'         => ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['titulo']) : $_POST['titulo'] ,
				'texto'          =>  ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['texto']) : $_POST['texto'] ,
				'fecha'          => $GLOBALS['DB']->DBTimeStamp( $fecha ),
				// 'tipo'           => $_POST['tipo'],
				'enlace'         => $_POST['enlace'],
				'textolink'      => $_POST['textolink'],
				'textohijas'     => $_POST['textohijas'],
				'template'       => $_POST['template'],
				
				// 'activa'         => $_POST['activa'],
				// 'img_align'      => $_POST['img_align'],
				// 'icono_align'    => $_POST['icono_align'],
				'variables'      => ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['variables']) : $_POST['variables'],
				'descripcion'    => ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['descripcion']): $_POST['descripcion'],
				// 'idgroup'        => $_POST['idgroup'],
				'iduser'         => $_SESSION['iduser']
			),
			array( 'uid', 'lang'),
			true );
		/*
		Vemos el uidparent ... y vamos a su página y div (divactivo)
		*/
		$comando_sql = 'select uidparent from ' . $DB_prefijo . 'paginas ' . 'where uid= ' . $_POST['uid'];
		$recordset = $DB->execute($comando_sql) ;
		$uidparent = $recordset->fields['uidparent'];
		$divactivo = $_POST['divactivo'];
		if (empty( $url_retorno) ) {
			$url_retorno = "admin.php?q=paginas/list/{$uidparent}";
		}
		/*
		borramos el objeto serializado ... si existe ... 
		*/
		morcegocms_utils::EmptyCacheObjects();    
		header( 'Location: ' . $url_retorno ) ;
		die();	
	}


	function add_lang_ok( $url_retorno = '' ) {
		global $DB, $DB_prefijo ;
		global $varsCMS;
		global $statsCMS;
		// por problemas con el editor visual quitamos el servidor y protocolo:
		$_POST['texto'] = str_replace( "http://{$_SERVER['SERVER_NAME']}", "", $_POST['texto']);
		$_POST['activa'] = (empty($_POST['activa'])) ?  '0' : '1' ;
		$_POST['variables'] = str_replace( "\r", '', $_POST['variables'] );
		$aFecha = explode('/', $_POST['fechamod'] );
		$fecha = (empty( $_POST['fechamod'])) ? time() : mktime(0,0,1, $aFecha[1], $aFecha[0], $aFecha[2])  ;
		// die ( get_magic_quotes_gpc()  . '<br>'. $_POST['texto'] ) ;
		$GLOBALS['DB']->Replace( $GLOBALS['DB_prefijo'] . 'paginas_lang' , 
			array( 
			'uid'            => $_POST['uid'],
			'lang'           => $_POST['lang'],
			'idpagina'       => $_POST['oldidpagina'],
			'titulo'         => ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['titulo']) : $_POST['titulo'] ,
			'texto'          =>  ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['texto']) : $_POST['texto'] ,
			'fecha'          => $GLOBALS['DB']->DBTimeStamp( $fecha ),
			// 'tipo'           => $_POST['tipo'],
			'enlace'         => $_POST['enlace'],
			'textolink'      => $_POST['textolink'],
			'textohijas'     => $_POST['textohijas'],
			'template'       => $_POST['template'],
			
			// 'activa'         => $_POST['activa'],
			// 'img_align'      => $_POST['img_align'],
			// 'icono_align'    => $_POST['icono_align'],
			'variables'      => ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['variables']) : $_POST['variables'],
			'descripcion'    => ( get_magic_quotes_gpc() == 0 ) ? stripslashes($_POST['descripcion']): $_POST['descripcion'],
			// 'idgroup'        => $_POST['idgroup'],
			'iduser'         => $_SESSION['iduser']
			),
			array( 'uid', 'lang'),
			true );
		/*
		Vemos el uidparent ... y vamos a su página y div (divactivo)
		*/
		$comando_sql = 'select uidparent from ' . $DB_prefijo . 'paginas ' . 'where uid= ' . $_POST['uid'];
		$recordset = $DB->execute($comando_sql) ;
		$uidparent = $recordset->fields['uidparent'];
		$divactivo = $_POST['divactivo'];
		if (empty( $url_retorno) ) {
			$url_retorno = "admin.php?q=paginas/list/{$uidparent}";
		}
		/*
		borramos el objeto serializado ... si existe ... 
		*/
		morcegocms_utils::EmptyCacheObjects();    
		header( 'Location: ' . $url_retorno ) ;
		die();
	}


	function form_edit($accion = 'add', $objpagina = 0) {
		
		      global $aArgumentos, $tipoboton, $pagina;
		      
		      $uidparent = $this->parameters[2];
		      include_once( 'fckeditor/fckeditor.php');
		      $sBasePath = $_SERVER['PHP_SELF'] ;
		      $sBasePath = substr( $sBasePath, 0, strpos( $sBasePath, "admin.php" ) ) ;
		      $oFCKeditor = new FCKeditor('texto') ;
		      $oFCKeditor->BasePath	= $sBasePath  . 'fckeditor/';
		      $oFCKeditor->Height		= '450' ;
		      $botones  = new htmlcontainer();
		      $botones_icono = $botones;
		      $botones_imagen = $botones;
			$str_out = '';
		    // establecemos el titulo
			/* indica el div activo */
			if (isset($aArgumentos[2])) $divactivo = $aArgumentos[2];
			else $divactivo = '';
			/* ponemos en $tipo el tipo de página */
			
			 /* establecemos el título */
			
			switch ($accion) {
			    case 'add':
				$template = $this->templateparent( $uidparent);
				$oldIdpagina = '';
				
				break;
			    case 'add_lang':
				$template = $objpagina->Campos['template'];
				$oldIdpagina = $objpagina->Campos['idpagina'];
				$idiomas_existentes = array();
				$sql = "select distinct lang from {$GLOBALS['DB_prefijo']}paginas_lang " .
				    "where uid = {$objpagina->Campos['uid']} order by lang";
				$recordset = $GLOBALS['DB']->execute($sql);
				$idiomas_existentes[] = $GLOBALS['configCMS'] -> get_var('lang');
				while (!$recordset->EOF) {
				    $idiomas_existentes[] = $recordset->fields['lang'] ;
				    $recordset->MoveNext();
				}
				break;
			    case 'edt_lang':
				$template = $objpagina->Campos['template'];
				$oldIdpagina = $objpagina->Campos['idpagina'];
				break;                
			    case 'edit':
				$template = $objpagina->Campos['template'];
				$oldIdpagina = $objpagina->Campos['idpagina'];
				break;
			}
			
			if (gettype($objpagina)  != 'object') {
			    /* En un alta el tipo nos viene como 3er parametro*/
			    $control_idgroup = comboAdmin::select_idgroup( $this->idgroupparent( $uidparent), 'idgroup', ($accion == 'add_lang' || $accion == 'edt_lang' ));
			    switch ($aArgumentos[2]) {
				case 'hijas':
				$tipo = 0;
				    /*
				    Ahora traemos una lista de templates indicando prefijo, valor, nombre del control
				    */
				    $control_templates = comboadmin::select_template('template_', $template, 'template');
				    break;
				case 'contenidos':
				    $tipo = 1;
				    $control_templates = comboadmin::select_template('content_', $template, 'template');              
				    break;
				      
				default:
				    $tipo = 0;
				    $control_templates = comboadmin::select_template('template_', $template, 'template');          
			    }
			} else {
			    $control_idgroup = comboAdmin::select_idgroup( $objpagina->Campos['idgroup'], 'idgroup', ($accion == 'add_lang' || $accion == 'edt_lang' ));
			    $tipo = $objpagina->Campos['tipo'];
				switch ($tipo) {
				    case 0:
					$control_templates = comboadmin::select_template('template_', $template, 'template');
					break;
				    case 1:
					$control_templates = comboadmin::select_template('content_', $template, 'template');
					break;
				    case 2:
					$control_templates = comboadmin::select_template('template_', $template, 'template');
					break;
				    default:
					$control_templates = comboadmin::select_template('template_', $template, 'template');
					break;
				}
			    
			}
			
			// creamos los botone de ver y borrar si existe el ICONO
		  
		
			$botones_pie =  new htmlContainer();
			$botones_pie->add( CustomHTML::botonAdmin("Grabar y Salir" , '', '#', 'check_edtpagina( document.formulario, document.formulario.oldidpagina.value, document.formulario.idpagina.value);' ));
			
			if ($accion != 'add' && $accion != 'add_lang' && $accion != 'edt_lang'   ) {
				$botones_pie->add( CustomHTML::botonAdmin("Grabar y Continuar" , 'Graba y continua la edición', '#', "document.formulario.accion.value='saveandcontinue';check_edtpagina( document.formulario, document.formulario.oldidpagina.value, document.formulario.idpagina.value);")) ;
			}
			if ($accion  != 'add') {
				$uidparent = $objpagina->Campos['uidparent'];
			}
			$botones_pie->add(CustomHTML::botonAdmin("Cancelar" , 'Cancela la edición y sale', '#', "confirmar('¿ Desea cancelar la Edición y salir (se perderán los cambios efectuados) ?', './admin.php?q=paginas/list/{$uidparent}')"));
				
			
			$HTML = new HtmlContainer( ) ;
			
			$div =& $HTML->add( 'DIV', array( 'class' => 'fleft'));
		       $div->add('script', array(
			    'src'       => 'fckeditor/fckeditor.js'));

			// JsCalendar 
			      
			$div->add('script', array(
			    'src'       => 'js/calendar.js'));
			$div->add('script', array(
			    'src'       => 'js/calendar-es.js'));      
			$div->add('script', array(
			    'src'       => 'js/calendar-setup.js'));            
		
			$form =& $div->add( 'FORM', array(
			    'name' => 'formulario',
			    'method' => 'post',
			    'action'        => 'admin.php?q=paginas/form',
			    'enctype' => 'multipart/form-data'));
			$form->add( 'INPUT', array( 'type' => 'hidden','name' => 'accion', 'value' => $accion ));
			$form->add( 'INPUT', array( 'type' => 'hidden','name' => 'uidparent', 'value' => $uidparent));
			$form->add( 'INPUT', array( 'type' => 'hidden','name' => 'divactivo', 'value' => $divactivo ));
			$form->add( 'INPUT', array( 'type' => 'hidden','name' => 'uid', 'value' => $objpagina->Campos['uid'] ));
			$form->add( 'INPUT', array( 'type' => 'hidden','name' => 'oldidpagina', 'value' => $oldIdpagina ));
			
			/*
			Ponemos los botones para acceder a los distintos divs
			
			DIV de datos generales de la página
			*/
			//$divSolapas =& $form->add('div', array('class' => 'solapas' ));
			
			
			
			
			$div_titulo =& $form->add('div', array(
			    'class' => 'menuEditPages'));
			
			$div_titulo ->add(  $botones_pie );
			
		
			$divSolapas =& $form->add( 'div', array( 'id' => 'divSolapas' ));
			/*
			$ulLista =& $divSolapas->add( 'ul', array( 'class' => 'yui-nav' ));
			$li =& $ulLista->add( 'li');
			$li->add( 'a', array('href' =>'#contenido1'), null, new htmlobject( 'em', array(),   'Datos de la página' )); 
			$li =& $ulLista->add( 'li');
			$li->add( 'a', array('href' =>'#contenido2'), null, new htmlobject( 'em', array(),   'Texto de la página' ));
			*/
			
			
			

			$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
			$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Datos de la Página' ));
			$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
			$elemento =& $div2->add( 'div', array( 
			  'class' => 'bloque' ));
			
			
			 if( $accion == 'add_lang' || $accion == 'edt_lang' ) {
			   $elemento->add( 'label', array( 
			    'for' => 'lang' ), 'Idioma: ');
			   if  ( $accion == 'edt_lang' ) {
			     $elemento->add( 'input', array(
				'name' => 'lang',
				'type' => 'hidden',
				'value' =>  $GLOBALS['statsCMS']->vars['lang']));
				$idiomas = morcegocms_lang::get_array_lang();
			      $elemento->add_text( '[' . $idiomas[ $GLOBALS['statsCMS']->vars['lang'] ] . '] &nbsp;&nbsp;' );
			      $elemento->add( 'a', array( 
				'href'=>'?del_lang/' . $objpagina->Campos['uid'] . '/' . $GLOBALS['statsCMS']->vars['lang'],
				'title' => 'Borrar idioma  ' . $idiomas[ $GLOBALS['statsCMS']->vars['lang'] ] 
				),
				'[ Borrar ]' );
				    
				    
				    
				} else {
				    $elemento->add_object( $this->select_idioma( '',  'lang', $idiomas_existentes ) );
				}
			$elemento->add( 'br');
			}
			
			
			
			
			$elemento->add( 'label', array( 
			  'for' => 'tipo' ), 'Tipo: ');
			$elemento->add_text(  $this->select_tipo($tipo, 'tipo', ($accion == 'add_lang' || $accion == 'edt_lang' ) ? true : false )  );
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'fechamod' ), 'Fecha: ');
			
			$elemento->add( 'input', array( 
				'type'          => 'text',
				'name'          => 'fechamod',
				'id'          => 'fechamod',
				'value'         => ($accion === 'add') ? date('d/m/Y') : date( 'd/m/Y',  $objpagina->Campos['fecha']) ,
				'readonly'      => true
				));
		
			$elemento->add_text(" ");
			$elemento->add( 'button', array( 
			 'type'          => 'reset',
			'name'          => 'btnfechamod',
			'id'          => 'btnfechamod'), '...');
			
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'template' ), 'Plantilla: ');
			$elemento->add_object( $control_templates );
			
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'idpagina' ), 'ID de Página: ');
			$elemento->add( 'input', array( 
				'type'          => 'text',
				'id'          => 'idpagina',
				'name'          => 'idpagina',
				'value'         => $objpagina->Campos['idpagina'],
				'readonly' => ( $accion == 'add_lang' || $accion == 'edt_lang'  ) ,
				'disabled' => ( $accion == 'add_lang' || $accion == 'edt_lang'  ) 
				));
			
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'titulo' ), 'Título: ');
			$elemento->add( 'input', array( 
				'type'          => 'text',
				'name'          => 'titulo',
				'id'          => 'titulo',
				'value'         =>  htmlentities (  $objpagina->Campos['titulo'] ) 
				));        
			
		
			if ( $accion == 'add' ) {
				$elemento->add( 'br');
				$elemento->add( 'label', array( 
					'for' => 'orden' ), 'Orden: ');
				$elemento->add_object( comboadmin::select_orden_nueva_pagina( 'orden') );
			
			}
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'descripcion' ), 'Descripción: ');
			$elemento->add( 'textarea', array (
				'rows'          => 5,
				'name'          => 'descripcion',
				'id'          => 'descripcion'
				),
				$objpagina->Campos['descripcion'] 
				);
			
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'idgroup' ), 'Visibilidad: ');
			$elemento->add_object( $control_idgroup );
			
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			  'for' => 'activa' ), 'Activa: ');
			$elemento->add( 'input', array(
				'type'  => 'checkbox',
				'name'  => 'activa',
				'id'  => 'activa',
				'readonly' => ( $accion == 'add_lang' || $accion == 'edt_lang'  ) ,
				'disabled' => ( $accion == 'add_lang' || $accion == 'edt_lang' ) ,
				'checked'  => ($objpagina->Campos['activa'] == 1) ? 'checked' : false ));
			if  ($accion != 'add_lang' && $accion != 'edt_lang' ) { 
			  $elemento->add( 'br');
			  $elemento->add( 'label', array( 
			    'for' => 'imagen' ), 'Imagen: ');
			  $elemento->add( 'input', array ( 'name' => 'imagen', 'id' =>'imagen', 'type' => 'file' ));
			  $elemento->add( 'br');
			  $elemento->add( 'label', array( 
			    'for' => 'img_align' ), 'Alineación Imagen: ');
			  $elemento->add_text( $this->select_align($objpagina->Campos['img_align'], 'img_align') );
			  if (!empty($objpagina->Campos['img_mimetype'])) {
			      $elemento->add_object( 
				  CustomHTML::botonadmin("Ver" , 'Visualizar la imagen', '#', 
				  "PopUp('imagen.php?imagen/{$objpagina->Campos['idpagina']}', 50, 50, 'imagen')") );
			      
			      $elemento->add_object( 
				  CustomHTML::botonadmin("Borrar" , 'Borrar la imagen', '#', 
				  "confirmar( '¿ Desea Realmente borrar la imagen asociada a esta página ?', 'admin.php?q=paginas/delimage/{$objpagina->Campos['uid']}' );") );
			  }
			  $elemento->add( 'br');
			  $elemento->add( 'label', array( 
			    'for' => 'icono' ), 'Icono: ');
			  $elemento->add( 'input', array ( 'name' => 'icono',  'id' => 'icono',  'type' => 'file' ));
			
			 $elemento->add( 'br');
			  $elemento->add( 'label', array( 
			    'for' => 'icn_align' ), 'Alineación Icono: ');  
			  $elemento->add_text( $this->select_align($objpagina->Campos['icono_align'], 'icono_align') );
			  if (!empty($objpagina->Campos['icono_mimetype'])) {
			       $elemento->add_object(
				    CustomHTML::botonadmin("Ver" , 'Visualizar el Icono', '#', "PopUp('imagen.php?icono/{$objpagina->Campos['idpagina']}', 50, 50, 'imagen')"));
				
				$elemento->add_object( 
				    CustomHTML::botonadmin("Borrar" , 'Borrar el Icono', '#', "confirmar( '¿ Desea Realmente borrar el icono asociado a esta página ?', 'admin.php?q=paginas/delicono/{$objpagina->Campos['uid']}' );")) ;
			  }                
			  
			}
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			   'for' => 'textohijas' ), 'Texto Hijas: ');        
			$elemento->add( 'input', array( 
				'type'          => 'text',
				'name'          => 'textohijas',
				'id'          => 'textohijas',
				'value'         =>  ($accion === 'add') ? 'Secciones' :$objpagina->Campos['textohijas']
				));        
		
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			   'for' => 'enlace' ), 'URL Enlace: ');        
			$elemento->add( 'input', array( 
			  'type'          => 'text',
			  'name'          => 'enlace',
			  'id'          => 'enlace',
			  'value'         => $objpagina->Campos['enlace']));        
  			$elemento->add( 'br');

			$elemento->add( 'label', array( 
			   'for' => 'textolink' ), 'Texto Enlace: ');  			   
			$elemento->add( 'input', array( 
			  'type'          => 'text',
			  'name'          => 'textolink',
			  'id'          => 'textolink',
			  'value'         => $objpagina->Campos['textolink'] ));        
			
			$elemento->add( 'br');
			$elemento->add( 'label', array( 
			   'for' => 'variables' ), 'Variables: ');        
			$div2->add( 'textarea', array(
			    'name'      => 'variables',
			    'id'      => 'variables'
			    ),
			    htmlentities ( $objpagina->Campos['variables'] ) ) ;
		
		
			// $table =& $div2->add( 'table', array( 'border' => '0', 'cellpadding' => '0', 'cellspacing' => '0'));
			$divSolapa =& $divSolapas->add('DIV', array( 'class'  => 'solapa') );
			$divSolapa->add( 'div', array('class' => 'tituloSolapa'), null, new htmlobject('h3',null, 'Texto de la Página' ));
			$div2 =& $divSolapa->add( 'div', array('class' => 'contenidoSolapa' ));
			/*
			$div2->add( 'br');
			$div2->add_text( 'Texto de la Página'  ) ;
			$div2->add_text(  ico_ayuda_pagina( 'texto' ) ) ;
			$div2->add_text( ' ' . boton_ampliar_full( 'formulario.texto', 'Texto de la Página', true) );
			*/
			
			
			$div3 =& $div2->add('div', array( 
			    'name' => 'divmenutexto',
			    'id' => 'divmenutexto',
			    'class' => 'divmenutexto'
			    ));
			$divenlace =& $div3->add( 'div');
			$divenlace->add_object( 
				new htmlobject( 'a', 
					array( 
					'class'=>'aRTF',
					'href' =>    "javascript:initRTF('" . $sBasePath  . 'fckeditor/' . "');" 
				), 'Editor Visual'));
			    
				
			$div2->add( 'textarea', array(
			    'name'      => 'texto',
			    'id'        => 'texto',
			    'rows'      => 10,
			    'cols'      => 60,
			    ),
			    htmlentities ( $objpagina->Campos['texto'] )
			    );
		    
		    $div->add( 'script', '' , "
		      oFCKeditor = new FCKeditor( 'texto' ) ;
		      oFCKeditor.BasePath	= '" . $sBasePath  . 'fckeditor/' . "'  ;
		      oFCKeditor.UserFilesPath = '/imagenes-2' ; 
		
		      " );
		
			
			$idurl = 'pgedicion';
			
			$div->add( 'script', '', "
			Calendar.setup({
			inputField     :    \"fechamod\",      // id of the input field
			ifFormat       :    \"%d/%m/%Y\",       // format of the input field
			showsTime      :    false,            // will display a time selector
			button         :    \"btnfechamod\",   // trigger for the calendar (button ID)
			singleClick    :    false            // double-click mode
			}); ");
		
			
			return  $div;
    }
 
 function select_tipo( $tipo = '', $control='tipo' , $readonly = false ) {
    $html = new htmlcontainer();
    $aValores = array( 
        '0' =>  'Página',
        '1' =>  'Contenido' /* ,
        '2' => 'Noticia'  */);
    // if ( $readonly == false  ) {
            
        $select =& $html->add( 'select', array (
            'name' => $control,
	    'id' => $control,
            'class' => 'form',
            'onchange' => 'get_templates( this.value); ',
            'readonly'  =>  $readonly, 
            'disabled' => $readonly));
        
        foreach ( $aValores as $clave => $valor  ) {
            $select->add( 'option', array( 
                'selected' =>  ($tipo == $clave) ? true : false ,
                'value' => $clave ),
                $valor);
        }
/*        
    } else {
        $html->add_text( '[' . $aValores[ $tipo ] . ']' );
    }
*/
return $html->render();
}

 function select_align( $alineado = '', $control='img_align' ) {
$strout = "<select name=\"$control\" id=\"{$control}\">"; // cadena de salida
$aValores = array (
   array( 'Derecha','right'),
   array( 'Izquierda', 'left'),
   array( 'Arriba', 'top'),
   array( 'Encima del texto', 'texttop'),
   array( 'Centro', 'middle'),
   array( 'Centro Absoluto', 'absolutemiddle'),
   array( 'baseline', 'baseline'),
   array( 'Arriba', 'top'),
   array( 'Abajo', 'bottom'),
   array( 'Abajo absoluto', 'absbottom') );
foreach ( $aValores as $aValor ) {

   if ( $alineado == $aValor[1] ){
         $selected = 'selected';
   } else {
         $selected = '';
   }
  $strout .=  "<option value=\"$aValor[1]\" $selected>$aValor[0]</option>\n";
}
$strout .= '</select>';
return $strout;
}


    

 
 
	/**
	* Borra el icono de una determinada página.
	*/
	function borraricono() {
		global $aArgumentos, $pagina, $tipoboton, $DB, $DB_prefijo;
		$uid = $this->parameters[1];
		$DB->Replace($GLOBALS['configCMS'] -> get_var('dbprefijo') . 'paginas ' , 
		array( 
		'uid' => $uid,
		'fecha' => $GLOBALS['DB']->DBTimeStamp( time()) ,
		'icono_mimetype' => '',
		'icono_content' => '',
		'iduser' => $_SESSION['iduser']
		),
		'uid',
		true    );
		morcegocms_utils::EmptyCacheObjects();    
		header( 'Location: admin.php?q=paginas/edit/' . $uid ) ;
	
	}
 
 
 function cambiar_orden() {
      $uid = $this->parameters[2];
      $cambio = $this->parameters[3];
      
      /*      Valores posibles de cambio: top, up, down, bottom.     */
      /*      Determinamos el orden actual de la página       */
      
      $SQL = "select uidparent, orden, tipo from {$GLOBALS['DB_prefijo']}paginas where uid={$uid}";
      $rs = $GLOBALS['DB']->execute( $SQL ) ;
      
      $uidparent = $rs->fields['uidparent'];
      $orden     = $rs->fields['orden'];
      $tipo      = $rs->fields['tipo'];
      
      $cambioOrden = false ;
      
      switch ( $cambio)  {
        case 'top':
          $cambioOrden = "-" . ( $orden - 1 ) ;
          break;
        case 'up':
          $cambioOrden = "-1" ;
          break;
        case 'down':
          $cambioOrden = "+1" ;
          break;
        case 'bottom':
          $SQL = "select max(orden) as orden from {$GLOBALS['DB_prefijo']}paginas where ". 
            "uidparent = {$uidparent} and tipo = {$tipo}" ;
          $rs = $GLOBALS['DB']->execute( $SQL );
          $cambioOrden = "+" . $rs->fields['orden'] - $orden ;
          break ;
      }

      
      if( ( (int) $cambioOrden ) >  0 ) {
        $nuevoOrden = $orden + ( (int) $cambioOrden ) ; 
        $comandoSQL = "update {$GLOBALS['DB_prefijo']}paginas ".
          "set orden = 0 " .
          " where uidparent = {$uidparent}  and tipo = {$tipo} and orden = {$orden} ";
        $GLOBALS['DB']->execute( $comandoSQL );
        $comandoSQL = "update {$GLOBALS['DB_prefijo']}paginas ".
          "set orden = orden - 1  " .
          " where uidparent = {$uidparent}  and tipo = {$tipo}  and orden > {$orden} and orden <= {$nuevoOrden} ";
        $GLOBALS['DB']->execute( $comandoSQL );
        $comandoSQL = "update {$GLOBALS['DB_prefijo']}paginas ".
          "set orden = {$nuevoOrden} " .
          " where uidparent = {$uidparent}  and tipo = {$tipo} and orden = 0 ";
        $GLOBALS['DB']->execute( $comandoSQL );
        
      } elseif ( ( (int) $cambioOrden ) < 0 ) {
      
        $nuevoOrden = $orden + ( (int) $cambioOrden ) ; 
        
        
        $comandoSQL = "update {$GLOBALS['DB_prefijo']}paginas ".
          "set orden = 0 " .
          " where uidparent = {$uidparent}  and tipo = {$tipo} and orden = {$orden} ";
        $GLOBALS['DB']->execute( $comandoSQL );
        
        
        $comandoSQL = "update {$GLOBALS['DB_prefijo']}paginas ".
          "set orden = orden + 1  " .
          " where uidparent = {$uidparent}  and tipo = {$tipo}  and orden < {$orden} and orden >= {$nuevoOrden} ";
        $GLOBALS['DB']->execute( $comandoSQL );
        $comandoSQL = "update {$GLOBALS['DB_prefijo']}paginas ".
          "set orden = {$nuevoOrden} " .
          " where uidparent = {$uidparent}  and tipo = {$tipo} and orden = 0 ";
        $GLOBALS['DB']->execute( $comandoSQL );
      
      }
      
      header( 'Location: ' . 'admin.php?q=paginas/list/' . $uidparent );
      die();
    }
     
 
 
	/**
	*
	* Borra la imagen de una determinada página.
	*/
	function borrarimagen() {
		global $aArgumentos, $pagina, $tipoboton, $DB, $DB_prefijo;
		$uid = $this->parameters[1];
		$DB->Replace($GLOBALS['configCMS'] -> get_var('dbprefijo') . 'paginas ' , 
			array( 
			'uid' => $uid,
			'fecha' => $GLOBALS['DB']->DBTimeStamp( time()) ,
			'img_mimetype' => '',
			'img_height' => 0,
			'img_width' => 0,
			'img_content' => '',
			'iduser' => $_SESSION['iduser']
			),
			'uid',
			true    );
		morcegocms_utils::EmptyCacheObjects();
		header( 'Location: admin.php?q=paginas/edit/' . $uid ) ;
	}
 
 
 
 function arbol_paginas() {
    
    $HTML = new HtmlContainer( ) ;
    
    $div =& $HTML->add('div', array(
        'style' => 'float: right; padding-right: 5px; text-align: right;'
        ) );
        $div->add( 'strong','', 'Leyenda :: ');
        $div->add( 'I', array(
            'style'         => 'text-decoration: line-through'),
            'Nombre');
        $div->add('','', ' = Inactiva || ');
        $div->add('img', array( 
            'src'           => 'images/closed.gif',
            'border'        => '0'));
        $div->add_text( ' ó ');
        $div->add('img', array( 
            'src'           => 'images/open.gif',
            'border'        => '0'));
        $div->add('','', ' = Página || ');
        $div->add('img', array( 
            'src'           => 'images/doc.gif',
            'border'        => '0'));
        $div->add('','', ' = Contenido ');
    $HTML->add( 'br');
    $HTML->add_object( $this->lista_paginas_hijas( 0, 1) );
    die( $this->render( $HTML, 'Árbol de Páginas'  ));
}

function lista_paginas_hijas( $uidparent = 0, $nivel = 1 ) {
global $DB, $DB_prefijo;    
    $comando_sql   = "select idpagina, uid, titulo, activa from " . $DB_prefijo . "paginas where uid = {$uidparent}";
    $recordset = $DB->execute("$comando_sql") ;
    $idpagina = $recordset->fields['idpagina'];
    $titulo = $recordset->fields['titulo'];
    $activa = $recordset->fields['activa'];

    $HTML = new HtmlContainer( ) ;

    $div =& $HTML->add( 'div',  array( 
        'class'         => 'trigger',
        'onClick'       => "showBranch('branch{$uidparent}');swapFolder('folder{$uidparent}')"
        ));
    $div->add( 'img',array(
        'src'   => 'images/join.gif'));
    $div->add( 'img',array(
        'src'   => 'images/closed.gif',
        'border' => '0',
        'id'    => "folder{$uidparent}" ));        
    if ( $activa == 0) {
        $div->add( 'span', array(
            'style'     => 'text-decoration: line-through'),
            "{$titulo} [{$idpagina}]");
    } else {
        $div->add_text("{$titulo} [{$idpagina}]" );
    }
    $div->add_text( '&nbsp;&nbsp;::&nbsp;&nbsp;');
    $a =& $div->add( 'a', array( 
        'href'  => "admin.php?q=paginas/edit/{$uidparent}",
        'title' => 'Editar Página'
        )
        );
    $a->add( 'img', array(
        'width' => '16',
        'height' => '16',
        'src'   => _ADM_BOTON_EDITAR ,
        'border'        => '0')) ;
    if ( $activa == 1) {
        $a =& $div->add( 'a', array( 
            'href'  => "/?{$idpagina}",
            'title' => 'Ver en el navegador',
            'target' => 'visualizar'
            )
            );
        $a->add( 'img', array(
            'width' => '16',
            'height' => '16',

            'src'   => _ADM_BOTON_VISUALIZAR ,
            'border'        => '0')) ;
    }
    $span =&  $HTML->add('span', array( 
        'class'         => 'branch',
        'id'            => "branch{$uidparent}" 
        ));
    
    // !*!
    
    $span->add_object( $this->lista_contenidos( $uidparent ) );
    $comando_sql    = "select idpagina, uid, titulo, uidparent from " . $DB_prefijo . "paginas where uidparent = {$uidparent} and tipo = 0 and uid != 0 order by orden" ;
    $recordset = $DB->execute("$comando_sql") ;
    while (!$recordset->EOF) {
        $idpagina =& $recordset->fields['idpagina'];
        $uid =& $recordset->fields['uid'];
        $titulo =& $recordset->fields['titulo'];
        $span->add_object( $this->lista_paginas_hijas( $uid, $nivel + 2) ) ;
        $recordset->MoveNext();
    }
    return $HTML;
}



function lista_contenidos( $uidparent) {
    global $DB, $DB_prefijo;    
    $HTML = new HtmlContainer( ) ;
    
    $comando_sql    = "select idpagina, uid, titulo, uidparent, activa from " . $DB_prefijo . "paginas where uidparent = {$uidparent} and tipo = 1 and uid != 0 order by orden" ;
    $recordset = $DB->execute("$comando_sql") ;
    while (!$recordset->EOF) {
        $idpagina =& $recordset->fields['idpagina'];
        $titulo =& $recordset->fields['titulo'];
        $activa =& $recordset->fields['activa'];
        $HTML->add( 'img',array(
            'src'   => 'images/doc.gif',
            'border' => '0'));        
        if ( $activa == 0) {
            $HTML->add( 'span', array(
                'style'     => 'text-decoration: line-through'),
                "{$titulo} [{$idpagina}]");
        } else {
            $HTML->add_text("{$titulo} [{$idpagina}]" );
        }
        $HTML->add_text( '&nbsp;&nbsp;::&nbsp;&nbsp;');
        $a =& $HTML->add( 'a', array( 
            'href'  => "admin.php?q=paginas/edit/{$recordset->fields['uid']}",
            'title' => 'Editar Página'
            )
            );
            $a->add( 'img', array(
                'width' => '16',
                'height' => '16',
                'src'   => _ADM_BOTON_EDITAR ,
                'border'        => '0')) ;
        $HTML->add( 'br' );
        $recordset->MoveNext();
    }
    return $HTML ;
}
 
 
 
 
 
 
 
 
 
 
 
 
 
 
    function del_pagina_ok( $uid ) {
        if (!empty($uid)) {
            global $DB, $DB_prefijo;    
            
            $recordset = $DB->execute("select uidparent from {$DB_prefijo}paginas where uid = {$uid}");
            $uidparent = $recordset->fields['uidparent'];
            
            $paginas_a_borrar = $this->array_descendientes( $uid );
            $comando_sql = "delete from {$DB_prefijo}paginas where uid = %s";
            $DB->execute(sprintf( $comando_sql, $uid ));
            foreach ($paginas_a_borrar as $uid2) {
                $DB->execute(sprintf( $comando_sql, $uid2 ));
            }
            header("Location: admin.php?q=paginas/list/{$uidparent}");
            die();
        } else  {
	   $error = customHTML::DialogBox( _DIALOG_STOP ,'Error', 'No se puede borrar la página principal, solo se pueden borrar páginas hijas');
	   echo $error->render();
        }
    }


    function array_descendientes( $uid ) {
        /*
        Nos devuelve un array con idpagina y titulo de las páginas hijas
        */
        $aresultado = array();
        $this->array_hijas($aresultado, $uid);
        return ( $aresultado);
        
    }
    function array_hijas( &$aresultado, $uidparent ) {
        global $DB, $DB_prefijo;    
        $comando_sql    = "select idpagina, uid, titulo, uidparent from {$DB_prefijo}paginas where uidparent = {$uidparent} and uid != 0 order by orden" ;
        $recordset = $DB->execute("$comando_sql") ;
        while (!$recordset->EOF) {
            $uid = $recordset->fields['uid'];
            $aresultado[count( $aresultado )] = $uid;
            $this->array_hijas($aresultado, $uid ); 
            $recordset->MoveNext(); 
        }
        
    }
	/** 
	* Dada una determinad uid nos devuelve el idgroup padre de una página.
	*/
	function idgroupparent( $uid) {
		global $DB, $DB_prefijo;
		$comando_sql = "select idgroup from {$DB_prefijo}paginas where uid = \"{$uid}\"";
		$recordset = $DB->execute( $comando_sql ) ;
		$idgroup = $recordset->fields['idgroup'];
		return  $idgroup ;
	
	}

	
	/**
	*Dada una determinad uid nos devuelve el template de una página.
	*/
	function templateparent( $uid) {
		
		global $DB, $DB_prefijo;
		$comando_sql = "select template from {$DB_prefijo}paginas where uid = \"{$uid}\"";
		$recordset = $DB->execute( $comando_sql ) ;
		$template = $recordset->fields['template'];
		return  $template ;
	}   

	function select_idioma( $value = '',  $control = 'lang',  $descartar = array() ) {
		$idiomas = morcegocms_lang::get_array_lang();
		$HTML = new htmlcontainer() ;
		$select =&  $HTML->add( 'select', array( 'name' => $control  ) );
		foreach ( $idiomas as $idioma => $texto ) {
			if( !in_array( $idioma, $descartar )) {
				$select->add( 'option', array(
					'value' => $idioma,
					'selected' => ( $value == $idioma) ? true: false ), $texto );
			}
		}
		return $HTML;
	}

}



?>