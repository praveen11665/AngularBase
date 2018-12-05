<style type="text/css">
  .help-block{
  color: red !important;
}
</style>
<script type="text/javascript">
  (function(angular) {
  angular.module('myapp', [])

    //Cannot access numberic values to be call in a form directive allow-characters
    .directive('allowCharacters', function() {
        return {
            restrict: 'A',

            link: function($scope, $element) {
                $element.bind('keydown', function(e) {
                  if (e.ctrlKey || e.altKey) 
                  {
                    e.preventDefault();
                  }else{
                    var key = e.keyCode;    
                    if (!((key == 116) || (key == 8) || (key == 9) || (key == 32) || (key == 46) || (key >= 35 && key <= 40) || (key >= 65 && key <= 90))) 
                    {          
                      e.preventDefault();            
                    }
                  }
                });
            }
        }
    }) 

    //Cannot Access White Spaces to be call in a form disallow-spaces
    .directive('disallowSpaces', function() {
        return {
            restrict: 'A',

            link: function($scope, $element) {
                $element.bind('keydown', function(e) {
                    if (e.which === 32) {
                        e.preventDefault();
                    }
                });
            }
        }
    }) 

    //Cannot Access White Spaces to be call in a form disallow-spaces
    .directive('cannotInput', function() {
        return {
            restrict: 'A',

            link: function($scope, $element) {
                $element.bind('keydown', function(e) {
                    if (e.which) {
                        e.preventDefault();
                    }
                });
            }
        }
    })  

    //Cannot Access Alphabetics Keys to be call in a form allow-only-numbers
    .directive('allowOnlyNumbers', function () {
          return {  
              restrict: 'A',  
              link: function (scope, elm, attrs, ctrl) {  
                  elm.on('keydown', function (event) {  
                      if (event.which == 64 || event.which == 16) {  
                          // to allow numbers  
                          return false;  
                      } else if (event.which >= 48 && event.which <= 57) {  
                          // to allow numbers  
                          return true;  
                      } else if (event.which >= 96 && event.which <= 105) {  
                          // to allow numpad number  
                          return true;  
                      } else if ([8, 13, 27, 37, 38, 39, 40].indexOf(event.which) > -1) {  
                          // to allow backspace, enter, escape, arrows  
                          return true;  
                      } else {  
                          event.preventDefault();  
                          // to stop others  
                          return false;  
                      }  
                  });  
              }  
          }  
    })

    //Select 2 Call in directive
    .directive("select2", function ($timeout, $parse) {
        return {
            restrict: 'AC',
            require: 'ngModel',
            link: function (scope, element, attrs) {
                $timeout(function () {
                    element.select2();
                    element.select2Initialized = true;
                });

                var refreshSelect = function () {
                    if (!element.select2Initialized) return;
                    $timeout(function () {
                        element.trigger('change');
                    });
                };

                var recreateSelect = function () {
                    if (!element.select2Initialized) return;
                    $timeout(function () {
                        element.select2('destroy');
                        element.select2();
                    });
                };

                scope.$watch(attrs.ngModel, refreshSelect);

                if (attrs.ngOptions) {
                    var list = attrs.ngOptions.match(/ in ([^ ]*)/)[1];
                    // watch for option list change
                    scope.$watch(list, recreateSelect);
                }

                if (attrs.ngDisabled) {
                    scope.$watch(attrs.ngDisabled, refreshSelect);
                }
            }
        };
    })

    //myCtrl for myapp
    .controller('myCtrl', ['$scope', '$http', '$compile', function($scope, $http, $compile) {

      $scope.formTitle = true;
      $scope.docFor    = JSON.parse('<?php echo $this->mcommon->json_records_all('app_roles', array('role_id >' => '3'));?>');
      $scope.docType   = JSON.parse('<?php echo $this->mcommon->json_records_all('document_type');?>');

      //When Submitted Form Throw Error
      $scope.submited = function(form)
      {
        if ($scope[form].$valid) {
        } else {
          //$(':required').addClass('customfocus');
          $scope.showMsgs = true;
        }    
      };

      //Common Group Checkbox Checked or not
      $scope.selectCheck = {};
      $scope.someSelected = function (object) {
          if (!object) return false;
          return Object.keys(object).some(function (key) {
            return object[key];
          });
      }     

      //When datatable click load only data for form page
      $('#dataTableId').on('click', '.editButtonClick', function () 
      {
        var formUrl = $(this).data('form_url');
        var request = $http({url: formUrl})
                .then(function(response){
                  $scope.form      = response.data;
                  $scope.formTitle = false;  
                });
      });

      //When datatable click load content
      $('#dataTableId').on('click', '.formButtonClick', function () 
      {
        var formUrl = $(this).data('form_url');
        var request = $http({url: formUrl})
                .then(function(response){
                  //var temp = $compile(response.data)($scope);
                  angular.element(document.getElementById('showContent')).html(response.data);
                });
      });
    }])    
  })(window.angular);
</script>