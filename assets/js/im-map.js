if(!jQuery('.hd_see_map').length){
  if ( typeof hd_gm_api_key !== 'undefined') {
    if(jQuery('#billing_pickup_warehouse_field').length){
      var ware_span = '<span class="hd_see_map hd_map_backend" place="" zoom="16" style="display: none">See Map</span>';
      jQuery( "#billing_pickup_warehouse_field" ).append(ware_span);
    }
  }
}
if(jQuery('.hd_see_map').length){
  if ( typeof hd_gm_api_key !== 'undefined') {
    jQuery("#IM_Warehouse_address").on('change keyup paste', function() {
      jQuery(".hd_see_map").attr("place", jQuery(this).val());
    });
    jQuery( function( $ ) {

      var cursorX;
      var cursorY;
      if (window.Event) {
        document.captureEvents(Event.MOUSEMOVE);
      }
      document.onmousemove = getCursorXY;
      jQuery(".hd_see_map").each(function() {
        var dPlace = jQuery(this).attr("place");
        var dZoom = jQuery(this).attr("zoom");
        var dText = jQuery(this).html();
        jQuery(this).html('<a onmouseover="hd_see_map.show(this);" style="text-decoration:none; border-bottom:1px dotted #999" href="http://maps.google.com/maps?q=' + dPlace + '&z=' + dZoom + '">' + dText + '</a>');
       });
      });
      var hd_see_map=function(){
      var tt;
      var errorBox;
      return{
        show:function(v){
         if (tt == null) {
         var pNode = v.parentNode;
         pPlace = jQuery(pNode).attr("place");
         pZoom = parseInt(jQuery(pNode).attr("zoom"));
         pText = jQuery(v).html();
         tt = document.createElement('div');
         jQuery(tt).html('<a href="http://maps.google.com/maps?q=' + pPlace + '&z=11" target="new"><img border=0 src="http://maps.google.com/maps/api/staticmap?center=' + pPlace + '&zoom=' + pZoom + '&size=300x300&sensor=false&format=png&markers=color:blue|' + pPlace + '&key=' + hd_gm_api_key +'"></a>');
         tt.addEventListener('mouseover', function() { mapHover = 1; }, true);
         tt.addEventListener('mouseout', function() { mapHover = 0; }, true);
         tt.addEventListener('mouseout', hd_see_map.hide, true);
         document.body.appendChild(tt);
      }
      fromleft = cursorX;
      fromtop = cursorY;
      fromleft = fromleft - 25;
      fromtop = fromtop - 25;
      tt.style.cssText = "position:absolute; left:" + fromleft + "px; top:" + fromtop + "px; z-index:999; display:block; padding:1px; margin-left:5px; background-color:#333; width:302px; -moz-box-shadow:0 1px 10px rgba(0, 0, 0, 0.5);";
      tt.style.display = 'block';
      },
      hide:function(){
        if(tt.style.display != undefined){
          tt.style.display = 'none';
          tt = null;
        }
      }
       };
      }();
      function getCursorXY(e) {
      cursorX = (window.Event) ? e.pageX : event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
      cursorY = (window.Event) ? e.pageY : event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
      }
    }
  }
