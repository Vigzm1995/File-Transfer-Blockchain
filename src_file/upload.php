<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 20/3/17
 * Time: 9:56 PM
 */
define('const_max_retrieve_items', 1000);
$target_dir = "uploads/";
require_once("rpc_function.php");
require_once("encode_decode.php");
require_once("split_merge.php");
$chain = "default";
$recv_addr = $_POST['recvaddr'];
$config=read_config();
set_multichain_chain($config[$chain]);
$max_upload_size=multichain_max_data_size()-512;
$max_upload_size*=3;
$fname = $_POST['fname'];
$target_file = $target_dir . $fname;
$upload_file = $_FILES['file']['tmp_name'];
$myaddr = get_my_addr();
$myaddr = array_shift($myaddr);
$my_stream = array();
$get_txid = array();
if(isset($_POST["submit"])) {
    $upload_size = $_FILES['file']['size'];
    if ($upload_size<$max_upload_size) {
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        if (move_uploaded_file($upload_file, $target_file)) {
            //File splitting
            split_file($target_file, 5);
            //Encrypting
            //Getting receiver key from stream
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
            $txids = array();
            $recv_pub;
            $viewstream = $my_stream['publickeys'];
            $success = no_displayed_error_result($items, multichain('liststreampublisheritems', $viewstream, $recv_addr, true, const_max_retrieve_items));
            foreach ($items as $item) {
                if($item[key]=="my_key".$recv_addr)
                    $recv_pub = $item['data'];
            }
            if(!isset($recv_pub)){
                die("Wrong address");
            }
            $recv_pub = hex2bin($recv_pub);
            $key = generateRandomString();
            for ($i = 0; $i < 5; $i++) {
                $fn = $fname . "_" . $i;
                $cfile = encrypt_x($target_dir.$fn,$key);
                write_file($target_dir.$fn,$cfile);
                $send_content = bin2hex($cfile);
                $send_fname = $recv_addr.$fn;
                if (no_displayed_error_result($publishtxid, multichain(
                    'publishfrom', $myaddr,'data',$send_fname, $send_content
                ))){
                    array_push($txids,$publishtxid);
                    echo "success";

                }
            }
            $encrypt_key = encrypt_rsa_x($recv_pub,$key);
            $encrypt_key=bin2hex($encrypt_key);
            $send_fname = $myaddr."_key_".$recv_addr."_file_".$fname;
            if (no_displayed_error_result($publishtxid, multichain(
                'publishfrom', $myaddr,'access',$send_fname, $encrypt_key
            ))){
                echo "Send";
            }
            $sender = $myaddr;
            $receiver = $recv_addr;
            $sender = bin2hex($sender);
            if (no_displayed_error_result($publishtxid, multichain(
                'publishfrom', $myaddr,'log',$receiver, $sender
            ))){
                echo "Send";
            }

        }
    }
}
