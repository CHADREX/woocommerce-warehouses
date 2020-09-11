
jQuery( function( $ ) {


  if ($("#warehouses_shipping_country").length) {
    $( "#warehouses_shipping_country" ).change(function() {
      var country = $( "#warehouses_shipping_country" ).val();

      $.ajax({
        url: ajaxurl,
        data: {
            'action':'hd_warehouses_change_country',
            'shipping_country': country,
        },
        success:function(response) {
        },
        error: function(errorThrown){
        }
      });

      Cookies.set("woocommerce_warehouses", country, { path: '/', expires: 30 });
      location.reload();
    });
  }

jQuery(document).ready(function($) {

    $( "#billing_country" ).change(function() {
      var billing_country = $( "#billing_country option:selected" ).val();
      var shipping_country = $( "#shipping_country option:selected" ).val();
      $.ajax({
        url: ajaxurl,
        data: {
            'action':'hd_warehouses_check_country',
            'billing_country': billing_country,
            'shipping_country': shipping_country
        },
        success:function(response) {
          var country = response;
            if(country){
              $.cookie("woocommerce_warehouses", country, { path: '/', expires: 30 });
              window.location.href = window.location.href;
            }
        },
        error: function(errorThrown){
          console.log(errorThrown);
        }
      });
    });

    $( "#shipping_country" ).change(function() {
      var billing_country = $( "#billing_country option:selected" ).val();
      var shipping_country = $( "#shipping_country option:selected" ).val();
        $.ajax({
          url: ajaxurl,
          data: {
              'action':'hd_warehouses_check_country',
              'billing_country': billing_country,
              'shipping_country': shipping_country
          },
          success:function(response) {
            var country = response;
              if(country){
                $.cookie("woocommerce_warehouses", country, { path: '/', expires: 30 });
                window.location.href = window.location.href;
              }
            },
          error: function(errorThrown){
            console.log(errorThrown);
          }
        });
    });

});

  if ($("#billing_pickup_warehouse").length) {

    //Check current shipping methods
    $( 'select.shipping_method, input[name^=shipping_method][type=radio]:checked, input[name^=shipping_method][type=hidden]' ).each( function() {
       var shipping_methods = [];
      shipping_methods[ $( this ).data( 'index' ) ] = $( this ).val();

      local_pickup_selected = false;
      for (var i = 0; i < shipping_methods.length; i++){
        if(shipping_methods[i].indexOf('local_pickup') > -1){
          local_pickup_selected = true;
        }
      }

      // local_pickup is not selected
     if(!local_pickup_selected){
       $( "#billing_pickup_warehouse" ).prop( "disabled", true );
       $( "#billing_pickup_warehouse" ).val('');
     }
     // local_pickup is selected
     else{
       $( "#billing_pickup_warehouse" ).prop( "disabled", false );
     }

     $( "#billing_pickup_warehouse" ).change(function() {
       var new_ware = $(this).val();
       var new_ware_address = hd_address_warehouses[new_ware];
       if ($(".hd_see_map:hidden")) {
         $(".hd_see_map").show();
       }
       if(!new_ware){
         $(".hd_see_map").hide();
       }
       jQuery(".hd_see_map").attr("place", new_ware_address);
     });

    });

 	// When shipping method changes
 	$( document ).on( 'change', 'select.shipping_method, input[name^=shipping_method]', function() {
     $( 'select.shipping_method, input[name^=shipping_method][type=radio]:checked, input[name^=shipping_method][type=hidden]' ).each( function() {
 			 var shipping_methods = [];
       shipping_methods[ $( this ).data( 'index' ) ] = $( this ).val();

       local_pickup_selected = false;
       for (var i = 0; i < shipping_methods.length; i++){
         if(shipping_methods[i].indexOf('local_pickup') > -1){
           local_pickup_selected = true;
         }
       }

       // local_pickup is not selected
      if(!local_pickup_selected){
        $( "#billing_pickup_warehouse" ).prop( "disabled", true );
        $( "#billing_pickup_warehouse" ).val('');
      }
      // local_pickup is selected
      else{
        $( "#billing_pickup_warehouse" ).prop( "disabled", false );
      }

 		});
   });

  }
 });
