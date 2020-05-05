
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Maptool login</title>

  <!-- Bootstrap core CSS -->
  <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom fonts for this template -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <link href="/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
  <link href='https://fonts.googleapis.com/css?family=Kaushan+Script' rel='stylesheet' type='text/css'>
  <link href='https://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
  <link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700' rel='stylesheet' type='text/css'>

</head>
<body id="page-top">
<section class="page-section" id="contact">
    <div class="container" id="login">
        <div class="row">
            <div class="col-lg-12 text-center">
              <h2 class="section-heading text-uppercase">Login</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <form id="loginform" action="/mvc/index.php?pkg=fx&contr=datafeed&event=login" method="post" name="sentMessage" novalidate="novalidate">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <input class="form-control" id="email" name="email" type="email" placeholder="Your email" required="required" data-validation-required-message="Please enter your code.">
                              <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <input class="form-control" id="password" name="password" type="password" placeholder="Your password" required="required" data-validation-required-message="Please enter your code.">
                              <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6 text-right">
                            <div id="success"></div>
                            <button id="sendMessageButton" class="btn btn-primary btn-xl text-uppercase" type="button">Login</button>
                        </div>
                        <div class="col-md-6 text-left">
                            <div id="success"></div>
                            <a href="#" id="toreg">Create Account</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container" id="register" style="display:none;">
        <div class="row">
            <div class="col-lg-12 text-center">
              <h2 class="section-heading text-uppercase">Create Account</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <form id="regform" action="/mvc/index.php?pkg=fx&contr=datafeed&event=login" method="post" name="sentMessage" novalidate="novalidate">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <input class="form-control" id="email2" name="email" type="email" placeholder="Your email" required="required" data-validation-required-message="Please enter your code.">
                              <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <input class="form-control" id="password2" name="password" type="password" placeholder="Your password" required="required" data-validation-required-message="Please enter your code.">
                              <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <input class="form-control" id="password-re" name="password-re" type="password" placeholder="Confirm password" required="required" data-validation-required-message="Please enter your code.">
                              <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-lg-12 text-center">
                            <div id="success"></div>
                            <button id="regButton" class="btn btn-primary btn-xl text-uppercase" type="button">Register</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
</body>
</html>
<script>
$(function(){
    $("#sendMessageButton").click(function(){
        var email = $('#email').val();
        email = $.trim(email);
        var password = $('#password').val();
        if(email.length ==0) {
            alert('Please input a valid email');
            return false;
        }
        if(password.length ==0) {
            alert('Please input a valid password');
            return false;
        }
        $.ajax({
            url: "/mvc/index.php?pkg=map&contr=user&event=login",
            type: "POST",
            data:$('#loginform').serialize(),
            dataType: "JSON",
            success: function(response){
                if(response.success) {
                    window.location.href = '/mvc/index.php?pkg=map&contr=map&event=map';
                }else{
                    alert('Login failed. Please check your login info and try again');
                }
            }
	});
    });
    $("#toreg").click(function(){
        $("#login").hide();
        $("#register").show();
        return false;
    });
    $("#regButton").click(function(){
        var email = $('#email2').val();
        email = $.trim(email);
        var password = $('#password2').val();
        var password_re = $('#password-re').val();
        if(email.length ==0) {
            alert('Please input a valid email');
            return false;
        }
        if(password.length ==0) {
            alert('Please input a valid password');
            return false;
        }
        if(password != password_re) {
            alert('Password input are not same');
            return false;
        }
        $.ajax({
            url: "/mvc/index.php?pkg=map&contr=user&event=register",
            type: "POST",
            data:$('#regform').serialize(),
            dataType: "JSON",
            success: function(response){
                if(response.success) {
                    window.location.href = '/mvc/index.php?pkg=map&contr=map&event=map';
                }else{
                    alert(response.msg);
                }
            }
	});
    });
})
</script>
