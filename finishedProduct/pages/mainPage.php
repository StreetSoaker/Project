<?php

?>
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
    <script src="js/bootstrap-tab.js"></script>
    <script>
        $(document).ready( function() {
            var time = '15:00';

            $('#menu').click(function (e) {
                e.preventDefault();
                $(this).tab('show');
            });

            function timer() {

                timeArray = time.split(':');
                if(timeArray[1] == '') {
                    time = timeArray[0]+':';
                } else {

                    parseInt(timeArray[0]);
                    parseInt(timeArray[1]);

                    if(timeArray[1] == 00) {
                        timeArray[1] = 59;
                        timeArray[0]--;
                    } else {
                        timeArray[1]--;
                    }
                    if(timeArray[0] == 00 && timeArray[1] == 00) {
                        timeArray[0] = 'Waiting for server response!';
                        timeArray[1] = '';
                    }
                    if(timeArray[1] < 10) {
                        timeArray[1] = '0' + timeArray[1];
                    }

                    if(timeArray[1] === '') {
                        time = timeArray[0]+':';
                        showTime = timeArray[0];
                    } else {
                        time = timeArray[0]+':'+timeArray[1];
                        showTime = timeArray[0]+':'+timeArray[1];
                    }

                    $('.pull-right ul li:first').html('<img src="img/ingamepage/icon_stopwatch.png" alt="stopwatch icon" />'+showTime);

                }

            }

            timeClock = setInterval(function() {
                timer();
            }, 1000);

        });
    </script>
    <style>
        
    </style>
</head>
<body>
        <div id="topbar">
            <div class="pull-left">
                <ul>
                    <li><a href="#"><img src="img/ingamepage/button_menu.png" alt="menu/back button" class="line_right" /></a></li>
                    <li>Finn105</li>
                </ul>
            </div>
            <div class="pull-right">
                <ul>
                    <li><img src="img/ingamepage/icon_stopwatch.png" alt="stopwatch icon" />15:00</li>
                    <li><a href="#"><img src="img/ingamepage/button_stats.png" alt="stats button" class="line_left" /></a></li>
                </ul>
            </div>            
        </div>
        <aside id="menu">
            <ul>
                <li><img src="img/ingamepage/icon_settings.png" alt="Setting icon" /><a href="#settings" data-toggle="tab">Settings</a></li>
                <li><img src="img/ingamepage/icon_about.png" alt="About icon" /><a href="#about" data-toggle="tab">About us</a></li>
                <li><img src="img/ingamepage/icon_logout.png" alt="Logout icon" /><a href="#">Logout</a></li>
            </ul>
        </aside>
        <div id="sub_menu" class="tab-content">
            <div class="tab-pane active" id="settings">
                <form>
                    <span class="inputnametag">Nickname</span><input type="text" name="nickname" value="Finn105" /></br>
                    <button>change nickname</button>
                </form>
                <form>
                    <span class="inputnametag">Password</span><input type="password" name="password" value="haahahah"/></br>
                    <button>change nickname</button>
                </form>
            </div>
            <div class="tab-pane" id="about">
                <b>Streetsoaker</b>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse rutrum molestie dolor lobortis feugiat. In dui ipsum, pretium ac cursus non, laoreet in nisi. Proin tincidunt, sapien nec ultricies mollis.</p>
                
                <b>Robin Valk</b>
                <p>est diam ultricies orci, ut volutpat dolor dolor eu odio. Nunc eros justo, fermentum ac egestas eu, dignissim ut ligula. Etiam at nunc vel purus vehicula pharetra. </p>
                
                <b>Joeri Aben</b>
                <p>Sed congue, felis et iaculis consequat, dui ipsum pulvinar justo, sed aliquam sem est vel dui. Mauris eu lacus quis velit porta tincidunt. Proin sollicitudin gravida viverra. </p>
                
                <b>Koen van den Heuvel</b>
                <p>lectus neque congue quam, nec accumsan magna augue sed turpis. Sed ac nulla a odio mattis bibendum nec placerat lorem. Ut imperdiet scelerisque aliquam. </p>    
            </div>
        </div>
        <div id="map"></div>
</body>
</html>