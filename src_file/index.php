<?php
    require_once "rpc_function.php";
    $chain = "default";
    $config=read_config();
    set_multichain_chain($config[$chain]);
    $max_upload_size=multichain_max_data_size()-512;
    $myaddr = get_my_addr();
$myaddr = array_shift($myaddr);
?>
<!DOCTYPE html>
<html>
<head>
    <title>BlockChain File Transfer</title>
    <!--Import Google Icon Font-->
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="css/style.css"  media="screen,projection"/>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>
    <nav>
        <div class="nav-wrapper light-blue">
            <a href="#" class="brand-logo"><i class="large material-icons" style="font-size: 52px;">settings_ethernet</i>BlockChain FileTransfer</a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="receiver.php">Be Receiver</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <!-- Page Content goes here -->
        <div class="row">
            <div class="col s8 offset-s2">
                <div class="card-panel light-blue lighten-5 center-align">
                  <span class="teal-text flow-text">
                      My address is: <br>
                      <?php
                            echo $myaddr."<br>";

                      ?>

                  </span>
                </div>
            </div>
        </div>
        <form method="post" action="upload.php" enctype="multipart/form-data">
            <div class="row">
                <div class="col s8 offset-s2">
                    <div class="card-panel light-blue lighten-5 center-align">
                  <span class="teal-text flow-text">
                      Enter the address of the Receiver:
                      <div class="input-field inline">
                        <input id="recvaddr" name="recvaddr" type="text" class="validate" required>
                      </div>
                  </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s8 offset-s2">
                    <div class="card-panel light-blue lighten-5 center-align">
                  <span class="teal-text flow-text">
                      Upload the file <?php echo "(Max. file size : ".floor($max_upload_size/1024)."KB)"; ?>:
                      <div class="file-field input-field">
                          <div class="btn">
                            <span>File</span>
                            <input name="file" type="file">
                          </div>
                          <div class="file-path-wrapper">
                            <input name="fname" class="file-path validate" required type="text">
                          </div>
                        </div>
                  </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s8 offset-s2">
                    <div class="card-panel light-blue lighten-5 center-align">
                  <span class="teal-text flow-text">
                      <button class="waves-effect waves-light btn-large" name="submit" type="submit"><i class="material-icons right" style="font-size: 52px;">present_to_all</i>Send</button>
                  </span>
                    </div>
                </div>
            </div>
        </form>

    </div>
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
</body>
</html>