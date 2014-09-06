<?php
/*
**
 * @package Core
 * @author Antonio Cortés (drZippie)  <antonio@antoniocortes.com> 
 * @copyright Copyright &copy; 2002-2006 Antonio Cortés
 * @license BSD
 * @Changed $LastChangedDate$
 * @Revision $Rev$
*/require "./includes/morcegoCMS/morcegoCMS.php" ;class cls_image{
    function cls_image($col, $idpagina){
        $col_content = ($col == 'icon') ? 'icono_content' : 'img_content' ;
        $col_mimetype = ($col == 'icon') ? 'icono_mimetype' : 'img_mimetype' ;
        $comando_sql = "select {$col_content}, {$col_mimetype} from {$GLOBALS['DB_prefijo']}paginas where idpagina='{$idpagina}'";
        $recordset = $GLOBALS['DB']->execute($comando_sql) ;
        if (isset($recordset -> fields[$col_mimetype ])){
            header("Content-Type: " . $recordset -> fields[$col_mimetype]);
            echo $recordset -> fields[$col_content];
        } else {
            header("Content-Type: image/png");
            echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAALHRFWHRDcmVhdGlvbiBUaW1l" .
                "AGx1biAyNCBtYXIgMjAwMyAyMTo1OTozMiArMDEwMFGLr4wAAAAHdElNRQfTAxgVAByEn/xV" .
                "AAAACXBIWXMAAArwAAAK8AFCrDSYAAAABGdBTUEAALGPC/xhBQAAAAZQTFRFAAAA////pdmf" .
                "3QAAAAJ0Uk5T/wDltzBKAAAACklEQVR42mNoAAAAggCB2kUIOwAAAABJRU5ErkJggg==");
        }
    }
}
$icono = new cls_image( 'image', $_SERVER["QUERY_STRING"]) ;?>