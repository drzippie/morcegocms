var pagina ;
var divactivo = 0;
/*
Version 1.5
*/
// añadimos el métodos URLDecode a la clase String
Object.extend(String.prototype, {
	URLDecode: function() { 
		var HEXCHARS = "0123456789ABCDEFabcdef"; 
		var plaintext = "";
		var i = 0 ;
		var encoded = this ;
		while (i < encoded.length) {
			var ch = encoded.charAt(i);
			if (ch == "+") {
				plaintext += " ";
				i++;
			} else if (ch == "%") {
				if (i < (encoded.length-2) && HEXCHARS.indexOf(encoded.charAt(i+1)) != -1 && HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
					plaintext += unescape( encoded.substr(i,3) );
					i += 3;
				} else {
					plaintext += "%[ERROR]";
					i++;
				}
			} else {
				plaintext += ch;
				i++;
			}
		} 
		return plaintext ;
	} 
});


/*
Gestión del árbol de páginas
*/

var openImg = new Image();
	openImg.src = "images/open.gif";
var closedImg = new Image();
	closedImg.src = "images/closed.gif";
function showBranch(branch){
	var objBranch = document.getElementById(branch).style;
	if(objBranch.display=="block")
		objBranch.display="none";
	else
		objBranch.display="block";
}

function swapFolder(img){
	objImg = document.getElementById(img);
	if(objImg.src.indexOf('images/closed.gif')>-1)
		objImg.src = openImg.src;
	else
		objImg.src = closedImg.src;
}


 
function confirmar( mensaje, destino) {
    if (confirm(mensaje)) {
       document.location.href = destino  ;
    }
}

function PopUp( ToURL, alto, ancho, ventana) {
	window.open(ToURL, ventana,'scrollbars=no,status=no,menubar=no,resizable=no,width=' + ancho +', height=' + alto + ',fullscreen=no');
	return 0;
}



   
   
        function borrar_cache() {
		var opt = {
			method: 'post',
			postBody: 'accion=borrar_cache'  ,
			onSuccess: function(t) {
				alert(   t.responseText.split(":")[1].URLDecode()  ) ; 
				
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




    function change_conf( key, value) {
    var opt = {
			method: 'post',
			postBody: 'accion=change_conf&key=' + key + '&value=' + value   ,
			onSuccess: function(t) {
				alert(   t.responseText.split(":")[1].URLDecode() ) ; 
				
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
    
    function masInfo( cadena ){
        var opt = {
			method: 'post',
			postBody: 'accion=ficheromasinfo&cadena=' + cadena    ,
			onSuccess: function(t) {
 				miArray =  t.responseText.split(":")[1].split('~')  ; 
			     $('mi-' + miArray[0]).innerHTML =    t.responseText.split('~')[1].URLDecode()  ;
                 a = cssQuery("div.masinfo");
	           for (i = 0; i != a.length; i++) {
	               objeto = a[i] ;
            	   if ( a[i].id  !=  'mi-' + miArray[0]  ) {
            	       objeto.style.display = '';
			
		              } else {
 			            objeto.style.display = 'block';
			
		          }
		
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

	
function check_edttemplate( parameters) {
         if ( parameters[0] == 'add' ) { 
		var opt = {
			method: 'post',
			postBody: 'accion=idtemplate_exists&metodo=' + parameters[0] + '&idtemplate=' + parameters[1]  ,
			onSuccess: function(t) {
				valor = parseInt( t.responseText.split(':')[1]  ) ;
				if (valor == 0 )  {
					document.editar.submit();
				} else {
					alert( "El identificador de template especificado ya existe o no es válido");
					document.editar.nombre.focus();
					
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
		document.editar.submit();
        }
	
    }
    
function srcimagen(id, imagen) {
    $(id).src = imagen;
}

function GetCookie (name) {  
    var arg = name + "=";  
    var alen = arg.length;  
    var clen = document.cookie.length;  
    var i = 0;  
    while (i < clen) {    
        var j = i + alen;    
        if (document.cookie.substring(i, j) == arg)      
        return getCookieVal (j);    
        i = document.cookie.indexOf(" ", i) + 1;    
        if (i == 0) break;   
    }  
    return null;
}

function SetCookie (name, value) {  
    var argv = SetCookie.arguments;  
    var argc = SetCookie.arguments.length;  
    var expires = (argc > 2) ? argv[2] : null;  
    var path = (argc > 3) ? argv[3] : null;  
    var domain = (argc > 4) ? argv[4] : null;  
    var secure = (argc > 5) ? argv[5] : false;  
    document.cookie = name + "=" + escape (value) + 
    ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) + 
    ((path == null) ? "" : ("; path=" + path)) +  
    ((domain == null) ? "" : ("; domain=" + domain)) +    
    ((secure == true) ? "; secure" : "");
}

function DeleteCookie (name) {  
    var exp = new Date();  
    exp.setTime (exp.getTime() - 1);  
    var cval = GetCookie (name);  
    document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString();
}

var exp = new Date(); 
    exp.setTime(exp.getTime() + (24*60*60*1000));

 
function getCookieVal(offset) {
    var endstr = document.cookie.indexOf (";", offset);
    if (endstr == -1)
    endstr = document.cookie.length;
    return unescape(document.cookie.substring(offset, endstr));
}

 

 

 



function iraidpagina() {
	 var res=prompt('Identificador de página al que ir','');
	if ( res > "" ) {
		document.gotoform.iraidpagina.value = res;
		document.gotoform.submit();
	}
	return void(0);
}

 












 





jsFunctions = function() {
  // constructor

}
jsFunctions.agt = navigator.userAgent.toLowerCase();
jsFunctions.is_ie  = ((jsFunctions.agt.indexOf("msie") != -1) && (jsFunctions.agt.indexOf("opera") == -1));

jsFunctions.addEvent = function(el, evname, func) {
	if (jsFunctions.is_ie) {
		el.attachEvent("on" + evname, func);
	} else {
		el.addEventListener(evname, func, true);
	}
};

jsFunctions.addEvents = function(el, evs, func) {
	for (var i = evs.length; --i >= 0;) {
		jsFunctions.addEvent(el, evs[i], func);
	}
};

jsFunctions.removeEvent = function(el, evname, func) {
	if (jsFunctions.is_ie) {
		el.detachEvent("on" + evname, func);
	} else {
		el.removeEventListener(evname, func, true);
	}
};

jsFunctions.removeEvents = function(el, evs, func) {
	for (var i = evs.length; --i >= 0;) {
		jsFunctions.removeEvent(el, evs[i], func);
	}
};

jsFunctions.stopEvent = function(ev) {
	if (jsFunctions.is_ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
};

jsFunctions.popUp = function(url, action) {
	drzPopUp(url, action);
};


function drzPopUp( url, action) {
  drzPopUp.open( url, action );

}
drzPopUp.modal = null ;
drzPopUp.padre = function(ev) {
	setTimeout( function() { if (drzPopUp.modal && !drzPopUp.modal.closed) { drzPopUp.modal.focus() } }, 50);
	
        if (drzPopUp.modal && !drzPopUp.modal.closed) {
           jsFunctions.stopEvent(ev);
	} else {
          
          drzPopUp.relWin( window ) ;
          return ;
  
        }
}             

drzPopUp.capWin = function( w) {
  jsFunctions.addEvent(w, "click", drzPopUp.padre);
  jsFunctions.addEvent(w, "mousedown", drzPopUp.padre);
  jsFunctions.addEvent(w, "focus", drzPopUp.padre);
}
drzPopUp.relWin = function( w ) {
  jsFunctions.removeEvent(w, "click", drzPopUp.padre);
  jsFunctions.removeEvent(w, "mousedown", drzPopUp.padre);
  jsFunctions.removeEvent(w, "focus", drzPopUp.padre);
}
drzPopUp.open = function( url, action ) {
  var dlg = window.open(url, "drzPopUp",
    "toolbar=no,menubar=no,personalbar=no,width=500,height=300," +
    "scrollbars=no,resizable=yes,modal=yes,dependable=yes");
  drzPopUp.modal = dlg;
 	drzPopUp.capWin(window);
        
      	for (var i = 0; i < window.frames.length; capwin(window.frames[i++]));

        drzPopUp._return = function (val) {
		if (val && action) {
			action(val);
		}
		drzPopUp.relWin(window);
		// capture other frames
		for (var i = 0; i < window.frames.length; relwin(window.frames[i++]));
		drzPopUp.modal = null;
	};
        
}



var RTFactivo = false ;
function initRTF( url ) {

if ( RTFactivo == false) {
  oFCKeditor = new FCKeditor( 'texto', '100%' , '450') ;
  oFCKeditor.BasePath = url ;
  oFCKeditor.height= 450 ;
  oFCKeditor.ReplaceTextarea() ;
  objeto = document.getElementById( 'divmenutexto');
  objeto.style.visibility="hidden";
  objeto.style.height='0px'; 
  objeto.style.overflow='auto'; 
  // ocultar('divmenutexto');
  RTFactivo = true ;
  
  return void(0);
}

}

function content( element, html ) {
       
	document.getElementById(element).innerHTML = html ;

}
/*
Cargamos en el onLoad() el nuevo estilo de los botones 
*/
function onLoadBase() {
	a = cssQuery("input[type='button'], input[type='submit'],  input[type='reset'] ");
	var el ;
	for (i = 0; i != a.length; i++) {
		el = a[i] ;
		el.className = 'boton-admin-off' ;
		el.onmouseover = function(e) { if (this.className != 'boton-admin-on' ) { this.className = 'boton-admin-on'; }}
		el.onmouseout   = function(e) { if (this.className != 'boton-admin-off' ) { this.className = 'boton-admin-off'; }}
	}
}

function DRZattachLoadEvent( funcion ) {
	if(typeof window.addEventListener != 'undefined') {
		//.. gecko, safari, konqueror and standard
		window.addEventListener('load', funcion , false);
	}else if(typeof document.addEventListener != 'undefined') {
		//.. opera 7
		document.addEventListener('load', funcion , false);
	} else if(typeof window.attachEvent != 'undefined') {
		//.. win/ie
		window.attachEvent('onload', funcion );
	}
}
Event.observe(window, 'load', onLoadBase, false);

 
/*
Utilidad para generar menus contextuales:

*/
var drZippie = function() {} ;
drZippie.createMenu = function( nameMenu, aMainMenuItems, elementList, funcionClick ) {
	
	var oContextMenu = new YAHOO.widget.ContextMenu( nameMenu, { trigger: elementList } );
	var nMainMenuItems = aMainMenuItems.length;
        var oMenuItem;
	for(var i=0; i<nMainMenuItems; i++) {
		oMenuItem =    new YAHOO.widget.ContextMenuItem( aMainMenuItems[i].text );
		oMenuItem.clickEvent.subscribe( funcionClick    );
		oContextMenu.addItem(oMenuItem);
	}
	oContextMenu.moveEvent.subscribe(
		function(  ) {
			var oNode = this.contextEventTarget;
			this.idDoc = oNode.id.split('_')[1]  ;
		}, 
		oContextMenu, 
		true
	);
	oContextMenu.render(document.body);
}

/**
Gestión de pestañas
*/

drZippie.tabs = function() {}
drZippie.tabs.init = function() { 
    	
    var tabView = new YAHOO.widget.TabView();
    YAHOO.util.Event.onContentReady('divSolapas', function() {
        var modules = YAHOO.util.Dom.getElementsByClassName('solapa', 'div', this);
        
        YAHOO.util.Dom.batch(modules, function(module) {
            tabView.addTab( new YAHOO.widget.Tab({
                label: module.getElementsByTagName('h3')[0].innerHTML,
                contentEl: YAHOO.util.Dom.getElementsByClassName('contenidoSolapa', 
                        'div', module)[0]
            }));
            YAHOO.util.Dom.setStyle(module, 'display', 'none'); /* hide modules */
        });

        tabView.set('activeIndex', 0); // make first tab active
        tabView.appendTo(this); // append to "top-stories"
    });

    
};
Event.observe(window, 'load', drZippie.tabs.init, false);

