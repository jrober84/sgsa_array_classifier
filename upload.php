<?php
error_reporting(E_ALL);
set_time_limit (0);
ini_set('display_errors', 0);
date_default_timezone_set('UTC');


include __DIR__."/Classes/buildProfile.php";
$kauff_file = __DIR__.'/DataFiles/serovars.txt';
$raw_path  = __DIR__.'/raw_files/';
$results_path = __DIR__.'/results/';
$profile_file = __DIR__.'/DataFiles/SGSA_V2_Data.txt';
$probe_config = __DIR__.'/DataFiles/probe.config';


if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
{
    $dir_name = md5(uniqid(rand(), true));
    mkdir($raw_path.$dir_name);
    mkdir($results_path.$dir_name);
    $classification_obj = new buildProfile($profile_file,$probe_config);
    $serovar_obj = new serovarLookUp($kauff_file);

    foreach ($_FILES['inputFiles']['name'] as $f => $name) 
    {  
        if ($_FILES['inputFiles']['error'][$f] == 4) 
        {
            continue; // Skip file if any error found
        }	

        if(move_uploaded_file($_FILES["inputFiles"]["tmp_name"][$f], $raw_path.$dir_name.'/'.$name)) 
        {

            $raw = new readRawData($raw_path.$dir_name.'/'.$name);
            $data = $classification_obj->classify($raw->data, $classification_obj->profiles,$classification_obj->probe_obj );
            #var_dump($data);
            if ($data["O"] == 'B' && $data["H1"] == 'i' && $data["H2"] == '1,5')
            {
           		$data['H2'] = '1,2';

            }
            if ($data['O'] == 'B' && $data['H1'] == 'r' && $data['H2'] == '1,5')
            {
           		$data['H2'] = '1,2';
            }
            #var_dump($data);
            $serovar = $serovar_obj->getSerovar($data['O'],$data['H1'],$data['H2']);
            
            
            $well_postiion = preg_replace("/\..+/","",$name);
            $antigenic_formula = '';
            $serovar_predicted = '';
            $predSubspecies = $data['Subspecies'];
            $ssp_array = array();
    
            foreach($serovar as $sname => $info)
            {
                $formula= $info['formula'];
                $subspecies = $info['subspecies'];     
                $ssp_array[$subspecies] = '';
            }
            
            
            foreach($serovar as $sname => $info)
            {
                $formula= $info['formula'];
                $subspecies = $info['subspecies'];
                if($sname == 'Enteritidis' && array_key_exists('Enteriditis',$data) && $data['Enteriditis'] == TRUE)
                {
                    $antigenic_formula = $formula;
                    $serovar_predicted = 'Enteritidis   ';
                    break;       
                }
                
                if(($sname == 'Oranienburg' || $sname == 'Othmarschen') && $predSubspecies == 'I')
                {
                	if($data['Oranienburg'] == TRUE)
                	{
                		$serovar_predicted = 'Oranienburg   ';
                		$antigenic_formula = $formula;
                		
                	}
                	else{
                		$serovar_predicted = 'Othmarschen   ';
                		$antigenic_formula = $formula;                	
                	}

                    break;       
                }

                if($sname == 'Paratyphi C' || $sname == 'Choleraesuis')
                {
                	if($data['ParatyphiC'] == TRUE)
                	{
                		$serovar_predicted = 'Paratyphi C   ';
                		$antigenic_formula = $formula;
                		break;
                	}
                	if($data['VI'] == TRUE)
                	{
                		$serovar_predicted = 'Paratyphi C   ';
                		$antigenic_formula = $formula;
                		
                	}
                	else{
                		$serovar_predicted = 'Choleraesuis   ';
                		$antigenic_formula = $formula;                	
                	
                	}
                	
                	break;

                           
                }
                
                if(count($serovar) == 1)
                {
                    $antigenic_formula = $formula;
                }
                if(count($serovar) > 1 && $predSubspecies != 'N/A')
                {
                    if($predSubspecies != $subspecies)
                    {
                        continue;
                    }
                }
                $serovar_predicted.= $sname." | ";
            }
            
            if($serovar_predicted != '')
            {
                $serovar_predicted = substr($serovar_predicted,0,strlen($serovar_predicted)-3);
            }

            

            $results = array();
            $results["File"] = $results_path.$dir_name.'/'.$name;
            
            $results['Well ID'] = preg_replace("/.+\{(.+)\}.+/","$1",$name);
            $results['Date'] = date("F j, Y, g:i a");
            $results["LocalID"] = preg_replace("/ .+/","",$name);
            $results['SGSA_Antigenic_Formula'] = $data['O'].":".$data['H1'].":".$data['H2'];
            $results['Interpolated_Antigenic_Formula'] = $antigenic_formula;
            $results['Subspecies'] =$data['Subspecies'];
            if($serovar_predicted == '')
            {
            	$serovar_predicted = $data['O'].":".$data['H1'].":".$data['H2'];
            }
            $results['Predicted_Serovar'] = $serovar_predicted;
            $results['Status'] = $data['Status'];
            $results_array[$results["File"]] = $results;
        }             
    }
    $out_file = $results_path.$dir_name.'/Results.txt';
    $out_string = "LocalID\tDate\tSubspecies\tSGSA_Antigenic_Formula\tInterpolated_Antigenic_Formula\tPredicted_Serovar\tStatus\n";
    foreach($results_array as $file => $results)
    {
    
        $out_string.=$results["LocalID"]."\t".
        $results['Date']."\t".
        $results['Subspecies']."\t".
        $results['SGSA_Antigenic_Formula']."\t".
        $results['Interpolated_Antigenic_Formula']."\t".
        $results['Predicted_Serovar']."\t".
        $results['Status']."\n";
    }
    #var_dump($out_string);
    file_put_contents($out_file, $out_string);

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=Results.txt');
    header('Content-Length: ' . filesize($out_file));
    readfile($out_file);
}


?>