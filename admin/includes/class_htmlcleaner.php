<?php
/**
*
*
*
*
*/
class adm_htmlcleaner  extends adm_class {
  
    function adm_htmlcleaner( $html)  {
        $this->parameters =& $GLOBALS['aArgumentos'] ;
        $this->oHtml = $html;

        // aqui irían las diferentes funciones
        $this->show_form();
        
    }
    
    
    function show_form() {
        $HTML = new HtmlContainer( ) ;
        $form =& $HTML->add( 'form', array (
            'action'    => $_SERVER['PHP_SELF'] . '?'  . implode( '/', $this->parameters ),
            'method'    => 'POST',
            'name'      => 'formulario'));
        
        $form->add( 'p', '', 'Pegue el codigo html a limpiar en el siguiente cuadro de texto y pulse sobre el botón ' .
            'limpiar para limpiar el codigo HTML' );
        
        $content = ( isset( $_POST['content'])) ? $_POST['content'] : '';
        $content = ( get_magic_quotes_gpc() == 1 ) ? stripslashes($content) : $content;
        $content = htmlentities( strip_tags( $content, 
            '<b><strong><u><ol><ul><li><p><br><a><tr><td><table>')) ;
        $form->add( 'textarea', array( 'name' => 'content',
            'style' => 'width: 99%; height: 250px;'), $content);
        $form->add('br');
        $form->add('input', array( 'type' => 'submit', 'value' => 'limpiar' ));
        $form->add('', '', '&nbsp;&nbsp;');         
        $form->add('input', array( 'type' => 'button', 'value' => 'Cerrar Ventana', 'onclick' => 'window.close();' ));
        
         // $form->add_object( customHTML::DialogBox( _DIALOG_INFO , 'Base de datos', 'La base de datos bla bla bla ' ));
        // $form->add_object( customHTML::DialogBox( _DIALOG_INFO , 'Base de datos', CustomHTML::menutitle("Prueba Objeto") ));
        $this->render( $HTML,  'HTML Cleaner', '') ; 
    }
}
	
	
	
?>