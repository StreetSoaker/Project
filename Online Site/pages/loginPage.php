<!DOCTYPE html>
<html>
<head>
    <title>Bootstrap 101 Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Css -->
    <link href="css/bootstrap.min.css"          rel="stylesheet" />
    <!--<link href="css/bootstrap-responsive.css"   rel="stylesheet" />-->
    <link href="css/normalize.css"              rel="stylesheet" />
    <link href="css/core.css"                   rel="stylesheet" />
    <!-- Javascript -->
    <script src="js/jquery-1.9.1.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        $(document).ready( function() {            
            function vcenter(){
                var win_top = $(window).height() / 2;
                var logo_top = $('.logo').height() / 2;
                var login_top = $('.inputbox').height() / 2;
                
                var login_mid = win_top - login_top;
                var logo_mid = win_top - logo_top;
                
                $('.logo').css({'top' : logo_mid+'px'});
                $('.inputbox').css({'top' : login_mid+'px'});
            }
            
            $('img').load(function(){
                vcenter();
                
                $(window).resize(function(){
                    vcenter();    
                });
            });


            /*
             * Login function
             */
            $('#login').submit(function(event) {
                event.preventDefault();
                var username = $('#username').val();
                var password = $('#password').val();

                $.post("pages/login.php", { 'username': username, 'password': password },
                    function(data) {
                        if(data == 1) {
                            window.location = 'index.php';

                            event.preventDefault();
                        } else {
                            $('body').append(data);
                        }
                    }
                );
            });
        });
    </script>
</head>
<body>
    <!-- Login Screen -->
    <div class="container-fluid" id="loginPage">
        <div class="row-fluid">
            <div class="span4 logo">
                    <img class="offset1 loginImg" src="img/loginpage/login_logo.png" alt="StreetSoaker Logo" />
            </div>
            <div class="offset1 span7 inputbox">
                    <form id="login">
                        <input type="text" class="span9" placeholder="username/email" id="username" tabindex=1 />
                        <input type="submit" class="btn btn-large btn-primary loginButton pull-right" tabindex=3 value="" /></br>
                        <input type="password" class="span9" placeholder="password" id="password" tabindex=2 /> 
                    </form>
                    <ul>
                        <li><a href="#">Forgot password</a></li>
                        <li><a href="register.html">Register account</a></li>
                    </ul>
            </div>
        </div>
    </div>
</body>
</html>