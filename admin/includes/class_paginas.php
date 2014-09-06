<?php 
	global $filtro;
	if (isset( $_POST['filtro']) ) {
	  $filtro = $_POST['filtro'];
	  $_SESSION['filtro'] = $filtro;
	} else {
		if (!isset( $_SESSION['filtro']) ) {
		   $filtro = 'off';
		   $_SESSION['filtro'] = $filtro;
		} else {
		   $filtro = $_SESSION['filtro'] ;
		}
	 } 
 /**
 * 
 *
 * @package default
 * @author drzippie
 **/
class cls_Paginas {
	var $paginas = array();
	var $contenidos = array();
	var $DB;
	var $DB_prefijo;
	var $uid;
	function cls_Paginas( $DB, $DB_prefijo, $uid) {
		 $this->DB = $DB;
		$this->DB_prefijo = $DB_prefijo;
		$this->uid = $uid;
		$this->load_paginas(0, $this->paginas);
		$this->load_paginas(1, $this->contenidos);
	 }
	function tabla_paginas( $soloactivas = 0, $solotabla = 0 ) {
		return $this->show_lista( $this->paginas, $soloactivas, 'Páginas Hijas','hijas', $solotabla);
	} 
	function tabla_contenidos( $soloactivas = 0, $solotabla = 0) {
		return $this->show_lista( $this->contenidos, $soloactivas, 'Contenidos', 'contenidos', $solotabla);
	} 
	
	function load_paginas($tipo, &$return ) {
		$sql = "select idpagina, uid, titulo, activa, img_mimetype, icono_mimetype from {$this->DB_prefijo}paginas ".
			"where uidparent = {$this->uid}	 and tipo = {$tipo} and uid != 0 order by orden";
		$recordset = $this->DB->execute($sql);
		while (!$recordset->EOF) {
			if ( !empty( $recordset->fields['img_mimetype']) ) {
				$imagen = 1;
			} else {
				$imagen = 0;
			}
			if ( !empty( $recordset->fields['icono_mimetype']) ) {
				$icono= 1;
			} else {
				$icono = 0;
			}
			$return[$recordset->fields['uid']] = array('idpagina' => $recordset->fields['idpagina'], 
				'titulo' => $recordset->fields['titulo'],
				'activa' => $recordset->fields['activa'],
				'imagen' => $imagen,
				'icono'	 => $icono );
			$recordset->MoveNext();
		}
	}
	/**
	 * 
	 *
	 * @return string 
	 * @author drzippie
	 **/
 	function show_lista( $elementos, $soloactivas = 0, $titulo, $tipo, $solotabla = 0) {
 		switch ( $tipo ) {
		case "hijas":
		   $etiqueta = 'pagina';
		   break;
		case "contenidos" : 
		   $etiqueta = 'contenido';
		   break;
		
		}	  
		$HTML = new HtmlContainer( ) ;
		$HTML->add( 'div',	array( 'class' => 'menuPageList' ), 
			'',
			CustomHTML::botonAdmin(	 "Añadir " . $etiqueta , 'Añadir ' . $etiqueta , '#' , 
				"DeleteCookie( 'pgedicionsolapa'); window.location.href='admin.php?q=paginas/add/{$this->uid}/{$tipo}'") );
		
		if ( count( $elementos ) > 0 ) { 
			$tabla_container =& $HTML->add( 'TABLE', array( 'class' => 'PageList ruler',	'id' => 'tabla_' . $tipo . '_' .	$this->uid   ) );
			$tabla =& $tabla_container->add('tbody');
			$tr =&	$tabla->add ('tr');
			$tr->add('th', '', '&nbsp;');
			$tr->add('th', '', '[IDPagina] Título');
			$tr->add('th', '', 'idiomas');
	   		$str_out = '';
	   		reset( $elementos ) ;
	   		$orden = 1;
	   		$tdClass = 'non' ; 
		 	foreach($elementos as $uid => $campos ) {
		   		if ( $soloactivas == 0 || ($soloactivas == 1 && $campos['activa'] == 1 )) {
					$tdClass = ($tdClass === 'non') ? false : 'non' ;
				 	$activa_css  = ($campos['activa'] == 1) ?  'paginaOn' : 'paginaOff'; 
					$LinkVisualizar = ($tipo != 'contenidos' && $campos['activa'] == 1)	 
						? "&nbsp;<a href=\"../?{$campos['idpagina']}\" target=\"_blank\"><img src=\"". 
							_ADM_BOTON_VISUALIZAR . "\" alt=\"Visualizar en el Navegador\" height=\"16\" width=\"16\"></a>" 
						: '';
					$tr =& $tabla->add('TR' , array( 'class' => $tdClass)) ;
					$td =& $tr->add( 'TD', array( 'class' => "col1" ));
						if ($campos['imagen']== 1) {
							$td->add( 
								customHTML::iconoAdmin( 
									array( 'src'=> 'images/iconos/16x16/imagen.gif'),
									array( 'title'=>'Ver Imagen', 'onclick' =>  "jsFunctions.popUp('imagen.php?imagen/{$campos['idpagina']}')")));
						}
						if ($campos['icono']== 1) {
							$td->add( 
								customHTML::iconoAdmin( 
									array( 'src'=> 'images/iconos/16x16/icono.gif'),
									array( 'title'=>'Ver Icono', 'onclick' =>  "jsFunctions.popUp('imagen.php?icono/{$campos['idpagina']}')")));
						}
						
					$td->add(
						customHTML::iconoAdmin( 
							array( 'src'=> _ADM_BOTON_MOVER, 'id' => 'imgmoverpagina_' . $uid  ),
							array( 'title'=>'Cambiar Orden',  'class' => 'menu-mover-pagina' ) )
						);
			 		$td->add(
						customHTML::iconoAdmin( 
							array( 'src'=> _ADM_BOTON_EDITAR   ),
							array('href' => "admin.php?q=paginas/edit/{$uid}",'title' => 'Editar Página' ) )
						);
				 	$td->add(
						customHTML::iconoAdmin( 
							array( 'src'=> _ADM_BOTON_OPCIONES, 'id' => 'imgpagina_' . $uid  ),
							array( 'title'=>'Cambiar Orden',  'class' => 'menu-pagina' ) )
						);
			 
		
				$td =& $tr->add( 'TD' , array( 'class' => "col2"));
				
				
				
					$a =& $td->add('A', array( 
						'id' => "text_{$uid}",
						'class' => $activa_css,
						'href' => "admin.php?q=paginas/list/{$uid}"), "[{$campos['idpagina']}]" );
					$a->add('STRONG', '', " {$campos['titulo']}");
				// $td->add('p', array(	  'class' => "dragable"), 'Mover');	 
				$td =& $tr->add( 'TD', array( 'class' => 'col3') );
				$td->add_object( $this->div_idiomas( $uid ) );
		  
		
			}
		   $orden++;
		   // echo , $campos['idpagina'] );
	   }
	} else {
		 $HTML->add( 'div', array(
		  'class' => 'emptyResult'
		), ' ');
	
	}
	   
		return $HTML->render() ;
	}
	
/**
 * Devuelve la lista de idiomas
 *
 * @return htmlObject
 * @author drzippie
 **/
 
	function div_idiomas( $uid , $float = true	) {
		$idiomas = morcegocms_lang::get_array_lang();
		$html = new htmlcontainer();
		if ( $float == true ) { 
			$div =&	 $html->add( 'div');
		} else {
			$div =& $html->add( 'span', array( 
				'class' => 'idiomas'));
		}
				
	 $sql = "select distinct lang from {$GLOBALS['DB_prefijo']}paginas_lang " .
			"where uid = {$uid} order by lang";
		
		$recordset = $GLOBALS['DB']->execute($sql);
		while (!$recordset->EOF) {
			$div->add( 'a', array( 
				'title' => 'Editar página en ' . $idiomas[ $recordset->fields['lang']] ,
				'href'	=> 'admin.php?q=paginas/edt_lang/' . $uid  . '/' . $recordset->fields['lang']), 
				'[' . $recordset->fields['lang'] . ']' );
	   
			$recordset->MoveNext();
		}
		$div->add( 'a', array( 
			'title' => 'Añadir nuevo idioma',
	   
			'href'	=> 'admin.php?q=paginas/add_lang/' . $uid ), '[+]' );
		return $div ;
	}
	
   
   
} // END class 
 
?>
