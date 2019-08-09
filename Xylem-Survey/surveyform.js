var $a = jQuery.noConflict();

$a(window).load(function(){
    $a("#survey-submit").on('click touchstart', function(e){
         e.preventDefault();
         $a("#survey-submit").prop("disabled", true);
         $a("#loading").show();
         var height = $a(window).height();
         $a("#loading").css('height', height + 'px');
         var surveydata = new FormData($a('#survey-form')[0]);
         surveydata.append('action', 'process_form');
         var homelink = $a('#home-link').attr('href');
         if(($a('#temp_emp').val() && $a('#temp_before').val() && $a('#temp_after').val()) || $a('#form-area').hasClass('edit')) {
             $a.ajax({
               type: "POST",
               url: SurveyAjax.ajaxurl,
               action: 'process_form',
               data: surveydata,
               processData: false,  // tell jQuery not to process the data
               contentType: false, 
               success: function(response){
                    if (response == 'Thank you for your submission!') {
                        $a('#form-area').html('<h1>Thank You<br/>for your submission!</h1><a id="home-link" href="' + homelink + '" class="btn form-control">Start a New Survey</a>');
                        $a('body').addClass('blue');
                        $a("#loading").hide();
                    } else if(response == 'The survey has been successfully updated!') {
                        $a('#form-area').html('<h1>The form has been successfully updated!</h1><a id="home-link" href="' + homelink + '/wp-admin/admin.php?page=survey-main" class="btn form-control">Return to Survey List</a>');
                        $a('body').addClass('blue');
                        $a("#loading").hide();
                    } else {
                        $a("#survey-submit").prop("disabled", false);
                        $a("#loading").hide();
                        $a('#form-errors').html(response);
                        $a("html, body").animate({ scrollTop: 0 }, 300);
                  }
               } 
             });
         } else {
             $a('#loading').hide();
             var msg = ''
             if(!($a('#temp_emp').val())) {
                msg = msg + 'Employee image data is still loading. <br/>';
             }
             if(!($a('#temp_before').val())) {
                msg = msg + 'Before image data is still loading. <br/>';
             }
             if(!($a('#temp_after').val())) {
                msg = msg + 'After image data is still loading. <br/>';
             }
             
             $a('#img-modal p').html(msg + "Please try again in a moment.");
             $a('#img-modal').modal('show');
             $a('.modal-backdrop').each(function() {
                 $a(this).css('height', height + 'px');
             })
             $a("#survey-submit").prop("disabled", false);
         }
        return false; 
    }); 
    $a('#sign-in-submit').on('click touchstart', function(e){
        e.preventDefault();
        var signinData = new FormData($a('#sign-in')[0]);
        var language = $a('#language').val();
        signinData.append('language', language);
        signinData.append('action', 'process_sign_in');
        var navigate = this.href;
        $a.ajax({
           type: "POST",
           url: SurveyAjax.ajaxurl,
           action: 'sign_in_form',
           data: signinData,
           processData: false,  // tell jQuery not to process the data
           contentType: false, 
           success: function(response){
               if (response == 'Success') {
                   window.location.href =  navigate;
               } else {
                   $a('#form-errors').html(response);
               }
           } 
         });
        return false; 
    });
    $a('#register-submit').on('click touchstart', function(e){
        e.preventDefault();
        var registrationData = new FormData($a('#registration')[0]);
        var language = $a('#language').val();
        registrationData.append('language', language);
        registrationData.append('action', 'process_registration_form');
        var homelink = $a('#home-link').attr('href');
        $a.ajax({
           type: "POST",
           url: SurveyAjax.ajaxurl,
           action: 'process_registration_form',
           data: registrationData,
           processData: false,  // tell jQuery not to process the data
           contentType: false, 
           success: function(response){
               if (response == 'Success') {
                   $a('#form-area').html('<h1>Thank you for registering!</h1><p class="text-center">You will be notified at the email address you signed up with when your registration has been approved.</p><a id="home-link" href="' + homelink + '" class="btn form-control">Submit a Survey</a>');
               } else {
                   $a('#form-errors').html(response);
               }
           } 
         });
        return false; 
    })
    
    $a('#enddate').datetimepicker({format: 'MMMM D, YYYY'});
    $a('#enddate input').click(function() {
        $a('#enddate').data("DateTimePicker").show();
    });
    $a('#startdate').datetimepicker({format: 'MMMM D, YYYY'});
    $a('#startdate input').click(function() {
        $a('#startdate').data("DateTimePicker").show();
    });
    $a("#contextual-help-link").click(function () {
        $a("#contextual-help-wrap").css("cssText", "display: block !important; visibility: visible !important;");
    });
    $a("#show-settings-link").click(function () {
        $a("#screen-options-wrap").css("cssText", "display: block !important; visibility: visible !important;");
    });
});          

