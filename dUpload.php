<?php
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
include __DIR__."/Classes/SGSAv2downgrade.php";
$raw_path  = __DIR__.'/raw_files/';
$results_path = __DIR__.'/results/';

if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
{
    $dir_name = md5(uniqid(rand(), true));
    mkdir($raw_path.$dir_name);
    mkdir($results_path.$dir_name);
    $analysisObj = new SGSAv2downgrade();
    
    foreach ($_FILES['inputFiles']['name'] as $f => $name) 
    {  
        if ($_FILES['inputFiles']['error'][$f] == 4) 
        {
            continue; // Skip file if any error found
        }	
        //var_dump($name);
        if(move_uploaded_file($_FILES["inputFiles"]["tmp_name"][$f], $raw_path.$dir_name.'/'.$name)) 
        {
            $analysisObj->process($raw_path.$dir_name.'/'.$name);
            $analysisObj->write($results_path.$dir_name.'/'.$name);
            $results_array[$results_path.$dir_name.'/'.$name] = array("file"=>$results_path.$dir_name.'/'.$name);
        }             
    }

    $zip = new ZipArchive;
    $zip_name = $results_path.$dir_name.'/'.$dir_name.'.zip';
    $zip->open($zip_name, ZipArchive::CREATE);
    foreach($results_array as $file => $data)
    {
        $zip->addFile($data['file'],basename($data['file']));
    }
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=Results.zip');
    header('Content-Length: ' . filesize($zip_name));
    readfile($zip_name);
}


?>