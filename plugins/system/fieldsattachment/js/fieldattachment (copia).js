// everything happens inside 'domready' event
window.addEvent('domready', function(){
         
        
	// define a function to run when form is submitted 
        $('addRow').addEvent( 'click' ,  function(e) {
                 //alert('test');
		 e.stop();  // stop the default submission of the form

                 alert($('this'));

                 // Using Selectors

                 //var myNewElements = new Element('input.json');
                 var myNewElements = $$('input.json');
                 
                 //alert(myNewElements);
                 var linia_json = "";
                 myNewElements.each(function(link)  {
                         //alert(link.name+" = "+link.value);
                         linia_json += '"'+link.name+'" : "'+link.value+'"';
                         linia_json += ',';
                 });

                 linia_json = linia_json.substring(0,linia_json.length-1) ;
                 linia_json = '{'+ linia_json + '}';


  
		//Pasamos nuestro string a un objeto
                var row = '<tr>';
		var objeto = JSON.decode(linia_json);
                myNewElements.each(function(link)  {
                     //alert(eval("objeto."+link.name));
                     row += '<td style="font-size:11px; padding:7px; color:#333;" class="'+link.name+'">'+link.value+'</td>';
                });
                row += '<td style="font-size:11px; padding:7px; color:#333;"><a href="#"  class="deleterow" >Delete</a></td>';
                row += '<tr>';

                inject_row( $('table_result_body'), row );
                events_remove(); 

                input_dest = create_input('table_result', this.get("class"));
                //var destino = this.get("class");
                //$(destino).value = String(input_dest).substring(0,  String(input_dest).length-1);
		 
                /* var objeto = JSON.evaluate(linia_json);
                 alert(json.Modelo);
                  var json = JSON.toString(linia_json);
                 json = JSON.encode(json);
                 var data = JSON.decode(json);
                 alert(data["Modelo"]);
                 var destino = this.get("class");
                 var input_dest = $(destino).value;
                 input_dest += linia_json;
                 $(destino).value = input_dest;
                 //alert(data);
                 */

                 


		// Validate our form. Make sure no fields are blank
		/*var valid_form = true;
		$$('input.json').each(function(item){
			if( item.value == '' ) valid_form = false;
		});

		// If our form is valid submit to table_form.php
		// Else show an error message.
		if( valid_form ) {
			//this.send();
		} else {
			alert('Fill in all fields');
		}*/

               

	}); // End handling of the 'submit event'

	/**
	 * This is a handy little function that handles adding an
	 * array of data to a table.
	 */
	var inject_row = function( table_body, row_str ){ 
		// convert string to table wrapped in a div element
		var newRow = htmlToElements( row_str );
		// inject the new row into the table body
		newRow.inject( table_body );

	}

        var events_remove = function(  ){
            $$('.deleterow').addEvent('click', function(e) {
                e.stop();
                //alert( $(this).getParent( ).getParent( ) );
                $(this).getParent( ).getParent( ).destroy();

                //Print input
                 
                var nombre = $(this).getParent('tr').get("id");
               
                nombre_int = find_input(nombre) ;
                input_dest = create_input('table_result', nombre_int);
                

                return false;
            });
        }

        var find_input = function(  name_input ){
            //alert(name_input);
            //alert("d: "+$('addRow').get("class") );
            input_value = $('addRow').get("class") ;
            return input_value;

        }


        var create_input = function( name_table, name_input ){
             //alert(name_input);
             var input_value = "";
             $$('table.table_result tbody tr').each(function(el) {
                    var linea = "{";
                    //el.addClass(count++ % 2 == 0 ? 'odd' : 'even');
                    //alert(el.getChildren('td'));
                    
                    el.getChildren('td').each(function(el) {
                        //alert(el.get("html")+" - "+el.get("class"));
                        if(el.get("class")) linea += '"'+el.get("class")+'":'+'"'+el.get("html")+'",';
                    });
                   linea = linea.substring(0,linea.length-1) ;
                   //alert( linea.length );
                   if( linea.length > 0) linea += "}";
                   if( linea.length > 0) input_value += linea+',';
                   //alert("-- "+linea);
                });

            //INSERT INOUT 
            $(name_input).value = String(input_value).substring(0,  String(input_value).length-1);

            return input_value;
             
        }

        /**
	 *  Fill the input init 
	 */
        var initinput = function( value ){
                //alert(value);
                /*
                var objeto = JSON.decode(linia_json);
                myNewElements.each(function(link)  {
                     //alert(eval("objeto."+link.name));
                     row += '<td class="'+link.name+'">'+link.value+'</td>';
                });
                */
        }

         

	/**
	 *  wraps tr in a div with full table details.
	 *  this little hack is required for IE support (h8 ie)
	 */
	var htmlToElements = function(str){
	    return new Element('div', {html: '<table><tbody>' + str + '</tbody></table>'}).getElement('tr');
	}

        //init_table();
        events_remove();

});


// end handling of 'domready' event