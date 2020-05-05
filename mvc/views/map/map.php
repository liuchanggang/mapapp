<!DOCTYPE html>
<html lang="en">
<head>
<title>Listing markers in clusters</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

<script src='https://api.mapbox.com/mapbox.js/v3.3.0/mapbox.js'></script>
<link href='https://api.mapbox.com/mapbox.js/v3.3.0/mapbox.css' rel='stylesheet' />

<title>Agency - Start Bootstrap Theme</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<!-- Bootstrap core CSS -->
<link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom fonts for this template -->
<link href="/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Kaushan+Script" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css">
<!-- Custom styles for this template -->
<link href="/css/agency.min.css" rel="stylesheet">
</head>
<body id="page-top">
<section class="page-section" id="services">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-left">
              <h2 class="section-heading">Restaurant near me</h2>
            </div>
        </div>
        <form id="systemForm">
        <div class="row">
            <div class="col-lg-3 text-left">
                <input class="form-control" id="zipcode" placeholder="Location" value="">
            </div>
            <div class="col-lg-3 text-left">
                <button id="search" class="btn btn-primary btn-s text-uppercase" type="button">Search</button><span id="searching" style="display:none;"><img src="/images/ajax-loader.gif"></span>
            </div>
            <div class="col-lg-6 text-right">
              <a href="#" id="myfav">My Favorites</a>
            </div>
            <div class="col-lg-12 left">
              Search for open restaurants
            </div>
            <div class="col-lg-12 left" id="favdiv">
            </div>
        </div>
        </form>
        <div >
            <p><p><p><iframe id="mapframe" frameBorder="0" height="400" width="100%" src=""></iframe>
        </div>
    </div>
</section>
<script>

$(function(){
    $(document).keypress(function(event){
      if (event.which == '13') {
        event.preventDefault();
        $("#search").click();
      }
    });
    $("#search").click(function(){
        $('#favdiv').html('');
        $("#searching").show();
        $.ajax({
            url: '/mvc/index.php?pkg=map&contr=map&event=map2&zipcode=' + $('#zipcode').val(),
            type: "GET",
            data:$('#systemForm').serialize(),
            dataType: "JSON",
            success: function(response){
                $("#searching").hide();
                if (response.success) {
                    $('#mapframe').attr("srcdoc", response.msg);
                }
                if (!response.success && response.code == 'logout')
                {
                    window.location.href = '/login.php';
                }
            }
	});

    });
    $("#myfav").click(function(){
        $('#mapframe').attr("srcdoc", '');
        $.ajax({
            url: '/mvc/index.php?pkg=map&contr=map&event=getfav',
            type: "GET",
            dataType: "JSON",
            success: function(response){
                if (response.success) {
                    $('#favdiv').html(response.msg);
                }
                if (!response.success && response.code == 'logout')
                {
                    window.location.href = '/login.php';
                }
            }
	});
    });
})
function saveBusiness(id) {
    $.ajax({
        url: '/mvc/index.php?pkg=map&contr=map&event=save',
        type: "POST",
        data:{busid:id},
        dataType: "JSON",
        success: function(response){
            if (response.success) {
                alert('Save successfully!');
            }
            if (!response.success && response.code == 'logout')
            {
                window.location.href = '/login.php';
            }
        }
    });
}
</script>
</html>