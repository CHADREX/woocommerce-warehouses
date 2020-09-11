jQuery(document).ready(function($) {
  if ( $( "#hd_select_warehouse_product" ).length ) {

    // Initial stock calculation
    var optionSelected = $("#hd_select_warehouse_product option:selected");
    var stock = optionSelected.data( "stock");
    if(stock <= 0){
      $('.single_add_to_cart_button').attr("disabled", true);
    }
    else{
      $('.single_add_to_cart_button').attr("disabled", false);
      $("input[name=quantity]").attr('max', stock);
    }

    // Stock calculation on change
    $('#hd_select_warehouse_product').on('change', function (e) {
      var optionSelected = $("option:selected", this);
      var stock = optionSelected.data( "stock");
      if(stock <= 0){
        $('.single_add_to_cart_button').attr("disabled", true);
      }
      else{
        $('.single_add_to_cart_button').attr("disabled", false);
        $("input[name=quantity]").attr('max', stock);
      }
    });

    if ( $( ".variation_id" ).length ) {
      $(".variation_id").on("change", function() {
        variation_id = $(".variation_id").val();
        if(variation_id){
          hd_change_product_var_warehouse(variation_id);
        }
      });

      function hd_change_product_var_warehouse(variation_id){
        $('.single_add_to_cart_button').attr("disabled", true);
        $("#hd_select_warehouse_product").find('option').remove();
        $('#hd_select_warehouse_product').append($("<option></option>")
          .attr("value",'')
          .text('Loading ...'));
        $('#hd_select_warehouse_product').attr("disabled", true);

        $.ajax({
          url: ajaxurl,
          dataType: 'JSON',
          data: {
              'action':'hd_warehouses_change_product_var_warehouse',
              'variation_id': variation_id
          },
          success:function(response) {
            if(response.product_warehouses){
              var warehouse = response.product_warehouses;
              $("#hd_select_warehouse_product").find('option').remove();
              $('#hd_select_warehouse_product').attr("disabled", false);
              $.each(warehouse, function( index, value ){
                $('#hd_select_warehouse_product').append($("<option></option>")
				          .attr("value",value.warehouse.IM_Warehouse_id)
                  .attr("data-stock",value.stock)
				          .text(value.warehouse.IM_Warehouse_name + ' - Stock: ' + value.stock));
                });
                var optionSelected = $("#hd_select_warehouse_product option:selected");
                var stock = optionSelected.data( "stock");
                if(stock <= 0){
                  $('.single_add_to_cart_button').attr("disabled", true);
                }
                else{
                  $('.single_add_to_cart_button').attr("disabled", false);
                  $("input[name=quantity]").attr('max', stock);
                }
            }
          },
          error: function(errorThrown){
            console.log(errorThrown);
          }
        });
      }
    }
  }

  else if ( $( "#hd_select_warehouse_product_stock" ).length ) {
    // Initial stock calculation
    var optionSelected = $("#hd_select_warehouse_product_stock option:selected");
    var stock = optionSelected.data( "stock");
    if(stock <= 0){
    }
    else{
      $("input[name=quantity]").attr('max', stock);
    }

    // Stock calculation on change
    $('#hd_select_warehouse_product_stock').on('change', function (e) {
      var optionSelected = $("option:selected", this);
      var stock = optionSelected.data( "stock");
      if(stock <= 0){
      }
      else{
        $("input[name=quantity]").attr('max', stock);
      }
    });

    if ( $( ".variation_id" ).length ) {
      $(".variation_id").on("change", function() {
        variation_id = $(".variation_id").val();
        if(variation_id){
          hd_change_product_var_warehouse(variation_id);
        }
      });

      function hd_change_product_var_warehouse(variation_id){
        $("#hd_select_warehouse_product").find('option').remove();
        $('#hd_select_warehouse_product').append($("<option></option>")
          .attr("value",'')
          .text('Loading ...'));
        $('#hd_select_warehouse_product_stock').attr("disabled", true);

        $.ajax({
          url: ajaxurl,
          dataType: 'JSON',
          data: {
              'action':'hd_warehouses_change_product_var_warehouse',
              'variation_id': variation_id
          },
          success:function(response) {
            if(response.product_warehouses){
              var warehouse = response.product_warehouses;
              $("#hd_select_warehouse_product_stock").find('option').remove();
              $('#hd_select_warehouse_product_stock').attr("disabled", false);
              $.each(warehouse, function( index, value ){
                $('#hd_select_warehouse_product_stock').append($("<option></option>")
				          .attr("value",value.warehouse.IM_Warehouse_id)
                  .attr("data-stock",value.stock)
				          .text(value.warehouse.IM_Warehouse_name + ' - Stock: ' + value.stock));
                });
                var optionSelected = $("#hd_select_warehouse_product_stock option:selected");
                var stock = optionSelected.data( "stock");
                if(stock <= 0){
                }
                else{
                  $("input[name=quantity]").attr('max', stock);
                }
            }
          },
          error: function(errorThrown){
            console.log(errorThrown);
          }
        });
      }
    }
  }

});
