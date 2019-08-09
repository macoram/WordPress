var $a = jQuery.noConflict();
 
$a(window).load(function(){

    $a("#jobs-form").submit(function(e){
         e.preventDefault();
        
         var jobdata = new FormData($a(this)[0]);
         var file = $a(document).find('input[type="file"]');
         var individual_file = file[0].files[0];
         jobdata.append("file", individual_file);
         jobdata.append('action', 'process_form');
 
      
         $a.ajax({
           type: "POST",
           url: JobAjax.ajaxurl,
           action: 'process_form',
           data: jobdata,
           processData: false,  // tell jQuery not to process the data
           contentType: false, 
           success: function(response){
              if (response == 'Thank you for your interest! You will be notified of job postings you may be interested in at the email you provided.') {
                $a('#jobs-form .form-wrapper').html('<p class="lead text-center">'+response+'</p>');
              } else {
                  $a('#form-errors').html(response);
              }
           } 
         });
        return false; 
    });    
    $a("#unsub-form").submit(function(e){
         e.preventDefault();
        
         var unsubdata = new FormData($a(this)[0]);
         unsubdata.append('action', 'process_unsub_form');
 
      
         $a.ajax({
           type: "POST",
           url: JobAjax.ajaxurl,
           action: 'process_unsub_form',
           data: unsubdata,
           processData: false,  // tell jQuery not to process the data
           contentType: false, 
           success: function(response){
              if (response == 'This email address has been removed from the job notification mailing list.') {
                $a('#unsub-form .form-group').html('<p class="lead text-center">'+response+'</p>');
              } else {
                  $a('#form-errors').html(response);
              }
           } 
         });
        return false; 
    }); 
});          