<?php
/**
 * 
 *
 * @author drzippie
 * @version $Id$
 * @copyright Antonio Cortés (Dr Zippie), 27 November, 2006
 * @package default
 **/
include_once('./includes/core_admin.php' );
if ( isset( $_GET['q'] ) ) {
	$_SERVER["QUERY_STRING"] = urldecode( $_GET["q"] );
} else {
	$_SERVER["QUERY_STRING"] = urldecode( $_SERVER["QUERY_STRING"] );
}
if ( substr(  $_SERVER["QUERY_STRING"], 0, 5 ) == 'login'  && !$GLOBALS['oUser']->isAdmin() ) {
	$peticion = explode( '/', $_SERVER["QUERY_STRING"] );
	if ( !isset( $peticion[1] )){
		$peticion[1] = '0' ;
	}
	$html = html_admin::ObjectPage( 'Entrada en la Administración', false );
	$body =& $html->get_element_by_id('body') ;
	/* Eliminamos el cuerpo de la plantilla de la administración */
	$body = new htmlobject( 'body') ;
	/* Añadimos al HEAD el js de validación */
	$head =& $html->get_element_by_tag( 'head');
	$head->add( html_admin::js(  'js/elements/login.js' ));
	
	$body->add_object( html_admin::loginForm( $peticion[1] ) );
	
	
	die( $html->render() );
} 
if ( !$GLOBALS['oUser']->isAdmin()	)  {
	header('Location: index.php?q=login');
	die();
}
switch ($_SERVER["QUERY_STRING"]) {
	case 'logout':
		$oUser->logout_user();
		header('Location: ./index.php');
		die();
		break;		   
	default :
		header( 'Location: ./admin.php' );
		break;
}
?>