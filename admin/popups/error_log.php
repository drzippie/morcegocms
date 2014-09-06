<?php 
/*
Nos muestra el texto de un error del log de errores
*/
$idlog = addslashes( $_GET['id'] ) ;
include("../includes/core_admin.php" );
?>
<html>
<head>
<link rel="stylesheet" href="popup.css" type="text/css"/>
<title>Visor de Log</title>
<meta http-equiv="expires" content="-1"/>
<meta http-equiv="pragma" content="no-cache"/>
<meta http-equiv="cache-content" content="no-cache"/>
<meta name="pragma" content="no-cache"/>
</head>
<body>
<?php
$comando_sql = "select * from {$GLOBALS['DB_prefijo']}log where idlog = \"{$idlog}\" " ;
$resultado = $GLOBALS['DB']->execute( $comando_sql ) ;
foreach( $resultado->fields  as $key => $value ) {
	if ( $key != 'content'  ) {
		echo '<div class="input-line">',
			'<label>',
			$key,
			':</label>',
			'<div class="input-content">',
			 htmlentities( $value ) ,
			 ' &nbsp;</div>',
			 '</div>';
	} else {
		
		$parametros = implode(';', array_slice( explode(';', $value ), 2));
		$error = unserialize( $parametros );
		foreach( $error  as $key2 => $value2 ) {
		echo '<div class="input-line">',
			'<label>',
			$key2,
			':</label>',
			'<div class="input-content">',
			 htmlentities( $value2) ,
			 ' &nbsp;</div>',
			 '</div>';
		}
	}
	
}
?>
</body>
</html>