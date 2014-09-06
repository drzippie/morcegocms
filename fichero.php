<?php
/*
**
 * @package Core
 * @author Antonio Cortés (drZippie)  <antonio@antoniocortes.com> 
 * @copyright Copyright &copy; 2002-2006 Antonio Cortés
 * @license BSD
 * @Changed $LastChangedDate$
 * @Revision $Rev$
*/require "./includes/morcegoCMS/morcegoCMS.php" ;
class cls_file{
    function cls_file($idfile){
        $comando_sql = "select mimetype, content, original_file, size from {$GLOBALS['DB_prefijo']}files where idfile='{$idfile}' and internal=0";
        $recordset = $GLOBALS['DB']->execute($comando_sql) ;
        if (isset($recordset -> fields['mimetype'])){
            header("Content-Type: " . $recordset -> fields['mimetype']);
            if ($this -> is_a_doc($recordset -> fields['mimetype'])){
                Header('Content-Length: ' . $recordset -> fields['size']);
                Header('Content-disposition: inline; filename=' . $recordset -> fields['original_file']);
            }
            echo $recordset -> fields['content'];
        } else {
            die("Page not found");
        }
    }
    function extension($filename){
        return substr(strtolower($filename), - (strlen($filename) - strrpos($filename , '.') - 1));
    }
    function is_a_doc($mimetype){
        return (in_array($this -> extension($mimetype), $GLOBALS['varsCMS'] -> mimetypes_docs)) ? true : false;
    }
}$cls_fichero = new cls_file( $_SERVER["QUERY_STRING"] ); ?>