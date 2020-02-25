<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/3/17
 * Time: 12:02 AM
 */
include('Crypt/AES.php');
include('Crypt/Random.php');
include('Crypt/RSA.php');
define('CRYPT_RSA_PKCS15_COMPAT', true);
function encode_x($filename){
    $file = file_get_contents($filename);
    return bin2hex($file);
}
function decode_x($filename,$data){
    $data = hex2bin($data);
    $file = file_put_contents($filename,$data);
    return $file;
}
function encrypt_x($filename,$key){
    $cipher = new Crypt_AES(CRYPT_AES_MODE_CTR);
    $cipher->setKey($key);
//    $cipher->setIV(crypt_random_string($cipher->getBlockLength() >> 3));
//    $data = encode_x($filename);
        $data = file_get_contents($filename);
    return $cipher->encrypt($data);
}
function decrypt_x($key,$filename){
    $cipher = new Crypt_AES(CRYPT_AES_MODE_CTR);
    $cipher->setKey($key);
//    $cipher->setIV(crypt_random_string($cipher->getBlockLength() >> 3));
    $filedata = file_get_contents($filename);
    $data = $cipher->decrypt($filedata);
//    $data = decode_x($filename,$data);
    return $data;
}
function generate_rsa_x($myaddr){
    $rsa = new Crypt_RSA();
    $rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_OPENSSH);
    extract($rsa->createKey(1024));
    $target_dir = "uploads/";
    $handle = fopen($target_dir.$myaddr.".ppk", 'wb') or die("error opening file");
    fwrite($handle,$privatekey) or die("error writing file");
    fclose($handle) or die("error closing file handle");
    $handle = fopen($target_dir.$myaddr.".pem", 'wb') or die("error opening file");
    fwrite($handle,$publickey) or die("error writing file");
    fclose($handle) or die("error closing file handle");
    return array($privatekey,$publickey);
}
function write_file($filename,$data){
    file_put_contents($filename,$data);
}
function encrypt_rsa_x($pubkey,$key){
    $rsa = new Crypt_RSA();
    $rsa->loadKey($pubkey);
    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    return $rsa->encrypt($key);
}
function decrypt_rsa_x($privatekey,$key){
    $rsa = new Crypt_RSA();
    $rsa->loadKey($privatekey);
    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    return $rsa->decrypt($key);
}
function generateRandomString($length = 24) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}