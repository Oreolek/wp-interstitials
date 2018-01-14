jQuery( function($){

  //Show Custom 		
  var ar = [ '#_see_show_cust', '#_see_show_all', '#_see_show_home', '#_see_show_categ'];
  down_option('#show_custom', '#_see_show_cust',  ar);

   //data picker
   $('.datapick').datepicker();

   //Data clear
   $('.dclear').click(function(e){
     e.preventDefault();	
     $(this).parent('div').find('input').val('');	

   });

   //Color picker
   if (typeof colorpicker == 'function') { 

     $('.colorpicker').colorpicker();

     $('.colorpicker').each(function(index) {
       var initc = $(this).find("input").val();
       $(this).find("i").css("background", initc);
     });
   }else{
     $('.wpcolorpicker').wpColorPicker();
   }

   //Color picker
   $('#show_timer').change(function() {

     init_settings();
   });

   init_settings();

});

//Down options:

function down_option(id, id_show, ar){
  var id_div = jQuery(id);
  var ck = jQuery(id_show);
  var exc = ( ar.join(', ') );
  jQuery(exc).click(function(){
    if(ck.is(':checked')){
      jQuery(id_div).fadeIn('fast');
    }else{
      jQuery(id_div).hide();
    }
  });	

}

//Select options:
function init_settings(){
  var is_show = jQuery('#show_timer').val();
  var opt1 = jQuery('#countdown_time');
  var opt2 = jQuery('#wait_time');

  if(is_show == "yes"){
    opt1.parents('tr').show();
    opt2.parents('tr').show();

  }
  else if(is_show == "no"){
    opt1.parents('tr').hide();
    opt2.parents('tr').hide();
  }
}
