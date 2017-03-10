?><!DOCTYPE html> 
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title><?php echo (!empty($GLOBALS['project_info']['title']) ? $GLOBALS['project_info']['title'] .' - ' : ''); ?><?php echo ($GLOBALS['project_info']['name']); ?></title>

    <link rel="shortcut icon" href="/favicon.ico">
    <!--<link rel="apple-touch-icon" href="apple-touch-icon.png">-->

    <!-- CSS -->
<?php
    //add_css('reset.css',2);
    add_css('global.css',2);
    echo template_css();
?>
    <!-- Javascript -->
    <!-- asynchronous google analytics change the UA-XXXXX-X to be your site's ID -->
    <!--
    <script type='text/javascript'>
    var _gaq = [['_setAccount', 'UA-XXXXX-X'], ['_trackPageview']];
    (function(d, t) {
        var g = d.createElement(t),
        s = d.getElementsByTagName(t)[0];
        g.async = true;
        g.src = '//www.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g, s);
        })(document, 'script');
    </script>
    -->
<?php
    add_js('global.js',1);
    echo template_js();
?>

<style type="text/css" media="screen">
html, body { height: 100%;}
body {
    font: 13px 'Verdana', Helvetica, 'Trebuchet MS', Arial ;    
    margin: 0;
    /*background-color: #272D39;*/
    background-color: #fefefe;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#eeeeee), to(#fefefe));
    background-image: -webkit-linear-gradient(top, #eeeeee, #fefefe);
    background-image: -moz-linear-gradient(top, #eeeeee, #fefefe);
    background-image: -ms-linear-gradient(top, #eeeeee, #fefefe);
    background-image: -o-linear-gradient(top, #eeeeee, #fefefe);
    background-image: linear-gradient(top, #eeeeee, #fefefe);    
}

/*--------------------*/

#login {
    background-color: #fff;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#eee));
    background-image: -webkit-linear-gradient(top, #fff, #eee);
    background-image: -moz-linear-gradient(top, #fff, #eee);
    background-image: -ms-linear-gradient(top, #fff, #eee);
    background-image: -o-linear-gradient(top, #fff, #eee);
    background-image: linear-gradient(top, #fff, #eee);  
    height: 250px;
    width: 400px;
    margin: -200px 0 0 -225px;
    padding: 25px;
    position: relative;
    top: 50%;
    left: 50%;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    -moz-box-shadow:    0px 0px 20px 5px #000000;
    -webkit-box-shadow: 0px 0px 20px 5px #000000;
    box-shadow:         0px 0px 20px 5px #000000;
}


/*--------------------*/

h1 {
    text-transform: uppercase;
    text-align: center;
    color: #666;
    margin: 0 0 30px 0;
    letter-spacing: 4px;
    font: normal 26px/1 Verdana, Helvetica;
    position: relative;
}

/*--------------------*/

fieldset {
    border: 0;
    padding: 0;
    margin: 0;
}

/*--------------------*/

#inputs input {
    background: #eeeeee url(images/login-sprite.png) no-repeat;
    padding: 15px 15px 15px 30px;
    margin: 0 0 10px 0;
    width: 350px;
    border: 1px solid #ccc;
    font: 13px Verdana, Helvetica, Arial;

    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;

    -moz-box-shadow: 0 1px 1px #ccc inset, 0 1px 0 #fff;
    -webkit-box-shadow: 0 1px 1px #ccc inset, 0 1px 0 #fff;
    box-shadow: 0 1px 1px #ccc inset, 0 1px 0 #fff;
}

#inputs input.login { background-position: 5px -2px; }
#inputs input.pass { background-position: 5px -50px; }

#username {
    background-position: 5px -2px !important;
}

#password {
    background-position: 5px -52px !important;
}

#inputs input:focus {
    background-color: #fff;
    border-color: #E3B176;
    outline: none;
    -moz-box-shadow: 0 0 0 1px #E3B176 inset;
    -webkit-box-shadow: 0 0 0 1px #E3B176 inset;
    box-shadow: 0 0 0 1px #E3B176 inset;
}

/*--------------------*/
#actions {
    margin: 25px 0 0 0;
}

#submit {
    /* dd7700 original: fddb6f,ffb94b, */
    background-color: #ffb94b;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#fddb6f), to(#ffb94b));
    background-image: -webkit-linear-gradient(top, #fddb6f, #ffb94b);
    background-image: -moz-linear-gradient(top, #fddb6f, #ffb94b);
    background-image: -ms-linear-gradient(top, #fddb6f, #ffb94b);
    background-image: -o-linear-gradient(top, #fddb6f, #ffb94b);
    background-image: linear-gradient(top, #fddb6f, #ffb94b);
    
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    
    text-shadow: 0 1px 0 rgba(255,255,255,0.5);
    
     -moz-box-shadow: 0 0 1px rgba(0, 0, 0, 0.3), 0 1px 0 rgba(255, 255, 255, 0.3) inset;
     -webkit-box-shadow: 0 0 1px rgba(0, 0, 0, 0.3), 0 1px 0 rgba(255, 255, 255, 0.3) inset;
     box-shadow: 0 0 1px rgba(0, 0, 0, 0.3), 0 1px 0 rgba(255, 255, 255, 0.3) inset;    
    
    border-width: 1px;
    border-style: solid;
    border-color: #d69e31 #e3a037 #d5982d #e3a037;

    height: 35px;
    padding: 0;
    width: 120px;
    cursor: pointer;
    font: bold 13px Verdana, Helvetica, Arial;
    color: #8f5a0a;
}

#submit:hover,#submit:focus {		
    background-color: #fddb6f;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#ffb94b), to(#fddb6f));
    background-image: -webkit-linear-gradient(top, #ffb94b, #fddb6f);
    background-image: -moz-linear-gradient(top, #ffb94b, #fddb6f);
    background-image: -ms-linear-gradient(top, #ffb94b, #fddb6f);
    background-image: -o-linear-gradient(top, #ffb94b, #fddb6f);
    background-image: linear-gradient(top, #ffb94b, #fddb6f);
}	

#submit:active {		
    outline: none;
   
     -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.5) inset;
     -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.5) inset;
     box-shadow: 0 1px 4px rgba(0, 0, 0, 0.5) inset;		
}

#submit::-moz-focus-inner {
  border: none;
}

#actions a{
    color: #3151A2;    
    float: right;
    line-height: 35px;
    margin-left: 20px;
}
</style>
</head>
<body<?php echo (template_onload().template_onunload()); ?>>

<div id="login" >
    <!--Start Body Content-->
    <?php echo $body; ?>
    <!--End Body Content-->
</div>

<div class='clear'></div>
<?php echo ($GLOBALS['debug_options']['enabled'] == 1 ? show_debug() : ''); ?>

</body>
</html>