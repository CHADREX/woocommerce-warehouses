jQuery(document).ready(function($) {

  // Adds datetimepicker to datetime input
  if ( $( "#hd-im-stocklog-date1-top" ).length ) {
    $('.hd-input-add-date').datepick({
      dateFormat: 'yy-mm-dd'
    });

    if ( $( "#hd-im-stocklog-product-top" ).length ) {
      $("#hd-im-stocklog-product-top").insertBefore( ".top .button");
    }

    if ( $( "#hd-im-stocklog-date2-top" ).length ) {
      $("#hd-im-stocklog-date2-top").insertBefore( "#hd-im-stocklog-product-top" );
    }

    if ( $( "#hd-im-stocklog-date1-top" ).length ) {
      $("#hd-im-stocklog-date1-top").insertBefore( "#hd-im-stocklog-date2-top" );
    }

    if ( $( "#hd-im-filter-stocklog-warehouse-top" ).length ) {
      $("#hd-im-filter-stocklog-warehouse-top").insertBefore( "#hd-im-stocklog-date1-top" );
    }

  }

})
