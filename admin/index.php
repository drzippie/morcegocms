<?php
/**
 * 
 *
 * @author drzippie
 * @version $Id$
 * @copyright Antonio Cort�s (Dr Zippie), 27 November, 2006
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
	$html = html_admin::ObjectPage( 'Entrada en la Administraci�n', false );
	$body =& $html->get_element_by_id('body') ;
	/* Eliminamos el cuerpo de la plantilla de la administraci�n */
	$body = new htmlobject( 'body') ;
	/* A�adimos al HEAD el js de validaci�n */
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