<!doctype html>
<html>
<head>
    <title>Streetsoaker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Css -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <!--<link href="css/bootstrap-responsive.css"   rel="stylesheet" />-->
    <link href="css/normalize.css" rel="stylesheet" />
    <link href="css/fonts.css" rel="stylesheet" />
    <link href="css/core.css" rel="stylesheet" />
    <!-- Javascript -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAEsqx3E9ITguWtbjptJCm6QkIs4fra4Fo&sensor=true"></script>
    <script src="js/markerwithlabel.js"></script>
    <script src="js/socket.io.min.js"></script>
    <script src="js/jquery-1.9.1.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-tab.js"></script>
    <script src="js/app.js"></script>
    <script>
        $(document).ready( function() {
            var sub_menu = 'closed';      
            
            function heightCalcFix(item, overflow){    
                var screenHeight = $(window).height();
                $(item).css({'height' : (screenHeight - overflow)});
            }

            function widthCalcFix(item, overflow){    
                var screenWidth = $(window).width();
                $(item).css({'width' : (screenWidth - overflow)});
            }

            function calcFix() {
                heightCalcFix('#menu', 40); 
                heightCalcFix('#sub_menu', 90); 
                heightCalcFix('#stats', 40);
                widthCalcFix('#sub_menu', $('#menu').width()+50);
            }

            calcFix();

            $(window).resize(function(){   
                calcFix();
            });
            
            $('#menu').click(function(e) {
                e.preventDefault();
                $(this).tab('show');
            });

            $('.menu_button').click(function(){
                $('#menu').toggle();
                $('#stats').hide();
                
                if(sub_menu == 'open'){
                    $('#sub_menu').toggle();
                    sub_menu = 'closed';
                }
            });

            $('.sub_menu_button').click(function(){
                if(sub_menu == 'closed'){    
                    $('#sub_menu').toggle();
                    sub_menu = 'open';
                }
            });
            $('.stats_button').click(function(){
                $('#stats').toggle();
                $('#menu').hide();
                if(sub_menu == 'open'){    
                    $('#sub_menu').toggle();
                    sub_menu = 'closed';
                }
            });



            /*
             * Countdown
             */
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

                    $('#time').html(showTime);

                }

            }

            timeClock = setInterval(function() {
                timer();
            }, 1000);



            /*
             * Logout
             */
            $('#menu ul li:last').click(function() {
                $.post('pages/logout.php', {'test':'test'}, function() {
                    history.go(0);
                });
            });
        });
        
        /*
         * Change password
         */
        function changePassword() {
            event.preventDefault();
            var password            = $('input[name="old_password"]').val();
            var passwordnew         = $('input[name="new_password"]').val();
            var passwordnewagain    = $('input[name="new_password_again"]').val();

            $.post('pages/changepassword.php', { 'password': password, 'newpassword': passwordnew, 'newpasswordagain': passwordnewagain }, function(data) {
                console.log(data);
            });
        }
        
        /*
         * Change mail
         */
        function changeMail() {
            event.preventDefault();
            var password            = $('input[name="password"]').val();
            var email         = $('input[name="new_email"]').val();

            $.post('pages/changemail.php', { 'password': password, 'email': email }, function(data) {
                console.log(data);
            });
        }
    </script>
</head>
<body>
        <div id="topbar">
            <div class="pull-left">
                <ul>
                    <li><a class="menu_button"><img src="img/ingamepage/button_menu.png" alt="menu/back button" class="line_right" /></a></li>
                    <li><?= $_SESSION['username'] ?></li>
                </ul>
            </div>
            <span id="status">
                Status: Dit is een status!
            </span>
            <div class="pull-right">
                <ul>
                    <li><img src="img/ingamepage/icon_stopwatch.png" alt="stopwatch icon" /></li>
                    <li id="time">14:25</li>
                    <li><a class="stats_button"><img src="img/ingamepage/button_stats.png" alt="stats button" class="line_left" /></a></li>
                </ul>
            </div>            
        </div>
        <aside id="menu">
            <ul>
                <li><img src="img/ingamepage/icon_skull.png" alt="Setting icon" /><a class="sub_menu_button" href="#killtab" data-toggle="tab">kill code</a></li>
                <li><img src="img/ingamepage/icon_settings.png" alt="Setting icon" /><a class="sub_menu_button" href="#settings" data-toggle="tab">Settings</a></li>
                <li><img src="img/ingamepage/icon_about.png" alt="About icon" /><a class="sub_menu_button" href="#about" data-toggle="tab">About us</a></li>
                <li><img src="img/ingamepage/icon_logout.png" alt="Logout icon" /><a href="#">Logout</a></li>
            </ul>
        </aside>
        <div id="sub_menu" class="tab-content">
            <div class="tab-pane" id="settings">
                <form method="post" id="changemailform" onsubmit="changeMail();">
                    <input type="password" name="password" placeholder="Password" required /></br>
                    <input type="email" name="new_email" placeholder="New email address" required /></br>
                    <input type="submit" name="changeemail_submit" value="Change Email" /></br>
                </form>
                <form method="post" id="changepassword_Form" onsubmit="changePassword();">
                    <input type="password" name="old_password" placeholder="Password" required /></br>
                    <input type="password" name="new_password" placeholder="New password" required /></br>
                    <input type="password" name="new_password_again" placeholder="Repeat new password" required /></br>
                    <input type="submit" name="changepassword_submit" value="Change password" /></br>
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
            <div class="tab-pane" id="killtab">
                <label>Your code:</label>
                <h1 id="deathcode">N/A</h1>
                <label>Your ID:</label>
                <h1 id="playerid">N/A</h1>
                <form method="post" id="kill" onsubmit="killEnemy();">
                    <input placeholder="Enemy ID" type="number" name="enemyid" limit="4"/></br>
                    <input placeholder="KillCode" type="number" name="killcode" limit="4"/></br>
                    <input type="submit" name="killcode_submit" value="Kill Enemy" />
                    <span id="invalidkillcode"></span>
                </form>
            </div>
        </div>
        <div id="map"></div>
        <aside id="stats">
            <section>
                <h2>Players</h2>
                <table>
                    <tr>
                        <td>Online:</td><td id="online">N/A</td>
                    </tr>
                </table>
            </section>
            <section>
                <h2>Account Stats:</h2>
                <table>
                    <tr>
                        <td>Kills:</td><td id="kills">N/A</td>
                    </tr>
                    <tr>
                        <td>Deads:</td><td id="deaths">N/A</td>
                    </tr>
                    <tr>
                        <td>KD Ratio:</td><td id="kdratio">N/A</td>
                    </tr>
                </table>
            </section>
        </aside>
</body>
</html>