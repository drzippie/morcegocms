YAHOO.util.Event.addListener(window, "load", function() {
	
	/*	Menú opciones de página */
	   var elementList = document.getElementsByClassName("menu-pagina");
	   var aMainMenuItems = [
						{ text: "Editar"}, 
			{ text: "Activar/Desactivar"}, 
						{ text: "Borrar" },
			{ text: "Duplicar" },
			{ text: "Mover" }
					];
	drZippie.createMenu( 'MenuContextualPaginas', aMainMenuItems, elementList,
		
		function(p_sType, p_aArguments) {
			switch(this.index) {
				case 0:		// Editar
				document.location.href= "admin.php?q=paginas/edit/" + this.parent.idDoc ;
				break;

				case 1:		// Activar / Desactivar 
				switch_pagina(	this.parent.idDoc  ) ;
				break;
				case 2:		// Borrar 
				document.location.href= "admin.php?q=paginas/borrar/" + this.parent.idDoc ;
				break;
				case 3:	   // Duplicar
				document.location.href = "admin.php?q=paginas/duplicar/" + this.parent.idDoc ;
				break;
				case 4: //mover	 
				document.location.href = "admin.php?q=paginas/mover/" + this.parent.idDoc ;
				break;
			}
			this.parent.hide();
		}
	);
	/*	Menú opciones mover de página */
	   var elementList = document.getElementsByClassName("menu-mover-pagina");
	   var aMainMenuItems = [
			{ text: "Poner como primera"}, 
			{ text: "Subir" },
			{ text: "Bajar" },
			{ text: "Poner como última" }
					];
	drZippie.createMenu( 'MenuContextualOrdenPaginas', aMainMenuItems, elementList,
		
		function(p_sType, p_aArguments) {
			switch(this.index) {
				case 0:		// primera
				document.location.href='admin.php?q=paginas/cambiar_orden/' + this.parent.idDoc	 + '/top';
				break;
				case 1:		// subir 
				document.location.href='admin.php?q=paginas/cambiar_orden/' + this.parent.idDoc	 + '/up';
				break;
				case 2:	   // bajar
				document.location.href='admin.php?q=paginas/cambiar_orden/' + this.parent.idDoc	 + '/down';
				break;
				case 3: //ultima  
				document.location.href='admin.php?q=paginas/cambiar_orden/' + this.parent.idDoc	 + '/bottom';
				break;
			}
			this.parent.hide();
		}
	);
	
	
	}
	
	

);

	
	function switch_pagina( uid ) {
		var opt = {
			method: 'post',
			postBody: 'accion=switch_pagina&uid=' + uid	   ,
			onSuccess: function(t) {
				miArray =  t.responseText.split(":")[1].split('~')	; 
				objeto = $('text_' + miArray[1] );
				 if ( miArray[0] == '1' ) {
					objeto.className = 'paginaOn';
					
				} else {
					
					objeto.className = 'paginaOff';
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
	
	}

