<?php

include 'libraries/MobiSync.php';
$obj = new MobiSync();
$surv = $obj->getActiveSurveys();

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
    <script src="js/main.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        $('#remoteUpdate').click(function(){
            $.ajax({
                type:'GET',
                url: 'entry.php',
                data: { action: 'update'},
                beforeSend: function(){
                    $('#loadingImg').css('visibility','visible');
                },
                success: function(data){
                    $('#loadingImg').css('visibility','hidden');
                    alert(data);
                },
                
                
            });
        });
    });
    </script>
    </head>
    <body>
        
        <div class="row">
            <nav class="navbar navbar-fixed-top navbar-default" role="navigation">
                <p class="navbar-brand"><?php echo MobiCore::getAccountName(); ?></p>
            </nav>

        </div>
       <div class="body-wrapper">
           <div class="container" style="margin-top: 85px; margin-bottom: 65px;">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h1 class="panel-title">Active Surveys</h1>
                </div>
                <div class="list-group">
                    <?php
                    foreach ( $surv as $survey)
                    {
                        $title = $survey[1];
                        $sid = $survey[0];
                        
                        $q = $obj->getSurveyStats($sid);
                        $total = $q['total'];
                        $url = $obj->getSurveyUrl($sid);
                        echo '<a href="details.php?sid='.$sid.'" class="list-group-item"><span class="badge">'.$total.'</span>'.$title.'</a>';
                    }
                    ?>
                    <a href="#" class="list-group-item"><span class="badge">42</span>Cras justo odio</a>
                    <a href="#" class="list-group-item">Dapibus ac facilisis in</a>
                    <a href="#" class="list-group-item">Morbi leo risus</a>
                    <a href="#" class="list-group-item">Porta ac consectetur ac</a>
                    <a href="#" class="list-group-item">Vestibulum at eros</a>
                 </div>
            </div>
            </div>
        </div>
        
        <nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
  <p class="navbar-text">
  <ul class="pager">
      <li><a id="remoteUpdate" class="alert-success" href="#"><span class="glyphicon glyphicon-download"></span> Download Updates</a></li> 
      <img id="loadingImg" src="img/loading.gif" style="width: 30px; height: 30px; visibility: hidden;" />
</ul>

  </p>

</nav>

    </body>
</html>