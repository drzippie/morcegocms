<?php 
require "../includes/morcegoCMS/morcegoCMS.php" ;
$aParametros = explode('/', $_SERVER["QUERY_STRING"]) ;
if ( count( $aParametros) != 2) {
    die( '' );
}
$url_imagen =  ($aParametros[0] == 'imagen')? '../imagen.php?' : '../icono.php?' ;
$url_imagen .= $aParametros[1];

$titulo_pagina = ($aParametros[0] == 'imagen')? 'Imagen ::' : 'Icono ::' ;
$titulo_pagina .= $aParametros[1];
?>
<html><head><title><?php echo $titulo_pagina ;?></title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><script language="javascript">
<!-- 
    function autosize(){
    if (top.screen) {
        var aw = screen.availWidth;
        var ah = screen.availHeight;
        obj = document.imagen ;
        if (obj.width < aw) {aw=obj.width  + 10};
        if (obj.height < ah) {ah=obj.height + 30 };
        top.resizeTo(aw, ah);
        // var x = opener.screenX + (opener.outerWidth - window.outerWidth) / 2;
        // var y = opener.screenY + (opener.outerHeight - window.outerHeight) / 2;
        var x = (screen.availWidth - obj.width) / 2;
        var y = (screen.availHeight - obj.height) / 2;
        window.moveTo(x, y);
    }
} //--> </script></head><body topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" onload="autosize();"><img src="<?php echo $url_imagen ;?>" name="imagen" align="center"></body></html>