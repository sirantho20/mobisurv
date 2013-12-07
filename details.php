<?php

include 'libraries/MobiSync.php';
$obj = new MobiSync();
$surv = $obj->getActiveSurveys();
$title = $surv[0][1];
$sid = filter_input(INPUT_GET, 'sid');
$survey = $obj->getSurveyStats($sid);

$total = $survey['total'];
$incomplete = $survey['incomplete'];
$complete = $survey['complete'];

$url = $obj->getSurveyUrl($sid);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Mobisurv</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#uploadSurveyData').click(function(){
                $.ajax({
                type:'GET',
                url: 'entry.php',
                data: { action: 'push', sid: '<?php echo $sid; ?>'},
                beforeSend: function(){
                    $('#loadingImg').css('visibility','visible');
                },
                success: function(data){
                    $('#loadingImg').css('visibility','hidden');
                    alert(data);
                    window.location = "index.php";
                }
            });
            });
        });
    </script>
    </head>
    <body>
        
        <div class="row">
            <nav class="navbar navbar-default" role="navigation">
                <p class="navbar-brand"><?php echo MobiCore::getAccountName(); ?></p>
            </nav>

        </div>
       <div class="body-wrapper">
            <div class="container">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h1 class="panel-title"><?php echo $title; ?></h1>
                </div>
                <div class="list-group">
                    <a href="#" class="list-group-item">Completed Response<span class="badge alert-success"><?php echo $complete; ?></span></a>
                    <a href="#" class="list-group-item">Incomplete Response<span class="badge alert-danger"><?php echo $incomplete; ?></span></a>
                    <a href="#" class="list-group-item">Total Answered<span class="badge alert-link"><?php echo $total; ?></span></a>
                 </div>
            </div>
            </div>
        </div>
        
        <nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
  <p class="navbar-text">
  <ul class="pager">
  <li><a href="index.php"><span class="glyphicon glyphicon-chevron-left"></span> Back</a></li>
  <li><a class="alert-success" href="<?php echo $url; ?>"><span class="glyphicon glyphicon-play-circle"></span> Start Survey</a></li>
  <li><a id="uploadSurveyData" class="alert-warning" href="<?php echo $url; ?>"><span class="glyphicon glyphicon-upload"></span> Upload</a></li>
</ul>

  </p>

</nav>

    </body>
</html>