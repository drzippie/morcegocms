function check_edtpagina( formulario, antiguo, nuevo ) {
       if ( antiguo != nuevo ) {
		var opt = {
			method: 'post',
			postBody: 'accion=idpagina_exists&nuevo='+ nuevo  ,
			onSuccess: function(t) {
				if (  t.responseText.split(":")[1] == "1"  ) {
					 alert( "El identificador de página especificado ya existe o no es válido");
				} else {
					document.formulario.submit();
				} 
			},
			on404: function(t) {
				alert('Error 404: location "' + t.statusText + '" was not found.');
			},
			onFailure: function(t) {
				alert('Error ' + t.status + ' -- ' + t.statusText);
			}
		}
		new Ajax.Request( 'rs/procesos.php', opt);
	
	
	} else {
           document.formulario.submit();
       }       
   }

function get_templates( tipo ){
    	var opt = {
		method: 'post',
		postBody: 'accion=templates4tipo&tipo='+ tipo ,
		onSuccess: function(t) {
			var miArray ;
			miArray  = t.responseText.split(":")[1].split("~") ;
			objeto = document.formulario.template ;
			objeto.options.length = 0 ;
			var defaultSelected = true;
			var selected = false;
			var length = 0;
			for( indice = 0; indice < miArray.length ; indice ++) {
				if ( indice == 0 ) {
					defaultSelected = true;
					selected = true;
				} else {
					defaultSelected = false;
					selected = false;
			}
			optionName = new Option(miArray[indice], miArray[indice], defaultSelected, selected) ;
			length = objeto.options.length;
			objeto.options[length] = optionName ;
			}
        	},
		on404: function(t) {
			alert('Error 404: location "' + t.statusText + '" was not found.');
		},
		onFailure: function(t) {
			alert('Error ' + t.status + ' -- ' + t.statusText);
		}
	}
	new Ajax.Request( 'rs/procesos.php', opt);
	return false
    }