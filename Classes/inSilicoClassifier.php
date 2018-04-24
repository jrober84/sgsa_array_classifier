<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include __DIR__."/probes.php";
include __DIR__."/readProfile.php";
ini_set("memory_limit","8000M");
set_time_limit ( 0);

$profile_file = '/Users/jrobertson/NetBeansProjects/SGSA/DataFiles/SGSA_V2_Data.txt';
$probe_config = '/Users/jrobertson/NetBeansProjects/SGSA/DataFiles/probe.config';

$file = '/Users/jrobertson/Desktop/__IntegrityTesting/intetgrity_panel.txt';

$raw = new readRawData($file);
//var_dump($raw->data);
$obj = new buildProfile($profile_file,$probe_config);
$sero = new serovarLookUp('/Users/jrobertson/NetBeansProjects/SGSA/DataFiles/serovars.txt');

foreach($raw->data as $data)
{
    $file = $data['file'];
    $result = $obj->classify($data, $obj->profiles,$obj->probe_obj );
    $serovar = $sero->getSerovar($result['O'],$result['H1'],$result['H2']);
    $out_string = '';
    $predSubspecies = $result['Subspecies'];


    foreach($serovar as $name => $info)
    {

    	$formula= $info['formula'];
    	$subspecies = $info['subspecies'];
        if($predSubspecies != 'N/A' and count($serovar) > 1 and $subspecies != $predSubspecies )
    	{
    		continue;
    	}
        $out_string.= $name.' |';
        if($name == 'Enteritidis' && array_key_exists('Enteriditis',$result) && $result['Enteriditis'] == TRUE)
        {
            $antigenic_formula = $formula;
            $out_string = 'Enteritidis   ';
            break;       
        }
        if($name == 'Paratyphi C' || $name == 'Choleraesuis')
        {
                if($result['ParatyphiC'] == TRUE)
                {
                    $out_string =  'Paratyphi C   ';
                }
                elseif ( array_key_exists('VI',$result) and $result['VI'] == TRUE)
                {
                        $out_string = 'Paratyphi C   ';

                }
                else{
                        $out_string =  'Choleraesuis   ';               	

                }

                break;

        }
        if(($name == 'Oranienburg' || $name == 'Othmarschen') && $result['Subspecies'] == 'I')
        {
                if($result['Oranienburg'] == TRUE)
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
        
        
    }
    if(strlen($out_string) > 1)
    {
        $out_string = substr($out_string, 0,strlen($out_string)-1);
    }
    else{
        $out_string = $result['O'].":".$result['H1'].":".$result['H2'];
    }

    echo $file."\t".$result['Subspecies']."\t".$result['O']."\t".$result['H1']."\t".$result['H2']."\t".$out_string."\t".$result['Status']."\n";
}



class readRawData{
    var $data = array();   
    function __construct($file) {
        $this->data = $this->readTSVdata($file);
        
    }
    function getProbeValue($probe)
    {
        return $this->data[$probe];
    }
    
    function getProbeNames()
    {
        return array_keys($data);
    }
    

   function readTSVdata($file)
    {
        //Load TSV into memory
        $tsv = new ReadTSV($file);
        $header = $tsv->getHeader();
        $hCount = count($header);
        $dataArray = array();
        $row = 1;
        $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",trim($tsv->getNextLine())));
        while($line != "" )
        {
            $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",$line));
            if(!preg_match("/\w/",$line))
            {
                continue;
            }
            $fields = explode("\t",$line);
            $dataArray[$row] = array();
            for($i=0; $i< $hCount; $i++)
            {
                if(!preg_match("/\w/",$header[$i]))
                {
                    continue;
                }
                if(!array_key_exists($i, $fields))
                {
                    $dataArray[$row][$header[$i]] = null;
                }
                else{
                    $dataArray[$row][$header[$i]] = $fields[$i];
                }

            }
            $line = trim($tsv->getNextLine());
            $row++;
        }

        unset($tsv);
        return $dataArray;
    }
}



class serovarLookUp
{
    var $file;
    var $lookup = array();
    
    function __construct($file) {
        $data = $this->readTSVdata($file);
        foreach($data as $row)
        {
            $o = $row['Serogroup'];
            $h1 = $row['H Phase 1'];
            $h2 = $row['H Phase 2'];
            $name = $row['Serovar'];
            $formula = $row['Antigenic Formula'];
            $subspecies = $row['Subspecies'];
            $this->addSerovar($o,$h1,$h2,$name,$formula,$subspecies);
        }
    }
    
    function getSerovar($o,$h1,$h2)
    {
        if(array_key_exists($o, $this->lookup)
                && array_key_exists($h1, $this->lookup[$o])
                && array_key_exists($h2, $this->lookup[$o][$h1]))
        {
            return $this->lookup[$o][$h1][$h2];
        } 
        return array();
    }
    
    
    
    function addSerovar($o,$h1,$h2,$name,$formula,$subspecies)
    {
        if(!array_key_exists($o, $this->lookup))
        {
            $this->lookup[$o] = array();
        }
        if(!array_key_exists($h1, $this->lookup[$o]))
        {
            $this->lookup[$o][$h1] = array();
        }
        if(!array_key_exists($h2, $this->lookup[$o][$h1]))
        {
            $this->lookup[$o][$h1][$h2] = array();
        }
        $this->lookup[$o][$h1][$h2][$name] = array('formula'=>$formula,'subspecies'=>$subspecies);
    }
    
    
    function readTSVdata($file)
    {
        //Load TSV into memory
        $tsv = new ReadTSV($file);
        $header = $tsv->getHeader();
        $hCount = count($header);
        $dataArray = array();
        $row = 1;
        $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",trim($tsv->getNextLine())));
        while($line != "" )
        {
            $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",$line));
            if(!preg_match("/\w/",$line))
            {
                continue;
            }
            $fields = explode("\t",$line);
            $dataArray[$row] = array();
            for($i=0; $i< $hCount; $i++)
            {
                if(!preg_match("/\w/",$header[$i]))
                {
                    continue;
                }
                if(!array_key_exists($i, $fields))
                {
                    $dataArray[$row][$header[$i]] = null;
                }
                else{
                    $dataArray[$row][$header[$i]] = $fields[$i];
                }

            }
            $line = trim($tsv->getNextLine());
            $row++;
        }

        unset($tsv);
        return $dataArray;
    }    
}






class buildProfile
{
    var $profiles = array();
    var $probe_obj;
    
    function __construct($profile_file,$probe_config) {
        $this->profiles['Subspecies'] = array();
        $this->profiles['Serovar'] = array();
        $this->profiles['O'] = array();
        $this->profiles['H1'] = array();
        $this->profiles['H2'] = array();
        $profile_obj = new readProfile($profile_file);
        $this->probe_obj = new probes($probe_config);
        $this->build($profile_obj,$this->probe_obj);
        $this->filter($this->probe_obj);
        
    }
    
    function build($profile_obj,$probe_obj)
    {
        $sampleIDS = $profile_obj->getIDS();
        $probes = $probe_obj->getThresholds();
        foreach($sampleIDS as $id)
        {
            $sampleInfo = $profile_obj->getSampleData($id);
            foreach($probes as $probe => $thresh)
            {
                if(floatval($sampleInfo[$probe]) < $thresh )
                {
                    $sampleInfo[$probe] = 0;
                }
                //echo "$probe\t$thresh\t$sampleInfo[$probe]\n";
                //die;
            }

            unset($sampleInfo['File']);
            $subspecies = $sampleInfo['Subspecies'];
            
            unset($sampleInfo['Subspecies']);           
            $oAntigen = $sampleInfo['O'];
            unset($sampleInfo['O']);           
            $h1Antigen = $sampleInfo['H1'];
            unset($sampleInfo['H1']);           
            $h2Antigen = $sampleInfo['H2'];
            unset($sampleInfo['H2']);            
            $serovar = $sampleInfo['Serovar'];
            unset($sampleInfo['Serovar']);
            //$this->addProfileElement('Records',$serovar,$sampleInfo,$id);
            $this->addProfileElement('Serovar',$serovar,$sampleInfo,$id); 
            if($subspecies !=  'N/A')
            {
                $this->addProfileElement('Subspecies',$subspecies,$sampleInfo,$id);
            }
            $this->addProfileElement('H2',$h2Antigen,$sampleInfo,$id);
            $this->addProfileElement('H1',$h1Antigen,$sampleInfo,$id);
            $this->addProfileElement('O',$oAntigen,$sampleInfo,$id);
            
        }
    }
    
    function filter($probe_obj)
    {
        foreach($probe_obj->probe_assignments as $probe => $categories)
        {
            foreach($this->profiles as $division => $samples)
            {
                if($division == 'Records' || array_key_exists($division, $categories) )
                {
                    continue;
                }
                foreach($samples as $id => $records)
                {
                    
                    foreach($records as $rID => $record)
                    {
                        unset($record[$probe]);
                        $this->profiles[$division][$id][$rID] = $record;
                    }
                }
            }           
        }

    }
    function processRaw($query_profile,$probes)
    {
         foreach($query_profile as $probe => $value)
        {
            if(!array_key_exists($probe, $probes))
            {
                unset($query_profile[$probe]);
                continue;
            }
            if($probes[$probe] > $value && $probe != '120_invA' )
            {
                $query_profile[$probe] = 0;
            }
        }
        #var_dump($query_profile);
      
        return $query_profile;
    }
    
    
    function classify($query_profile,$target_profiles,$probe_obj)
    {
        $probes = $probe_obj->getThresholds();
        $query_profile = $this->processRaw($query_profile, $probes);

        //var_dump($query_profile);
        //Get ranked probes by antigen
        $ranked_probes = array('O'=>array(),'H1'=>array(),'H2'=>array(),'Serovar'=>array(),'Subspecies'=>array());
        $thresholds = $probe_obj->getThresholds();
        //var_dump($thresholds);
        foreach($probe_obj->probe_assignments as $probe => $categories)
        {
            if(!array_key_exists($probe, $query_profile))
            {
                continue;
            }
            $value = $query_profile[$probe];
            //echo "$probe\t$value\t$thresholds[$probe]\n";
            //var_dump($categories);
            if($value > $thresholds[$probe])
            {
                //echo "PASS\n";
                foreach($categories as $category => $dump)
                {
                    $ranked_probes[$category][$probe] = $value;

                }
            }
        }

        

        if(count($ranked_probes['H2']) == 1 && array_key_exists('88_1,5_1,2,7', $ranked_probes['H2']))
        {
            unset($ranked_probes['H2']['88_1,5_1,2,7']);
            $query_profile['88_1,5_1,2,7'] = 0;
        }
        
        if(count($ranked_probes['H1']) == 1 && array_key_exists('183_g,m,q or g,q-2', $ranked_probes['H1']))
        {
            unset($ranked_probes['H1']['183_g,m,q or g,q-2']);
            $query_profile['183_g,m,q or g,q-2'] = 0;            
        }
        
        
        foreach($ranked_probes as $factor => $data)
        {
            asort($data,SORT_NUMERIC);          
            end($data);
            $ranked_probes[$factor] = $data;
        }
		
		//var_dump($ranked_probes['O']);

        $top_O = key($ranked_probes['O']);
        
        //var_dump($top_O);
        $top_h1 = key($ranked_probes['H1']);
        //var_dump($ranked_probes['H1']);
        $top_h2 = key($ranked_probes['H2']);
        //var_dump($ranked_probes['H2']);
        $oMatch = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['O'],5));
        //var_dump($oMatch);
        $h1Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H1'],5));
        //var_dump($h1Match);
        $h2Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H2'],5));
        //var_dump($h2Match);
        
        if(substr($h1Match['antigen'],0,1) != 'f' 
                && substr($h1Match['antigen'],0,1) != 'g'
                && array_key_exists('183_g,m,q or g,q-2', $ranked_probes['H1']))
        {
            unset($ranked_probes['H1']['183_g,m,q or g,q-2']);
            $query_profile['183_g,m,q or g,q-2'] = 0;  
            
        }
        //O Antigen
        if(count($ranked_probes['O']) > 0)
        {
            
            if(count($ranked_probes['O']) == 1)
            {
                $probe = key($ranked_probes['O']);   
                if(count($probe_obj->probes['O'][$probe]) == 1)
                {
                    $oMatch['antigen'] = key($probe_obj->probes['O'][$probe]);
                }
            }
            else{
                if(count($probe_obj->probes['O'][$top_O]) == 1)
                {
                    $oMatch['antigen'] = key($probe_obj->probes['O'][$top_O]);
                }
                else{
                    $oMatch = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['O'],5));
                }
                 
            }           
        }
		//var_dump($oMatch);
        //H1 Antigen
        if(count($ranked_probes['H1']) > 0)
        {
            
            if(count($ranked_probes['H1']) == 1)
            {
                $probe = key($ranked_probes['H1']);   
                if(count($probe_obj->probes['H1'][$probe]) == 1)
                {
                    $h1Match['antigen'] = key($probe_obj->probes['H1'][$probe]);
                }
                else{
                    $h1Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H1'],5));
                }
            }
            else{
                if(count($probe_obj->probes['H1'][$top_h1]) == 1)
                {
                    $h1Match['antigen'] = key($probe_obj->probes['H1'][$top_h1]);
                }
                else{
                    $h1Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H1'],5));
                }
                 
            }          
        }
       //var_dump($h1Match);

        //H2 Antigen
        if(count($ranked_probes['H2']) > 0)
        {
            
            if(count($ranked_probes['H2']) == 1)
            {
                $probe = key($ranked_probes['H2']);   
                if(count($probe_obj->probes['H2'][$probe]) == 1)
                {
                    $h2Match['antigen'] = key($probe_obj->probes['H2'][$probe]);
                }
            }
            else{
                if(count($probe_obj->probes['H2'][$top_h2]) == 1)
                {
                    $h2Match['antigen'] = key($probe_obj->probes['H2'][$top_h2]);
                }
                else{
                    $h2Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H2'],5));
                }
                 
            }          
        }
        //var_dump($h2Match);
        
       
		if($oMatch['antigen'] == 'G' && ($h1Match['antigen'] == 'z'))
        {
        	$temp_1 = $query_profile['71_z'];
        	$query_profile['71_z'] = 0;
        	$h2Match = $this->getNearestNeighbor($query_profile,$target_profiles['H2']);
        	$query_profile['71_z'] = $temp_1;
        }
        if($oMatch['antigen'] == 'E' && ($h1Match['antigen'] == 'g,[s],t'))
        {
        	$h2Match['antigen'] = '-';
        }
        //echo substr($h2Match['antigen'],0,1) ."\n";
        
        if(($h1Match['antigen'] == 'l,v' || $h1Match['antigen'] == 'l,w') 
                && $h2Match['antigen'] != 'e,n,z15' && $h2Match['antigen'] != 'e,n,x' && substr($h2Match['antigen'],0,1) != '1')
        {

            $temp_1 = $query_profile['114_l,w'];
            $temp_2 = $query_profile['61_l,z13_l,v'];
            $query_profile['114_l,w'] = 0;
            $query_profile['61_l,z13_l,v'] = 0;
            if($h2Match['antigen'] != '1,5' && $h2Match['antigen'] != '1,2')
            {
                $h2Match = $this->getNearestNeighbor($query_profile,$target_profiles['H2']);
            }

            if(count($ranked_probes['H1']) > 2 
                    && $h2Match['antigen'] != '-' 
                    && $h2Match['antigen'] != 'l,v' 
                    && $h2Match['antigen'] != 'l,w'
                    && $h2Match['antigen'] != '1,5'
                    && $h2Match['antigen'] != '1,2')
            {
                $temp_h1Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H1'],5));
                if($temp_h1Match['antigen'] != '-' &&
                        $temp_h1Match['antigen'] != 'l,v' &&
                        $temp_h1Match['antigen'] != 'l,w' &&
                        $temp_h1Match['antigen'] != 'e,n,z15')
                {
                    $h2Match = $h1Match;
                    $h1Match = $temp_h1Match;   
                }
            }
            $query_profile['114_l,w'] = $temp_1;
            $query_profile['61_l,z13_l,v'] = $temp_2;
            
            
        }
        //var_dump( $h1Match);
        //var_dump($h2Match);
         //die;

        
        if($h1Match['antigen'] == $h2Match['antigen'])
        {
            $probe_h1 = key($ranked_probes['H1']);  
            $probe_h2 = key($ranked_probes['H2']);
            if($probe_h1 == $probe_h2)
            {
                if(count($ranked_probes['H1']) > count($ranked_probes['H2']))
                {
                    $temp_1 = $query_profile[$probe_h1];
                    $query_profile[$probe_h1] = 0;
                    $h1Match = $this->getNearestNeighbor($query_profile,$target_profiles['H1']);
                    $query_profile[$probe_h1] = $temp_1;
                }
                if(count($ranked_probes['H1']) <= count($ranked_probes['H2']))
                {
                    $temp_1 = $query_profile[$probe_h2];
                    $query_profile[$probe_h2] = 0;
                    $h2Match = $this->getNearestNeighbor($query_profile,$target_profiles['H2']);
                    $query_profile[$probe_h2] = $temp_1;
                }                
            }
            
        }
        
        if($h2Match['antigen'] == 'l,w' 
                && array_key_exists('61_l,z13_l,v', $ranked_probes['H2'])
                && array_key_exists('114_l,w', $ranked_probes['H2']))
        {
            if($ranked_probes['H2']['61_l,z13_l,v'] > $ranked_probes['H2']['114_l,w'] )
            {
                $h2Match['antigen'] = 'l,v'; 
            }
        }

        if(substr($h1Match['antigen'],0,1) == 'f' && !array_key_exists('39_f sub 1-2', $ranked_probes['H1']))
        {
            $array = array('39_f sub 1-2'=>$query_profile['39_f sub 1-2'],
                            '40_f,g'=>$query_profile['40_f,g'],
                            '42_f,g,t-2'=>$query_profile['42_f,g,t-2'],
                            '44_f,g,s'=>$query_profile['44_f,g,s']);
            foreach ($array as $key => $value)
            {
                $query_profile[$key] = 0;
            }
            $h1Match = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['H1'],5));
            foreach ($array as $key => $value)
            {
                $query_profile[$key] = $value;
            }
        }
        
        
        
        $present = FALSE;
        
        
        
        if($h1Match['antigen'] == 'g,m,s' && array_key_exists('39_f sub 1-2', $ranked_probes['H1']))
        {
            $h1Match['antigen'] = 'f,g,s';
        }
        $subspecies = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['Subspecies'],3));
        #var_dump($subspecies);
        $serovar = $this->CategoryVote($this->getKNN($query_profile,$target_profiles['Serovar'],3));
        /*var_dump($subspecies);
        var_dump($serovar);
        var_dump($ranked_probes);*/
        //var_dump($oMatch);
        if($oMatch['antigen'] == 'A')
        {
        	
            $valid = array('a'=>'','g,m'=>'','g,p'=>'','l,v'=>'');
            if(!array_key_exists($h1Match['antigen'],$valid ))
            {
                $oMatch['antigen'] = 'D';
            }
            $valid = array('-'=>'','1,5'=>'');
            if(!array_key_exists($h2Match['antigen'],$valid ))
            {
                $oMatch['antigen'] = 'D';
            }           
            if(array_key_exists('137_RHS-Gallinarum-2', $ranked_probes['Serovar']))
            {
                $oMatch['antigen'] = 'D';
            }
            
            if($h1Match['antigen'] == 'g,p' && $h2Match['antigen'] == '-' && array_key_exists('210_Pullorum-1', $ranked_probes['Serovar']))
            {
                $oMatch['antigen'] = 'D';
            }
            //Enteriditis/Nitra Switch
            if(array_key_exists('Ent-rfbE', $ranked_probes['Serovar']) && $h1Match['antigen'] == 'g,m')
            {
                $oMatch['antigen'] = 'D';
            }
            
            if($h1Match['antigen'] == 'g,p' && array_key_exists('210_Pullorum-1', $ranked_probes['Serovar']) )
            {
                $oMatch['antigen'] = 'D';
            }
            
            if(!array_key_exists('1_A', $ranked_probes['O']))
            {
                $oMatch['antigen'] = 'D';
            }
            if($h1Match['antigen'] == 'a' && $h1Match['antigen'] == '1,5' && array_key_exists('1_A', $ranked_probes['O']))
            {
                $oMatch['antigen'] = 'A';
            }            
        }
        if($oMatch['antigen'] == 'D')
        {
            if($h1Match['antigen'] == 'a' && $h2Match['antigen'] == '1,5' && array_key_exists('1_A', $ranked_probes['O']))
            {
                $oMatch['antigen'] = 'A';
            }
            //Enteriditis/Nitra Switch
            if(!array_key_exists('Ent-rfbE', $ranked_probes['Serovar']) && $h1Match['antigen'] == 'g,m')
            {
                $oMatch['antigen'] = 'A';
            }
            //Gallinarum and Pullorum Overide
            if(array_key_exists('210_Pullorum-1',$ranked_probes['Serovar']) && !array_key_exists('213_Enteritidis-1',$ranked_probes['Serovar']) && array_key_exists('sspIIIa_4',$ranked_probes['Serovar']) )
            {
                $h1Match['antigen'] = '-';
            }

            
            if(array_key_exists('137_RHS-Gallinarum-2', $ranked_probes['Serovar']) 
                    &&  array_key_exists('sspI', $ranked_probes['Subspecies']) && ($h1Match['antigen'] == 'g,m' ))
            {
                $h1Match['antigen'] = '-';
            }  
            if(array_key_exists('Gallinarum_1',$ranked_probes['Serovar'] ) && ($h1Match['antigen'] == 'g,m' || $h1Match['antigen'] == 'g,q' || $h1Match['antigen'] == 'g,p'))
            {
                $h1Match['antigen'] = '-';
            }
            if(!array_key_exists('Dublin-nupC',$ranked_probes['Serovar']) && $h1Match['antigen'] == 'g,p')
            {
                $oMatch['antigen'] = 'A';
            }
            //Hillingdon override
            $ent_probes = array('Enteritidis-2','Enteritidis-3','Enteritidis-4','Enteritidis-5','Enteritidis-6','Enteritidis-7');
            
            if(!array_key_exists('sspIIIa_4',$ranked_probes['Serovar']))
            {
            	$present = TRUE;
            }
            foreach($ent_probes as $e)
            {
                if(array_key_exists($e, $ranked_probes['Serovar']))
                {
                    $present = TRUE;
                }
            }
            
          
        }

        
        if($h2Match['antigen'] == '1,5' &&
                count($ranked_probes['H2']) == 2 &&
                array_key_exists('139_1,2', $ranked_probes['H2']) &&
                array_key_exists('82_1,2_1,5_1,2,7', $ranked_probes['H2']))
        {
            $h2Match['antigen'] = '1,2';
        }

        //Kottbus Overide
        if(    $oMatch['antigen'] == 'C2-C3'
                && $h1Match['antigen'] == 'e,h'
                && $h2Match['antigen'] == '1,2' 
                && array_key_exists('223_Kottbus',$ranked_probes['Serovar']))
        {
            //$h2Match['antigen'] = '1,5';
        }
        
        if($h1Match['antigen'] == 'l,v' || $h1Match['antigen'] == 'l,w')
        {
            unset($ranked_probes['H2']['114_l,w']);
            unset($ranked_probes['H2']['61_l,z13_l,v']);
            
        }
 
        if(substr($h1Match['antigen'],0,1) == 'g' && $h2Match['antigen'] == '-')
        {
            if(array_key_exists('Blegdam_Moscow_1', $ranked_probes) )
            {
                $h1Match['antigen'] = 'g,m,q';
            }    
        }


        if(($query_profile['120_invA'] < $thresholds['120_invA']) )
        {
            
            $results = array('Serovar'=>'N/A','Subspecies'=>'N/A','O'=>'-','H1'=>'-','H2'=>'-','Status'=>'FAIL','Formula'=>'-:-:-');
        }
        else{
            
            $results = array('Serovar'=>'N/A','Subspecies'=>'N/A','O'=>'-','H1'=>'-','H2'=>'-','Status'=>'PASS','Formula'=>'-:-:-');
        }
        
        
        foreach($ranked_probes as $category => $probes)
        {
            #echo "$category\n";
            #var_dump($probes);
            if(count($probes) == 0)
            {
                continue;
            }
            foreach($probes as $probe => $value)
            {
                if($category == 'O')
                {
                    if(array_key_exists($oMatch['antigen'], $probe_obj->probes['O'][$probe]))
                    {
                        $results['O'] = $oMatch['antigen'];
                    }                    
                }
                if($category == 'H1')
                {
                    
                    if(array_key_exists($h1Match['antigen'], $probe_obj->probes['H1'][$probe]))
                    {
                         $results['H1'] = $h1Match['antigen'];
                    }      
                }
                if($category == 'H2')
                {
                  
                    if(array_key_exists($h2Match['antigen'], $probe_obj->probes['H2'][$probe]))
                    {
                         $results['H2'] = $h2Match['antigen'];
                    }      
                }
            }
            if($present == TRUE)
            {
                $results['Enteriditis'] = TRUE;
            }

            if($category == 'Subspecies')
            {
                #var_dump($subspecies['antigen']);
                $results['Subspecies'] = $subspecies['antigen'];
            }           
        }
        //Overide inva threshold for known issues
        //var_dump($query_profile);
        if( ($results['O'] == 'G' || $results['O'] == 'U' || $results['O'] == 'H')&& $query_profile['120_invA'] > 0.4)
        {
            $results['Status'] = 'PASS';
        }
        //Berta override
        if($results['O'] == 'D' && $results['H1'] == 'f,g,m,t' &&  $results['H2'] == '-')
        {
            $results['H1'] = '[f],g,[t]';
        }
        //Bovismorbificans override
        if($results['O'] == 'C2-C3' && $results['H1'] == 'r' &&  $results['H2'] == '1,5')
        {
            $results['H1'] = 'r,[i]';
        }
        
        //Livingstone Overide
        if($results['O'] == 'C1' && $results['H1'] == 'd' &&  $results['H2'] == 'l,v')
        {
            $results['H1'] = 'l,w';
        }        
        //Oranienburg Othmarschen override
        $oranienburg_ssp_probes = array('sspIIIb_2','sspIIIb_3','sspIV_2','sspIV_5','sspVI_1','sspVI_2','sspV_1');
        $results['Oranienburg'] = TRUE;
        foreach($oranienburg_ssp_probes as $probe)
        {
        	if(array_key_exists($probe,$ranked_probes['Serovar']))
        	{
        		$results['Oranienburg'] = TRUE;
        		break;	
        	}
        }
        //Gallinarum/Pullorum
        if($results['O'] == 'D' && $results['H1'] == 'g,m' &&  $results['H2'] == '-')
        {
        
        	if(array_key_exists('sspI', $ranked_probes['Serovar']) && array_key_exists('137_RHS-Gallinarum-2', $ranked_probes['Serovar']) && $ranked_probes['Serovar']['137_RHS-Gallinarum-2'] > 0.3)
        	{
        		$results['H1'] = '-';
        	}
            if(array_key_exists('137_RHS-Gallinarum-2', $ranked_probes['Serovar']) && $ranked_probes['Serovar']['137_RHS-Gallinarum-2'] > 0.6)
            {
            	$results['H1'] = '-';
            
            }
			if(array_key_exists('137_RHS-Gallinarum-2',$ranked_probes['Serovar']) 
			&& array_key_exists('210_Pullorum-1',$ranked_probes['Serovar']) 
			&& !array_key_exists('213_Enteritidis-1',$ranked_probes['Serovar']))
			{
				$results['H1'] = '-';
			}
			//Dublin/Rostock
			elseif(array_key_exists('gpu-gp',$ranked_probes['Serovar']))
			{
				if(array_key_exists('gp-3',$ranked_probes['Serovar']))
				{
					$results['H1'] = 'g,p';	
				}
				else{
					$results['H1'] = 'g,p,u';	
				}
			}
			elseif(array_key_exists('Blegdam_Moscow_1',$ranked_probes['Serovar']) 
			&& !array_key_exists('Enteritidis(vsBlegMosc)_1',$ranked_probes['Serovar'])){
				
				if($present == TRUE)
				{
					$results['H1'] = 'g,m,q';
				}
				else{
					$results['H1'] = 'g,q';
				}					
			}

        }        
        $is_vi_factor_present = FALSE;
        if(array_key_exists('122_Vi',$ranked_probes['Serovar']))
        {
        	$is_vi_factor_present = TRUE;
        }
        $results['VI'] = $is_vi_factor_present;
        
        $paratyphi_c_probe_present = FALSE;
        
        if(array_key_exists('ParatyphiC_1',$ranked_probes['Serovar']))
        {
        	$paratyphi_c_probe_present = TRUE;
        }
        $results['ParatyphiC'] = $is_vi_factor_present;
                
        return $results;
        
    }
     function getKNN($query_profile,$target_profiles,$k)
    {
        $antigens = array();
        $matches = array();
        foreach($target_profiles as $antigen => $profiles)
        {
           foreach($profiles as $id => $profile)
           {
                $dist = $this->getEuclideanDist($query_profile,$profile);
                $matches[$id] = $dist;
                $antigens[$id] = $antigen;
           }
        }
        asort($matches, SORT_NUMERIC);
       
        $knn = array();
        $count = 1;
        foreach($matches as $id => $dist)
        {
            $knn[$id] = array('antigen'=> $antigens[$id],'dist'=>$dist);
            if($count == $k)
            {
                break;
            }
            $count++;
            
        }
        //var_dump($knn);
        return $knn;
    }
    
    function CategoryVote($matches)
    {
        $categories = array();
        foreach($matches as $id => $data)
        {
            $category = $data['antigen'];
            $dist = 1000;
            if($data['dist'] > 0)
            {
                $dist = 1/($data['dist']);
            }
            
            if(!array_key_exists($category, $categories))
            {
                $categories[$category] = 0;
            }
            $categories[$category] = $categories[$category] + $dist;
        }
        asort($categories,SORT_NUMERIC);
        end($categories);
        $key = key($categories);
        return (array('dist'=>$categories[$key],'antigen'=>$key));
        
    }
    
    
    function getNearestNeighbor($query_profile,$target_profiles)
    {
        $match_dist = -1;
        $match_id = '';
        $match_antigen = '';
        $matches = array();
        foreach($target_profiles as $antigen => $profiles)
        {
           foreach($profiles as $id => $profile)
           {
                $dist = $this->getEuclideanDist($query_profile,$profile);
                if($match_dist == -1 || $dist < $match_dist)
                {
                    $match_id = $id;
                    $match_dist = $dist;
                    $match_antigen = $antigen;
                }
                $matches[] = array('antigen'=>$match_antigen ,'id'=>$match_id,'dist'=>$match_dist);
                //echo "$id\t$antigen\t$dist\n";
           }
        }
        //echo "------\n";
       // var_dump($matches);
        return array('antigen'=>$match_antigen ,'id'=>$match_id,'dist'=>$match_dist);
    }
    
    function getEuclideanDist($array1,$array2)
    {
        $sum = 0;
        $match = 0;
        foreach($array1 as $key => $value)
        {
            if(!array_key_exists($key, $array2))
            {
                continue;
            }
            if(floatval($value) > 0 && floatval($array2[$key]) > 0)
            {
                $match++;
            }
            //echo "$key\t$sum\t$value\t$array2[$key]\t".pow(($value -$array2[$key]),2)."\n";;

            $sum = $sum + round(pow((round(floatval($value),2) - round(floatval($array2[$key]),2)),2),6);
        }
        if($match == 0)
        {
            $sum = 1000;
        }
        //echo sqrt($sum)."\n";;
        //die;
        return sqrt($sum);
    }
    

    function addProfileElement($division,$name,$profile,$id)
    {
        if(!array_key_exists($division, $this->profiles))
        {
            $this->profiles[$division] = array();
        }
        if(!array_key_exists($name, $this->profiles[$division]))
        {
            $this->profiles[$division][$name] = array();
        }
        $this->profiles[$division][$name][$id] = $profile;
        
    }
    
}