jQuery(document).ready(function($) {

  $("#hd_import_from_csv").live('click',function(e){
  			var answer = confirm("Are you sure you want to start stock update?");
		    if (answer){
			    $( "#hd_list_of_events" ).empty();
			    $('<p>Stock import started!</p>').appendTo( "#hd_list_of_events" );
			    csvImportNextAjax(0, 5000, 0);
			  }
  });

  function csvImportNextAjax(offset, size, products_updated){
		  var ajaxFunction = 'hd_csv_update_warehouses_stock';
		  var percent = 0;
			$.ajax({
		      url: ajaxurl,
		      dataType: 'JSON',
		      data: {
		          'action': ajaxFunction,
		          'offset': offset,
		          'size'  : size
		      },
		      success:function(response) {
		        if(response){
			      if(response.result == 'success'){
				    offset += size;
				    products_updated += response.products_updated;
				    var finished = response.finished;

				    response_text = "<p id='hd_import_status'>Updated " + products_updated + " products.</p>";
				    $( "#hd_import_status" ).remove();
  					$(response_text).appendTo( "#hd_list_of_events" );
  					if(finished){
				  	  $('<p>Import completed with success!</p>').appendTo( "#hd_list_of_events" );
			    	}
			    	else{
					  csvImportNextAjax(offset, size, products_updated);
					}
			      }
			      else{
				    response_text = "<p id='hd_import_status'>" + response.message + "</p>";
				    $( "#hd_import_status" ).remove();
					$(response_text).appendTo( "#hd_list_of_events" );
			      }

		        }
		      },
		      error: function(errorThrown){
		      }
		    });

  	}

});
