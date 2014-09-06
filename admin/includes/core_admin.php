<?php
/**
 * @package Admin
 * @author Antonio Cortés <antonio@antoniocortes.com> 
 * @copyright Copyright &copy; 2002-2006 Antonio Cortés
 * @license BSD
 * @version 1.7.5 
 */
define('_ADM_VERSION', '1.7.5');
/*
definimos la constante _MORCEGOCMS_ADMIN 
si está activa el fichero morcegoCMS.php no carga las clases de funciones de salida:
optimizamos de esta forma el uso de memoria.
*/
define('_MORCEGOCMS_ADMIN', 'true') ;
require dirname( __FILE__) . '/../../includes/morcegoCMS/morcegoCMS.php';
include_once( dirname( __FILE__) . '/libs/spyc.php' );

define('_ADM_BOTON_EDITAR', './images/iconos/16x16/edit.gif');
define('_ADM_BOTON_MOVER', './images/iconos/16x16/mover.gif');
define('_ADM_BOTON_OPCIONES', './images/iconos/16x16/exec.gif');
define('_ADM_BOTON_BORRAR', 'images/iconos/16x16/papelera.gif');
define('_ADM_BOTON_VISUALIZAR', 'images/iconos/16x16/vistaprevia.gif');
define('_ADM_BOTON_INFO', 'images/iconos/16x16/nota.gif');
define('_ADM_BOTON_GRABAR', 'images/iconos/16x16/filesave.gif');
define('_ADM_BOTON_DUPLICAR', 'images/iconos/16x16/tab_duplicate.png');
define('_ADM_BOTON_CAMBIAR_PADRE', 'images/iconos/16x16/tab_breakoff.png');
define('_ADM_BOTON_ACTIVAR', 'images/iconos/16x16/lock.png');
// cuadros de dialogo
define('_DIALOG_STOP', 0);
define('_DIALOG_INFO', 1);
define('_DIALOG_QUESTION', 2);
define('_DIALOG_CAUTION', 3);
define('_DIALOG_LOCK', 4);



if ( isset( $_SESSION['user_name']) ) {
    if ( isset( $_SERVER['QUERY_STRING']) && substr( $_SERVER['QUERY_STRING'], 0, 6) != 'frame_') {
        $_SESSION['pagina_actual'] = $_SERVER['PHP_SELF'] . 
            ( (isset( $_SERVER['QUERY_STRING'] )) ?  '?' . $_SERVER['QUERY_STRING'] : '' ) ;
    }
}

class html_admin {
    function ObjectPage( $titulo = '', $menu = true ) {
        $HTML = new HtmlContainer( ) ;           
	$HTML->add_text( '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'. "\n" . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ) ;
        $page =& $HTML->add('HTML', array( 'xmlns' => 'http://www.w3.org/1999/xhtml' , 'xml:lang' => 'es' ) );
        $head =& $page->add('HEAD');	
	$head->add('title','', 'Administración de la Web :: ' . $titulo );
	/* hojas de estilo por defecto de Yahoo UI */
	$head->add_object(  html_admin::css(  'js/yui/reset/reset.css' ) ) ;    	
	$head->add_object(  html_admin::css(  'js/yui/fonts/fonts.css' ) ) ;    	
	$head->add_object(  html_admin::css(  'js/yui/container/assets/container.css' ) ) ;    	
	$head->add_object(  html_admin::css(  'js/yui/menu/assets/menu.css' ) ) ;    	
	$head->add_object(  html_admin::css(  'js/yui/tabview/assets/tabs.css' ) ) ;    	
	$head->add_object(  html_admin::css(  'js/yui/tabview/assets/border_tabs.css' ) ) ;    	
	$head->add_object(  html_admin::css(  'js/yui/reset-fonts-grids/reset-fonts-grids-min.css' ) ) ;    	
	//reset-fonts-grids/reset-fonts-grids-min.css
	/* Hojas de estilo propias*/
	$head->add_object(  html_admin::css(  'css/admin.css' ) ) ;    	
	/* JS: Prototype y Scriptaculous */    
	$head->add_object(  html_admin::js(  'js/prototype.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/scriptaculous.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/controls.js' ) ) ;    	
	/* JS: Yahoo UI */
	$head->add_object(  html_admin::js(  'js/yui/yahoo/yahoo.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/yui/event/event.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/yui/dom/dom.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/yui/tabview/tabview.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/yui/container/container_core.js' ) ) ;    	
	$head->add_object(  html_admin::js(  'js/yui/menu/menu.js' ) ) ;    	
	/*JS: Propio */
	$head->add_object(  html_admin::js(  'js/admin.js' ) ) ;    	
	/*JS: cssQuery  */
	$head->add_object(  html_admin::js(  'js/cssQuery-p.js' ) ) ;    	
        /* Meta */
	$head->add( 'meta', array('http-equiv' => 'expires', 'content' => '-1'));
        $head->add( 'meta', array('http-equiv' => 'pragma', 'content' => 'no-cache'));
        $head->add( 'meta', array('http-equiv' => 'cache-content', 'content' => 'no-cache'));
        
	// Body 
	$body =& $page->add( 'body', array( 'id'    => 'body', ));
	$container =& $body->add( 'div', array( 'id' => 'doc2', 'class' => "yui-t1" ));
	
	
	if ( $menu === true ) {
		$encabezado =& $container->add('div',  array( 'id' => 'hd'));  
		$encabezado->add('div',  array( 'id' => 'logo') , null, new htmlobject('a', array( 
		'title'         => 'Página de Inicio de la Administración',
		'href'          =>  'index.php') , null,  CustomHTML::MenuImagen( 'Ir a inicio', 'images/logo-100x100t.gif') ));   
		$encabezado->add('div');  
		$encabezado->add('div',  array( 'id' => 'opciones'));  
		$encabezado->add( 'div',  array( 'id' => 'encabezado'));
		$encabezado->add( 'div',  array( 'id' => 'subMenu'));
		$container2 =& $container->add( 'div', array('id' => 'bd' ));
		$baseContenido =& $container2->add('div',  array( 'id' => 'yui-main'));  
	 //	$baseContenido->add( 'div', array('class' => 'yui-b' ), null, new htmlobject('div',  array( 'id' => 'container'), null, 	new htmlobject('div',  array( 'id' => 'encabezado'))));  
		$baseContenido->add( 'div', array('class' => 'yui-b' ), null, new htmlobject('div',  array( 'id' => 'container')));
		$encabezado =& $container->add('div',  array( 'id' => 'ft'), null, new htmlobject('span' , array( 'class' => 'version'), 
              "MorcegoCMS [{$GLOBALS['configCMS']->version}/" . _ADM_VERSION . ']') );            

		$strFunction = implode( '/' , array_slice ( explode( '/', $_SERVER['QUERY_STRING']  ), 0, 2 ) ) ;
		
		
		$div =& $container2->add('div', array( 'class' => 'yui-b'));
		$div =& $div->add('div', array( 'id' => 'menuIzquierda'));
		$div->add_object( html_admin::menuPrincipal()  );
		
		$div =& $container->get_element_by_id(  'opciones');
		$span =& $div->add('span', array( 'class' => 'itemMenuSuperior' ));
			$a =& $span->add('a', array(	'href' => 'index.php?logout', 'title' => 'Salir de la administración'));
				$a->add( 'img', array('alt' => 'cerrar sesión y salir', 'src' => 'images/iconos/32x32/salir.gif','width' => '32','height' => '30' )); 
		$span =& $div->add('span', array( 'class' => 'itemMenuSuperior'));
			$a =& $span->add('a', array(	'href' => '#','onClick' => 'borrar_cache();', 'title' => 'Borrar Caché'));
				$a->add( 'img', array('alt' => 'Borrar Caché','src' => 'images/iconos/32x32/trash.gif','width' => '32','height' => '32' )); 
		$span =& $div->add('span', array( 'class' => 'itemMenuSuperior'));
			$a =& $span->add('a', array(	'href' => 'ayuda.php?function='  . $strFunction ,'target' => 'ayuda','title' => 'Ayuda'));
				$a->add( 'img', array('alt' => 'ayuda','src' => 'images/iconos/32x32/help.gif','width' => '32','height' => '32' )); 
	}

        return  $HTML;
    }
    function css( $url, $media = 'screen' ) {
	$object = new htmlobject( 'LINK', array( 
            'rel'       => 'stylesheet',
	    'type' 	=> 'text/css', 
            'href'      => $url ,
            'media'      => $media  ));    
	return $object;
    }
    function js( $url ) {
	return new htmlobject( 'script', array (
            'type'      => 'text/javascript',
            'src'       => $url ));	  
    }
    function botonIndexAdmin( $imagen, $titulo, $url ) {
	$html = new htmlobject( 'a', array('title' => $titulo, 'href' => $url ) );
	$html->add( 'img' , array( 'src'  => $imagen, 'alt' => $titulo ));
	$html->add('span', '', $titulo );
	return $html;

   }
    
    function IndexAdmin() {
        $html = html_admin::ObjectPage();
        $head =& $html->get_element_by_tag( 'head');
	$head->add( html_admin::css(  'css/index.css' ) ) ;
	$body =& $html->get_element_by_id('container') ;
	 
	$encabezado =& $html->get_element_by_id('encabezado') ;
	$encabezado->add_object( CustomHTML::DivEncabezado( '',  "Inicio Administración") );
        $html2  = new htmlcontainer();
	$html2->add('h2', array('class' => 'seccion-menu' ), 'Gestionar Contenidos:');
        $ul =& $html2->add( 'ul' , array( 'class' => 'botones') );
	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/paginas.gif' , 
		"Páginas" ,  
		"admin.php?q=paginas" ));	
	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/archivos.gif' , 
		"Archivos" ,  
		"admin.php?q=files" ));	
	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/reordenar.gif' , 
		"Reordenar" ,  
		"admin.php?q=utils/reordenar" ));	
	$html2->add('h2', array('class' => 'seccion-menu' ), 'Gestionar Diseño:');
        $ul =& $html2->add( 'ul' , array( 'class' => 'botones') );
	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/plantillas.gif' , 
		"Plantillas" ,  
		"admin.php?q=templates" ));		

	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/botones.gif' , 
		"Botones" ,  
		"admin.php?q=botones" ));	
		
	$html2->add('h2', array('class' => 'seccion-menu' ), 'Administrar MorcegoCMS:');
        $ul =& $html2->add( 'ul' , array( 'class' => 'botones') );
        $ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/editar-perfil.gif' , 
		"Editar Perfil" ,  
		"admin.php?q=users/edit/{$_SESSION['iduser']}" ));
	
	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/usuarios.gif' , 
		"Usuarios" ,  
		"admin.php?q=users" ));	
	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/configurar.gif' , 
		"Configurar Web" ,  
		"admin.php?q=edit_config" ));	

	$ul->add( 'li' , '', '', html_admin::botonIndexAdmin(  
		'images/iconos/log.gif' , 
		"Logs" ,  
		"admin.php?q=logs/list/20" ));	
       
        $body->add_object( CustomHTML::DivContenido( $html2  ) );        
        echo $html->render();    
    
    }
    
    
    
    
    function menuPrincipal() {
	$html = new htmlcontainer ;
	$body =& $html ; 
	$menu =& $body->add( 'div', array( 'class' => 'menu' ));
	$leftMenu = Spyc::YAMLLoad( dirname(__FILE__) .'/../config/leftmenu.yml');
	
	foreach( $leftMenu['menu'] as  $seccion  ) {
		$menu->add('h2', '',  $seccion['name']);
		foreach( $seccion['items'] as  $element  ) {
			$menu->add_object(customHTML::MenuElement( $element['title'], $element['url'], $element['target'], $element['onclick']) );
		}
		
	}
        return $html ;
    
    }
    
    function loginForm( $error = '0' ) {
    
    $html = new htmlContainer();
    $form =& $html->add( 'form', array( 
      'action' => 'index.php',  
      'name'  => 'frmLogin', 
      'method' => 'post'  ,
      'onsubmit' => "checkLogin(); return false;" 

     
      )) ;

    $div =& $form->add( 'div', array('class' => 'dialog'));
    if ( $error === '1' ) {
		$div->add('div', array('class'=>'notice'), 'Debe estar logueado como administrador');
	 }
	$div->add( customHTML::inputLine( 'Usuario',  	new htmlobject( 'input', array( 'type' => 'text', 'name' => 'username',  'id' => 'username' ) ))  );
   	$div->add( customHTML::inputLine( 'Contraseña',	new htmlobject( 'input', array( 'type' => 'password', 'name' => 'password',  'id' => 'password' ) ))  );
	$div->add( customHTML::inputLine( '',  	new htmlobject('input', array( 'name' => 'btnSubmit','type' => 'submit','value' => 'Entrar' ) ))  );
	$div->add( customHTML::inputLine( '',  	new htmlobject( 'a',  array( 'href' => '#', 'onclick' => 'send_password();'), 'Recordar Contraseña'))  );
	
	 
   
    return customHTML::DialogBox( _DIALOG_LOCK , 'Acceso a la Administración', $html ) ;
}
    

}

/**
* Esta clase nos devolverá objetos HTML ... cada método nos devolverá un tipo de objeto:
* un botón, una caja de diálogo, un grupo de botones, un icono, una imagen con enlace, ... 
* el objeto que nos devolverá siempre será de la clase htmlcontainer 
*/

class CustomHTML {
    /**
    * Nos devuelve una imagen con un enlace
    *
    *
    */ 
    function  ImageLink( $image = '', $href = '',  $title = '', $target = '', $onclick = '', $align= 'left' ) {
        $html = new htmlcontainer();
        // !*TODO: Comprobar la existencia de la imagen
        if ( !empty( $href ) || !empty( $onclick )) {
            
            $a =& $html->add( 'a', array(
                'href'          => ( empty( $href ))? 'javascript:void(0);' : $href ,
                'title'         => ( empty( $title )) ? false : $title , 
                'target'        => ( empty( $target )) ? false : $target ,
                'onclick'       => ( empty( $onclick )) ? false : $onclick    ));
            $a->add_object( CustomHTML::Image( $image, $align ) );
        }  else {
            $html->add_object( CustomHTML::Image( $image, $align ) );
        }
        return $html;
    }

    function  iconoAdmin(  $imageProperties = array(), $linkProperties = array() ) {
        $html = new htmlcontainer();
        // !*TODO: Comprobar la existencia de la imagen
		if (!isset( $linkProperties['href'] )) {
			$linkProperties['href'] = 'javascript:void(0);';
		}
        if ( count( $linkProperties ) > 0) {
            $a =& $html->add( 'a', $linkProperties, null, new htmlobject( 'img', $imageProperties ) );
        }  else {
            $html->add( 'img', $imageProperties );
        } 

        return $html;
    }

    
        /**
    * Nos devuelve una imagen con un enlace
    *
    *
    */ 

    function  Boton16x16( $image = '', $href = '',  $title = '', $target = '', $onclick = '', $align = 'left' ) {
        $html = new htmlcontainer();
        // !*TODO: Comprobar la existencia de la imagen
        if ( !empty( $href ) || !empty( $onclick )) {
            
            $a =& $html->add( 'a', array(
                'href'          => ( empty( $href ))? 'javascript:void(0);' : $href ,
                'title'         => ( empty( $title )) ? false : $title , 
                'target'        => ( empty( $target )) ? false : $target ,
                'onclick'       => ( empty( $onclick )) ? false : $onclick    ));
            $a->add_object( CustomHTML::Image( $image, $align, 16, 16 ) );
        }  else {
            $html->add_object( CustomHTML::Image( $image, $align, 16, 16 ) );
        }
        return $html;
    }
    
    function Image( $image, $align = 'left', $width = false, $height= false ) {
        $html = new htmlcontainer();
        $html->add( 'img', array('src'=> $image, 'align' => $align ,
        'width' => $width,
        'height' => $height
        ));
        return $html;
    }
    
    /*
    *****>  C U S T O M   D E   L A   A D M I N I S T R A C I O N 
    */


    /*
      Control numerico
      Se ha añadido la funcion sumar( controlid, cantidad  ) a las funciones en javascript 
    
    */
    function numeric( $control = '', $value = 0 ,  $readonly = false ) {
      $html = new htmlcontainer() ;
      $html->add( 'input', array(
        'type' => 'text',
        'name' => $control,
        'id'  => $control ,
        'value' => $value,
        'readonly' =>  false  
      ));
      return $html;
      
      
    
    }



    /**
    *  Div del encabezado de la administración 
    */
    function DivEncabezado( $ImgEncabezado = '', $Titulo = '', $UrlAyuda = '') {
        $div  = new HtmlContainer( ) ;
          
          
          $form =& $div->add( 'form', array( 
            'name' => 'gotoform',
            'method' => 'post',
            'action' => 'admin.php?q=paginas/goto',
            'class' => 'oculto'));
          $form->add('input', array(
              'type' => 'hidden',
              'value' => '',
              'name' => 'iraidpagina'));
          $div->add('h1', '', "$Titulo" );
          
            
          
        return $div  ;
    }
    /**
    * Div con el contenido de la administración
    */
    function DivContenido( $oContenido) {
        $HTML = new HtmlContainer() ;
        $div =& $HTML->add('div', array( 
            'class' => 'mainContent'));
        
        $div->add_object($oContenido );
        return $HTML;
    }
    /**
    *
    *
    *  DialogBox
    *  $Contenido (mixed) string|object html
    */ 
    function DialogBox( $Tipo = 0, $Titulo = '', $Contenido = '', $Botones = '' ) {
    
        /* 
        $Tipo: images/dialog_{valor}.gif
            0 = stop
            1 = info
            2 = question
            3 = caution
            4 = lock
        */
        switch ( $Tipo ) {
            case 0:
                $image_sufix = 'stop';
                break;
            case 1:
                $image_sufix = 'info';
                break;
            case 2:
                $image_sufix = 'question';
                break;
            case 3:
                $image_sufix = 'info'; // caution
                break;
            case 4:
                $image_sufix = 'lock';
                break;
            default :
                $image_sufix = 'stop';
                break;
        }
    
    
	$HTML = new htmlcontainer();
        $div =& $HTML->add( 'div', array( "class" => "dialogo" ) );
        $table =& $div->add( 'table', array ( 
            'class' => 'dialogo',
            'cellpadding' => '2',
            'cellspacing' => '4'));
        $tr =& $table->add( 'tr') ;
        $td =& $tr->add('td', array('colspan' => '2', 'class' =>'titulo' ), $Titulo );
        $tr =& $table->add( 'tr') ;
        $td =& $tr->add('td', array('class' =>'icono' )) ;
        $td->add(  'img', array (
	  'alt' => $image_sufix ,
          'src' => 'images/iconos/32x32/dlg_'  . $image_sufix . '.gif',
          'width' => '32',
          'height' => '32'
          
          ));
          
        $td =& $tr->add('td', array('class' =>'content' )) ;
        if ( is_object($Contenido)) {
            $td->add_object( $Contenido );
        } else {
            $td->add_text($Contenido );
        }
        if ( is_object($Botones)) {
            $div =& $td->add( 'div', array('class' => "botones") );
            $div->add_object( $Botones ) ;
        }
        
        return $HTML;
    }
    
    /**
    * Nos devuelve el índice de una sección del menu principal
    */
    function MenuTitle( $Titulo ) {
        $html = new htmlcontainer();
        $html->add( 'li', '', $Titulo ) ;
        return $html;
    }
    /**
    *
    * Nos devuelve un elemento del menu
    */
    function MenuElement( $titulo,  $href='', $target='', $onclick = '') {
        $html = new htmlcontainer();
        
        $html->add( 'a', array ( 'href' => $href, 'target' =>  $target ,'onclick'=> $onclick ), 
            /* '&raquo; '. */ $titulo );
        return $html;
    }
    function MenuBoton( $titulo, $imagen,  $href='', $target='', $onclick = '') {
        $html = new htmlcontainer();
        $a =& $html->add( 'a', array ( 'href' => $href, 'target' =>  $target ,'onclick'=> $onclick ));
        $a->add( 'img', array(
            'src' => $imagen,
            'title' => $titulo,
            'align' => 'center',
            'style' => 'margin: 0px; margin-left: 4px;'
            ));
        return $html;
    }
    
    function MenuImagen( $titulo, $imagen) {
        $html = new htmlcontainer();
        $html->add( 'img', array(
            'src' => $imagen,
            'title' => $titulo,
            'align' => 'center',
            'style' => 'margin-top: 4px; margin-left: 4px;'));
        return $html;
    }
    
    /**
    * Boton de la administración
    *
    */ 
    function BotonAdmin( $texto, $alt = '', $href='',  $onclick = '', $target='' ) {
        $html = new htmlcontainer();
        $onclick =(empty( $onclick)) ? 'document' . ( (!empty($target) && $target != '_self') ? "." . $target : '' ) . '.location.href=' ."'" . $href . "'" : $onclick ;
        $html->add( 'input', array(
            'type' => 'button',
            'class' => 'botonadmin',
            'style' => 'margin-right: 5px;',
            'value' => $texto,
            'title' => ( empty($alt))  ? false : $alt ,
            'onclick' => $onclick ));
        return $html ;
    }
    /*
    *
    * Nos devuelve un boton "Volver";
    */ 
    function BotonBack() {
        return  CustomHTML::BotonAdmin("Volver" , 'Volver' , "", "history.go(-1)" );
    }

    function celda_icono( $imagen, $titulo, $contenido ) {
        $html = new htmlcontainer();
        $table =& $html->add( 'table');
            $tr =& $table->add( 'tr' );
                $td =& $tr->add( 'td', array(
                    'rowspan'   => 2 ));
                $td->add('img', array( 
                    'src'       => $imagen ));
                
                $td =& $tr->add( 'td');
                $td->add('h5', '', $titulo );
            $tr =& $table->add( 'tr' );                
                $td =& $tr->add( 'td');
                $td->add( $contenido  );
        return $html;
    }

	/**
	* Nos devuelve una linea (tabulada) de label + control 
	*/
	function inputLine( $label = '' , $object )  {
		$html = new htmlobject( 'div', array('class' => 'input-line' ));
		$html->add( 'label', '', $label ) ;
		$html->add( 'div', array( 'class' => 'input-content' ), '',  $object ) ; 
		return $html;
	
	}


}

/**
* La clase comboadmin nos devuelve objetos HTML con elementos SELECT (listas desplegables)
*
*/
class ComboAdmin  {
    function make_select( $Elements, $Value, $ControlName , $readonly = false  ) {
        $html = new htmlcontainer() ;
        $select =& $html->add( 'select', array(
            'name' => $ControlName, 
	    'id' => $ControlName, 
            'readonly' => $readonly  ,
            'disabled' => $readonly  
            ));
        foreach ( $Elements as $Options ) {
            $select->add( 'option', array ( 'value' => $Options[0], 'selected' => ( $Value == $Options[0]) ? true : false ),
                $Options[1] );
        }
        return $html;    
    }
    function select_idgroup( $idgroup = '-1' , $control='idgroup' , $readonly = false ) {
        $aValores = array(array('-1', 'Todos'),
            array('0', '0 - Usuario Registrado'),
            array('1', '1 - ' ),
            array('2', '2 - '),
            array('3', '3 - '),
            array('4', '4 - Administrador'),
            array('5', '5 - Administrador [root]'));
        return ComboAdmin::make_select( $aValores, $idgroup, $control , $readonly ) ;
    }
    /*
    * Nos Crea un control select con el nombre $control y los valores de los templates
    * como valor seleccionado pondrá el que está en $template.
    */
    function select_template($prefijo, $template = '', $control='template' ) {
        $comando_sql = "select idtemplate from {$GLOBALS['DB_prefijo']}templates where idtemplate like '{$prefijo}%'";
        $recordset = $GLOBALS['DB']->execute( $comando_sql );
        $tmpArray = array();
        while ( !$recordset->EOF ) {
            $tmpArray[] = array( $recordset->fields['idtemplate'], $recordset->fields['idtemplate']);
            $recordset->MoveNext();
            }
        return ComboAdmin::make_select( $tmpArray, $template, $control ) ;
    }
    /**
    * Nos devuelve un select con los valores (primera[first] o última [last])
    *
    */ 
    function select_orden_nueva_pagina( $control = 'orden') {
        $aValores = array ( 
            array( 'first', 'Poner al Principio [Primera]'),
            array( 'last', 'Poner al Final [Última]'));
        return ComboAdmin::make_select( $aValores, 'last', $control );
    }
    
    
}

class adm_class {
	var $oHtml ;
	var $oHead = null ; /* Objeto para añadir al head del html*/
	var $parameters = array();
	function render( $html , $titulo = '', $urlayuda = '', $onLoad = null, $onHead = null  ) {
		if( !empty( $this->oHead ) ) {
			$head =& $this->oHtml->get_element_by_tag( 'head');
			$head->add( $this->oHead ); 
		}
		if ( !empty( $onHead )  ) {
			$head =& $this->oHtml->get_element_by_tag( 'head');
			$head->add( $onHead ); 
		}
		$body =& $this->oHtml->get_element_by_id( 'body')  ;
		if ( !empty( $onLoad ) ) {
			$body->set_value( 'onload', $onLoad ) ;
		}
		$encabezado =& $body->get_element_by_id('encabezado') ;
		$encabezado->add_object( CustomHTML::DivEncabezado( '', $titulo, $urlayuda) );
		$body =&  $body->get_element_by_id( 'container');  
		$body->add_object( CustomHTML::DivContenido( $html  ) );
		echo $this->oHtml->render();
	} 

}






?>