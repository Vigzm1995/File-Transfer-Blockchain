<?php
define('const_max_retrieve_items', 1000);
require_once("rpc_function.php");
require_once("encode_decode.php");
require_once("split_merge.php");
$chain = "default";
$target_dir = "uploads/";
$src_dir = "downloads/";
$config=read_config();
set_multichain_chain($config[$chain]);
$max_upload_size=multichain_max_data_size()-512;
$myaddr = get_my_addr();
$myaddr = array_shift($myaddr);
if (!file_exists($target_dir.$myaddr.".ppk")) {
    $my_keys = generate_rsa_x($myaddr);
    $labels = multichain_labels();
    no_displayed_error_result($liststreams, multichain('liststreams', '*', true));
    no_displayed_error_result($getinfo, multichain('getinfo'));
    $subscribed = false;
    $viewstream = null;
    //subscribe to unsubscribed streams
    foreach ($liststreams as $stream) {
        if ($stream['subscribed'] == 1) {
            $my_stream[$stream['name']] = $stream['createtxid'];
        } else {
            if (no_displayed_error_result($result, multichain('subscribe', $stream['streamref']))) {
                output_success_text('Successfully subscribed to stream: ' . $stream['name']);
                $subscribed = true;
            }
        }
    }
    $recv_key = $my_keys[1];
    $recv_key = bin2hex($recv_key);
    $pubstream = $my_stream['publickeys'];
    if (no_displayed_error_result($publishtxid, multichain(
        'publishfrom', $myaddr,'publickeys','my_key'.$myaddr, $recv_key
    ))){
        echo "Published Publickey";
    }

}
$send_addrs = array();
if(no_displayed_error_result($items, multichain('liststreamkeyitems', 'log', $myaddr, true, const_max_retrieve_items))){
    foreach ($items as $item){
        array_push($send_addrs,hex2bin($item['data']));
    }
}
$index_data = array();
$data_array = array();
$file_array = array();
$file_name_array = array();
foreach ($send_addrs as $send_addr) {
    if(no_displayed_error_result($items, multichain('liststreampublisheritems', 'data', $send_addr, true, const_max_retrieve_items))){
        foreach ($items as $item){
            if(!is_array($item['data'])){

                if(strpos($item['key'],$myaddr)!==false){
                    array_push($index_data,$item['key']);
                    $key = $item['key'];
                    $file_array[$key] = explode($myaddr,$key)[1];

                    $data_array[$key]=hex2bin($item['data']['txid']);
                }
            }else{
                if(strpos($item['key'],$myaddr)!==false){
                    array_push($index_data,$item['key']);
                    $key = $item['key'];
                    $file_array[$key] = explode($myaddr,$key)[1];
                    $data_array[$key]=hex2bin($item['data']['txid']);
                }
            }
        }
    }
}
$file_name_array[$myaddr]=array();
foreach ($file_array as $file){
    array_push($file_name_array[$myaddr],substr($file,0,-2));
}
if(isset($_GET['file'])&&isset($_GET['addr'])){
    if($_GET['addr']==$myaddr){
        $static = 0;
        $fname_file = $_GET['file'];
        for($i=0;$i<5;$i++){
            $index = $myaddr.$fname_file."_$i";
            $content = $data_array[$index];
            write_file($src_dir.$fname_file."_$i",$content);
            $privatekey = file_get_contents($target_dir.$myaddr.".ppk");
            if(no_displayed_error_result($items, multichain('liststreampublisheritems', 'access', '*', true, const_max_retrieve_items))){
                foreach ($items as $item) {
                    if(strpos($item['key'],$myaddr."_file_".$fname_file)!==false){
                        $dec_key = $item['data'];
                        $dec_key = hex2bin($dec_key);
                        $dkey = decrypt_rsa_x($privatekey,$dec_key);
                        $dfile = decrypt_x($dkey,$src_dir.$fname_file."_$i");
                        write_file($src_dir.$fname_file."_$i",$dfile);
                    }
                }
            }

        }
        merge_file($src_dir.$fname_file,5,$src_dir.$fname_file);
        echo "Downloaded";

    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receiver - BlockChain File Transfer</title>
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
            <li><a href="index.php">Be Sender</a></li>
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
    <div class="row">
        <div class="col s8 offset-s2">
            <div class="card-panel light-blue lighten-5 center-align">
                  <span class="teal-text flow-text">
                      Files available to download: <br>
                      <?php
                      foreach (array_unique($file_name_array[$myaddr]) as $file){
                          echo "<a href='receiver.php?addr=".$myaddr."&file=".$file."'>".$file."</a><br>";
                      }
                      ?>

                  </span>
            </div>
        </div>
    </div>
</div>
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
</body>
</html>
