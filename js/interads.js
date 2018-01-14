jQuery( function($){
  if(typeof(interAds) !== 'undefined' && interAds !== null){
    //WAIT TIME
    var is_wait = interAds.is_wait;
    var is_cached = interAds.is_cached;
    if(is_wait){
      setTimeout(
        function() 
        {
          $('#interads').fadeIn('fast');
          interads_count();
        }, (is_wait*1000));
    }
    if(!is_wait && !is_cached){
      interads_count();
    }

    //CACHED AD
    if(is_cached){

      $.ajax({
        type : "post",
        dataType : "html",
        cache: false,
        url : interAds.ajaxurl,
        data : {action: 'inter_ads_action', id_post : interAds.id_post, is_front : interAds.is_front },
        success: function(response) {
          if(response.type !== "" && response != "none_interads") {
            $('body').append(response);
            if(!is_wait){
              interads_count();
            }
          }
        }
      });
    }
  }
});

function interads_count(){
  var min_txt = (typeof(interAds) !== 'undefined' && interAds !== null && interAds.minutes) ? interAds.minutes : '';
  var sec_txt = (typeof(interAds) !== 'undefined' && interAds !== null && interAds.seconds) ? interAds.seconds : '';

  if(typeof(interAds.is_count) !== 'undefined' && interAds.is_count !== null && interAds.is_count > 0){

    count = jQuery('.interads-kkcount-down').data('seconds');

    jQuery('.interads-kkcount-down').countdown({
      date: +(new Date()) + 1000*count,
      render: function(data) {
        if( data.min > 0)
        jQuery(this.el).html("<div>" + this.leadingZeros(data.min, 2) + " <span> "+ min_txt +" : </span></div><div>" + this.leadingZeros(data.sec, 2) + " <span>"+ sec_txt +"</span></div>");
        else
        jQuery(this.el).html("<div>" + this.leadingZeros(data.sec, 2) + " <span>"+ sec_txt +"</span></div>");

      },
      onEnd: function() {
        interads_close();
      }
    });
  }
}

//Close Ad
function interads_close(){
  jQuery('#interads').fadeOut('fast', function() {
    jQuery('#interads').remove();
    return false;
  });
}

