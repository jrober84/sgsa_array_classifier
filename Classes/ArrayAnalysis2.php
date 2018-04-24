<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ArrayAnalysis
{

    function readTSVdata($file)
    {
        //Load TSV into memory
        $tsv = new ReadTSV($file);
        $header = $tsv->getHeader();
        $hCount = count($header);
        $dataArray = array();
        $row = 1;
        $line = trim($tsv->getNextLine());
        while($line != "")
        {
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
    function ProcessRawData()
    {
         #summarize the data file
        $raw_data = $this->readTSVdata($this->getRawDataFile());
        $data = array();
        foreach($raw_data as $row => $record)
        {
            $probe = $record['Substance'];
            if(preg_match('/Biotin/',$probe))
            {
                $probe = 'Biotin-Marke_2,5uM';
            }
            $value = $record['Signal'];
            if(!array_key_exists($probe, $data))
            {
                $data[$probe] = array('sum'=>0,'count'=>0,'average'=>0);
            }
            $data[$probe]['sum'] = $data[$probe]['sum'] + $value;
            $data[$probe]['count']++;
        }

        #get the average for each probe
        foreach($data as $probe => $info)
        {

            $sum = $info['sum']*100;
            $count = $info['count'];
            if($sum > 0 && $count > 0)
            {
                $this->data[$probe]['average'] = $sum/$count;
            }
            else{
                $this->data[$probe]['average'] = 0;
            }
            if( $this->data[$probe]['average'] < 2)
            {
                $this->data[$probe]['average'] = 0;
            }
            $this->data[$probe]['sum'] = $sum;
            $this->data[$probe]['count'] = $count;
            $this->probes[$probe] = $this->data[$probe]['average'];
        }   
        natsort($this->probes);
  
    }
    function callAntigens()
    {
        $problist = $this->probe;
        $lookUp['H1']['168_b-5'] = 'b';
        $lookUp['H1']['107_e,n,z15 and e,n,x,z15'] = 'e,n,z15/e,n,x,z15';
        $lookUp['H1']['35_d-2'] = 'd';
        $lookUp['H1']['183_g,m,q or g,q-2'] = 'g,m,q or g,q';
        $lookUp['H1']['78_z4,z24'] = 'z4,z24';
        $lookUp['H1']['75_z38'] = 'z38';
        $lookUp['H1']['76_z4,z23'] = 'z4/z23';
        $lookUp['H1']['154_m,t_mtpu_gmt-3'] = 'm,t_mtpu_gmt';
        $lookUp['H1']['74_z29'] = 'z29';
        $lookUp['H1']['161_a-3'] = 'a';
        $lookUp['H1']['157_fgt,fg,gmt'] = 'fgt,fg,gmt';
        $lookUp['H1']['73_z10'] = 'z10';
        $lookUp['H1']['170_y-3'] = 'y';
        $lookUp['H1']['42_f,g,t-2'] = 'f,g,t';
        $lookUp['H1']['40_f,g'] = 'f,g';
        $lookUp['H1']['36_e,h'] = 'e,h';
        $lookUp['H1']['44_f,g,s 0'] = 'f,g,s';
        $lookUp['H1']['56_g,s,t or g,t'] = 'g,s,t/g,t';
        $lookUp['H1']['69_r,[i]'] = 'r,[i]';
        $lookUp['H1']['64_l,z28'] = 'l,z28';
        $lookUp['H1']['59_k'] = 'k';
        $lookUp['H1']['57_g,z51'] = 'g,z51';
        $lookUp['H1']['173_g,m,s-4 (g,m[p]s)'] = 'g,m,s (g,m[p]s)';
        $lookUp['H1']['33_c'] = 'c';
        $lookUp['H1']['71_z'] = 'z';
        $lookUp['H1']['61_l,z13_l,v'] = 'l,zl,v';
        $lookUp['H1']['264_d-4'] = 'd';
        $lookUp['H1']['108_ e,n,x,z15'] = ' e,n,x,z15';
        $lookUp['H1']['114_l,w'] = 'l,w';
        $lookUp['H1']['317_p'] = 'p';
        $lookUp['H1']['283_r,[i]-4'] = 'r,[i]';
        $lookUp['H1']['210_Pullorum-1'] = 'Pullorum';
        $lookUp['H1']['39_f sub 1-2'] = 'f sub 1';
        $lookUp['H1']['252_gt-1 0'] = 'gt 0';
        $lookUp['H1']['250_gmq-gq-5'] = 'gmq-gq';
        $lookUp['H1']['190_g,p_g.p.s'] = 'g,p_g.p.s';
        $lookUp['H1']['188_m,t_g,m,t'] = 'm,t_g,m,t';
        $lookUp['H1']['179_l,z13,z28-2 (FliC)'] = 'l,z13,z28';
        $lookUp['H1']['216_i-2'] = 'i';
        $lookUp['H1']['219_gp-1'] = 'gp';
        $lookUp['H1']['249_gp-3'] = 'gp';
        $lookUp['H1']['169_y-2'] = 'y';
        $lookUp['H1']['67_r'] = 'r';
        $lookUp['H1']['224_O:61-k1'] = 'O:61';
        $lookUp['O']['14_L'] = 'L';
        $lookUp['O']['18_N'] = 'N';
        $lookUp['O']['1_A'] = 'A';
        $lookUp['O']['16_M'] = 'M';
        $lookUp['O']['13_K'] = 'K';
        $lookUp['O']['12_J'] = 'J';
        $lookUp['O']['4_C1'] = 'C1';
        $lookUp['O']['5_C2/C3'] = 'C2/C3';
        $lookUp['O']['9_G'] = 'G';
        $lookUp['O']['10_H'] = 'H';
        $lookUp['O']['19_0'] = 'O';
        $lookUp['O']['21_P'] = 'P';
        $lookUp['O']['141_B-4'] = 'B';
        $lookUp['O']['147_E1-2'] = 'E1';
        $lookUp['O']['237_D1/D2-5 0'] = 'D1/D2';
        $lookUp['O']['140_B-3'] = 'B';
        $lookUp['O']['29_O:61'] = 'O:61';
        $lookUp['O']['23_S'] = 'S';
        $lookUp['O']['24_V'] = 'V';
        $lookUp['O']['26_Y'] = 'Y';
        $lookUp['O']['28_O:58'] = 'O:58';
        $lookUp['H2']['106_e,n,x-4'] = 'e,n,x';
        $lookUp['H2']['107_e,n,z15 and e,n,x,z15'] = 'e,n,z15/e,n,x,z15';
        $lookUp['H2']['86_1,7'] = '1,7';
        $lookUp['H2']['83_1,7_Indiana'] = '1,7';
        $lookUp['H2']['87_1,6'] = '1,6';
        $lookUp['H2']['71_z'] = 'z';
        $lookUp['H2']['108_ e,n,x,z15'] = 'e,n,x/z15';
        $lookUp['H2']['114_l,w'] = 'l,w';
        $lookUp['H2']['88_1,5_1,2,7'] = '1,5/1,2,7';
        $lookUp['H2']['61_l,z13_l,v'] = 'l,z13_l,v';
        $lookUp['H2']['273_1,5,7'] = '1,5,7';
        $lookUp['H2']['139_1,2'] = '1,2';
        $lookUp['H2']['116_z6 and z67 -2'] = 'z6/z67';
        $lookUp['H2']['93_1,5'] = '1,5';
        $lookUp['H2']['94_1,5-2'] = '1,5';
        $lookUp['H2']['96_1,5-4'] = '1,5';
        $majorAntGroup = array(
            'O'=>array('1_A'=>0,'4_C1'=>0,'5_C2/C3'=>0,'9_G'=>0,'10_H'=>0,'12_J'=>0,'13_K'=>0,'14_L'=>0,'16_M'=>0,'18_N'=>0,'19_0'=>0,'21_P'=>0,'23_S'=>0,'24_V'=>0,'26_Y'=>0,'28_O:58'=>0,'29_O:61'=>0,'140_B-3'=>0,'141_B-4'=>0,'147_E1-2'=>0,'237_D1/D2-5 '=>0),
            'H1' => array('33_c'=>0,'35_d-2'=>0,'36_e,h'=>0,'40_f,g'=>0,'42_f,g,t-2'=>0,'44_f,g,s '=>0,'56_g,s,t or g,t'=>0,'57_g,z51'=>0,'59_k'=>0,'64_l,z28'=>0,'69_r,[i]'=>0,'73_z10'=>0,'74_z29'=>0,'75_z38'=>0,'76_z4,z23'=>0,'78_z4,z24'=>0,'154_m,t_mtpu_gmt-3'=>0,'157_fgt,fg,gmt'=>0,'161_a-3'=>0,'168_b-5'=>0,'170_y-3'=>0,'173_g,m,s-4 (g,m[p]s)'=>0,'179_l,z13,z28-2 (FliC)'=>0,'183_g,m,q or g,q-2'=>0,'188_m,t_g,m,t'=>0,'190_g,p_g.p.s'=>0,'216_i-2'=>0,'219_gp-1'=>0,'224_O:61-k1'=>0,'67_r'=>0,'169_y-2'=>0,'249_gp-3'=>0,'250_gmq-gq-5'=>0,'252_gt-1 '=>0,'264_d-4'=>0,'61_l,z13_l,v'=>0,'71_z'=>0,'107_e,n,z15 and e,n,x,z15'=>0,'108_ e,n,x,z15'=>0,'114_l,w'=>0,'39_f sub 1-2'=>0,'210_Pullorum-1'=>0,'283_r,[i]-4'=>0,'317_p'=>0),
                                'H2'=>array('61_l,z13_l,v'=>0,'71_z'=>0,'107_e,n,z15 and e,n,x,z15'=>0,'108_ e,n,x,z15'=>0,'114_l,w'=>0,'83_1,7_Indiana'=>0,'86_1,7'=>0,'87_1,6'=>0,'88_1,5_1,2,7'=>0,'93_1,5'=>0,'94_1,5-2'=>0,'96_1,5-4'=>0,'106_e,n,x-4'=>0,'116_z6 and z67 -2'=>0,'139_1,2'=>0,'223_Kottbus'=>0,'273_1,5,7'=>0));
        #group the probe data into antigen classes
        foreach($problist as $name => $value)
        { 
                foreach($majorAntGroup as $mAG => $probes)
                {
                    if(array_key_exists($probe, $probes))
                    {
                        $majorAntGroup[$mAG][$probe] = $value;
                    }
                }         
        }
        #sort array so stringest signals are first
        foreach($majorAntGroup as $mAG => $probes)
        {
            natsort($probes);
            $majorAntGroup[$mAG] = array_reverse($probes);
        }
        #call Antigesn based on top hit
        $formula = array();
        foreach($majorAntGroup as $mAG => $probes)
        {
            $count = 0;
            foreach($probes as $probe => $value)
            {
                if($count > 0)
                {
                    break;
                }
               $formula[$mAG] = $lookUp[$mAG][$probe];
               $count++;
            }
        }   
        
        
    }
    
    
}