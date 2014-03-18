if( typeof(_seoJQuery) == 'undefined' ) var _seoJQuery = null;
function _seoInitializeJQuery(callback){
   if(typeof(_seoJQuery) == 'undefined' || !_seoJQuery){
      if(!_seoInitializeJQuery.included){
         _seoInitializeJQuery.included = true;
         document.write('<scr' + 'ipt type="text/javascript" src="/_seo/frontend/js/jquery.js"></scr' + 'ipt>');
         document.write('<scr' + 'ipt type="text/javascript">_seoJQuery = jQuery.noConflict(true);</scr' + 'ipt>');
      }
      setTimeout(function (){
         _seoInitializeJQuery(callback);
      }, 50);
   } else{
      _seoInitializeJQuery.initializePlugins();

      !function ($){
         $(function (){
            callback(_seoJQuery);
         });
      }(_seoJQuery)
   }
}


_seoInitializeJQuery.initializePlugins = function (){
   !function ($){
      if(!$.fn.formValidator){
         $.fn.formValidator = function (){
            var form = this;
            form.find('[data-form-success], [data-form-validation-error], [data-validation-error]').hide();

            form.find('[data-validate="true"]').each(function (){
               var field = $(this);
               if(field.data("validate")){
                  field.on('focusout', function (){
                     var validationType = field.data("validationType");
                     var content = field.val();
                     var validationValue = field.data("validationValue");
                     var postData = {'validationType': validationType, 'content': content, 'validateField': 'true'};
                     $.ajax({
                        url: '/_seo/modules/inputvalidator.php', data: postData, type: 'POST', success: function (text){
                           if(text != 1){
                              form.find('[data-validation-error="' + field.attr('name') + '"]').slideDown();
                           }
                           else{
                              form.find('[data-validation-error="' + field.attr('name') + '"]').slideUp();
                           }
                        }
                     });
                  });
               }
            });

            form.on('submit', function (e){
               e.preventDefault();
               $.ajax({
                  url: '/_seo/modules/formvalidator.php', data: form.serialize(), dataType: 'json', type: 'POST', cache: false, success: function (jsonData){
                     if(jsonData['formvalid'] == 1){
                        form.children().hide();
                        form.find('.header, [data-form-success]').show();
                     } else {
                        delete jsonData['formvalid'];
                        form.find('[data-validation-error]').hide();
                        form.find('[data-form-validation-error]').show();
                        $.each(jsonData, function (index, value){
                           if(value == 0){
                              form.find('[data-validation-error="' + index + '"]').show();
                           }
                        });
                     }
                  }
               });

            });
         }
      }
   }(_seoJQuery)

};