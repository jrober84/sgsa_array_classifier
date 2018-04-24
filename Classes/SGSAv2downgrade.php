<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include __DIR__."/ReadTsv.php";
error_reporting(E_ALL);
set_time_limit (3000);
ini_set('display_errors', 1);
mb_internal_encoding("UTF-8");
/*
$in = '/Users/jrobertson/Desktop/Sent from Alere validation panel SGSAv2/01-A.274823110495_1.chip.txt';
$out = '/Users/jrobertson/Desktop/out.txt';

$obj = new SGSAv2downgrade();
$obj->process($in);
var_dump($obj);
//$obj->write($out);

*/
class SGSAv2downgrade
{

var $header = array('Spot ID','Substance','Confidence','Signal','Valid','Background','Mean');    
var $hCount = 7;  
var $out_string = '';
var $data = array();
var $probes = array('1_A'=>'','4_C1'=>'','5_C2-C3'=>'','9_G'=>'','10_H'=>'','12_J'=>'','13_K'=>'','14_L'=>'','16_M'=>'','18_N'=>'','19_0'=>'','21_P'=>'','23_S'=>'','24_V'=>'','26_Y'=>'','28_O-58'=>'','29_O-61'=>'','33_c'=>'','35_d-2'=>'','36_e,h'=>'','39_f sub 1-2'=>'','40_f,g'=>'','42_f,g,t-2'=>'','44_f,g,s'=>'','45_f,g,m,t'=>'','46_f,g,m,t-2'=>'','56_g,s,t or g,t'=>'','57_g,z51'=>'','59_k'=>'','61_l,z13_l,v'=>'','64_l,z28'=>'','67_r'=>'','69_r,[i]'=>'','71_z'=>'','73_z10'=>'','74_z29'=>'','75_z38'=>'','76_z4,z23'=>'','78_z4,z24'=>'','82_1,2_1,5_1,2,7'=>'','83_1,7_Indiana'=>'','86_1,7'=>'','87_1,6'=>'','88_1,5_1,2,7'=>'','93_1,5'=>'','94_1,5-2'=>'','96_1,5-4'=>'','106_e,n,x-4'=>'','107_e,n,z15 and e,n,x,z15'=>'','108_ e,n,x,z15'=>'','114_l,w'=>'','116_z6 and z67 -2'=>'','120_invA'=>'','122_Vi'=>'','137_RHS-Gallinarum-2'=>'','139_1,2'=>'','140_B-3'=>'','141_B-4'=>'','142_D1/D2-3'=>'','147_E1-2'=>'','152_m,t_mtpu_gmt'=>'','153_m,t_mtpu_gmt-2'=>'','154_m,t_mtpu_gmt-3'=>'','157_fgt,fg,gmt'=>'','161_a-3'=>'','168_b-5'=>'','169_y-2'=>'','170_y-3'=>'','173_g,m,s-4 (g,m[p]s)'=>'','179_l,z13,z28-2 (FliC)'=>'','180_l,z13,z28-2 (FljB)'=>'','183_g,m,q or g,q-2'=>'','188_m,t_g,m,t'=>'','190_g,p_g.p.s'=>'','210_Pullorum-1'=>'','213_Enteritidis-1'=>'','216_i-2'=>'','219_gp-1'=>'','223_Kottbus'=>'','224_O:61-k1'=>'','D1/D2-5'=>'','gpu-2'=>'','gpu-gp-fg-gst'=>'','fgt-gmt-mptu-mt'=>'','gp-2'=>'','gp-3'=>'','gmq-gq-5'=>'','gt-1'=>'','d-4'=>'','1,2,7-5'=>'','1,5,7'=>'','r,[i]-4'=>'','Ent-shdA'=>'','Dublin-nupC'=>'','Ent-rfbE'=>'','pepT'=>'','pepT-3'=>'','p'=>'','0,1M NaPP Standard pH 9'=>'','Biotin-Marke_2,5uM'=>'');

public function encodeToUtf8($string) {
     return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
}

function write($file)
{
    $fh = fopen($file,'w');
    //var_dump($this->data);
    fwrite($fh,implode("\t",$this->header)."\n");
    foreach($this->data as $spotID => $elements)
    {
//        /var_dump($elements);
        $line = iconv('UTF-8','UTF-16LE',$elements['Spot ID']."\t".$elements['Substance']."\t".$elements['Confidence']."\t".$elements['Signal']."\t".$elements['Valid']."\t".$elements['Background']."\t".$elements['Mean']."\n");
        fwrite($fh,$line);
    }
    fclose($fh);
}


function process($file)
{
    $raw_data = $this->readTSVdata($file);
    $rawCount = count($raw_data);
    $count = 0;
    #it is version v
    if($rawCount > 301)
    {
        $data = $this->template();
    }
    else{ #it is version 1
        $data = $this->template1();
    }
    //var_dump($data);
    //die;
    //var_dump($raw_data);
    //die;
    foreach($raw_data as $row => $record)
    {
        $probe = $this->encodeToUtf8(preg_replace('/"/','',$record['Substance']));
        $spotID = $this->encodeToUtf8(preg_replace('/"/','',$record['Spot ID']));
        if(!array_key_exists( $row , $data))
        {
           // echo "$probe\t $spotID\n";
            continue;
        }    

        for($i=1; $i<$this->hCount; $i++)
        {
            $value = 0;
            if(array_key_exists($this->header[$i], $record))
            {
                $value = $record[$this->header[$i]];
            }
            else{
                echo $this->header[$i]."\n";
            }
            $data[ $row ][$this->header[$i]] = $this->encodeToUtf8($value);
        }
        $count++;
    }
    $this->data = $data;
    
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
        $line = preg_replace("/—|–|-/","-",preg_replace("/\"/","",trim($tsv->getNextLine())));
        $row++;
    }
    unset($tsv);
    return $dataArray;
}
function template()
{
$template[1] = array('Substance'=>'1_A','Spot ID'=>5,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[2] = array('Substance'=>'4_C1','Spot ID'=>6,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[3] = array('Substance'=>'5_C2-C3','Spot ID'=>7,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[4] = array('Substance'=>'9_G','Spot ID'=>8,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[5] = array('Substance'=>'10_H','Spot ID'=>9,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[6] = array('Substance'=>'12_J','Spot ID'=>10,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[7] = array('Substance'=>'13_K','Spot ID'=>11,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[8] = array('Substance'=>'14_L','Spot ID'=>12,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[9] = array('Substance'=>'16_M','Spot ID'=>13,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[10] = array('Substance'=>'18_N','Spot ID'=>14,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[11] = array('Substance'=>'19_0','Spot ID'=>23,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[12] = array('Substance'=>'21_P','Spot ID'=>24,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[13] = array('Substance'=>'23_S','Spot ID'=>25,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[14] = array('Substance'=>'24_V','Spot ID'=>26,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[15] = array('Substance'=>'26_Y','Spot ID'=>27,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[16] = array('Substance'=>'28_O-58','Spot ID'=>28,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[17] = array('Substance'=>'29_O-61','Spot ID'=>29,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[18] = array('Substance'=>'33_c','Spot ID'=>30,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[19] = array('Substance'=>'35_d-2','Spot ID'=>31,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[20] = array('Substance'=>'36_e,h','Spot ID'=>32,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[21] = array('Substance'=>'39_f sub 1-2','Spot ID'=>33,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[22] = array('Substance'=>'40_f,g','Spot ID'=>34,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[23] = array('Substance'=>'42_f,g,t-2','Spot ID'=>35,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[24] = array('Substance'=>'44_f,g,s','Spot ID'=>41,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[99999] = array('Substance'=>'45_f,g,m,t','Spot ID'=>42,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[100000] = array('Substance'=>'46_f,g,m,t-2','Spot ID'=>43,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[25] = array('Substance'=>'56_g,s,t or g,t','Spot ID'=>44,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[26] = array('Substance'=>'57_g,z51','Spot ID'=>45,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[27] = array('Substance'=>'59_k','Spot ID'=>46,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[28] = array('Substance'=>'61_l,z13_l,v','Spot ID'=>47,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[29] = array('Substance'=>'64_l,z28','Spot ID'=>48,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[30] = array('Substance'=>'67_r','Spot ID'=>49,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[31] = array('Substance'=>'69_r,[i]','Spot ID'=>50,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[32] = array('Substance'=>'71_z','Spot ID'=>51,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[33] = array('Substance'=>'73_z10','Spot ID'=>52,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[34] = array('Substance'=>'74_z29','Spot ID'=>53,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[35] = array('Substance'=>'75_z38','Spot ID'=>54,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[36] = array('Substance'=>'76_z4,z23','Spot ID'=>55,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[37] = array('Substance'=>'78_z4,z24','Spot ID'=>59,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[38] = array('Substance'=>'82_1,2_1,5_1,2,7','Spot ID'=>60,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[39] = array('Substance'=>'83_1,7_Indiana','Spot ID'=>61,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[40] = array('Substance'=>'86_1,7','Spot ID'=>62,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[41] = array('Substance'=>'87_1,6','Spot ID'=>63,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[42] = array('Substance'=>'88_1,5_1,2,7','Spot ID'=>64,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[43] = array('Substance'=>'93_1,5','Spot ID'=>65,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[44] = array('Substance'=>'94_1,5-2','Spot ID'=>66,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[45] = array('Substance'=>'96_1,5-4','Spot ID'=>67,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[46] = array('Substance'=>'106_e,n,x-4','Spot ID'=>68,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[47] = array('Substance'=>'107_e,n,z15 and e,n,x,z15','Spot ID'=>69,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[48] = array('Substance'=>'108_ e,n,x,z15','Spot ID'=>70,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[49] = array('Substance'=>'114_l,w','Spot ID'=>71,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[50] = array('Substance'=>'116_z6 and z67 -2','Spot ID'=>72,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[51] = array('Substance'=>'120_invA','Spot ID'=>73,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[52] = array('Substance'=>'122_Vi','Spot ID'=>74,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[53] = array('Substance'=>'137_RHS-Gallinarum-2','Spot ID'=>75,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[54] = array('Substance'=>'139_1,2','Spot ID'=>77,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[55] = array('Substance'=>'140_B-3','Spot ID'=>78,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[56] = array('Substance'=>'141_B-4','Spot ID'=>79,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[57] = array('Substance'=>'142_D1/D2-3','Spot ID'=>80,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[57] = array('Substance'=>'147_E1-2','Spot ID'=>83,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[58] = array('Substance'=>'152_m,t_mtpu_gmt','Spot ID'=>84,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[59] = array('Substance'=>'153_m,t_mtpu_gmt-2','Spot ID'=>85,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[58] = array('Substance'=>'154_m,t_mtpu_gmt-3','Spot ID'=>86,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[59] = array('Substance'=>'157_fgt,fg,gmt','Spot ID'=>87,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[60] = array('Substance'=>'161_a-3','Spot ID'=>88,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[61] = array('Substance'=>'168_b-5','Spot ID'=>89,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[62] = array('Substance'=>'169_y-2','Spot ID'=>92,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[63] = array('Substance'=>'170_y-3','Spot ID'=>93,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[64] = array('Substance'=>'173_g,m,s-4 (g,m[p]s)','Spot ID'=>94,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[65] = array('Substance'=>'179_l,z13,z28-2 (FliC)','Spot ID'=>95,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[66] = array('Substance'=>'180_l,z13,z28-2 (FljB)','Spot ID'=>96,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[66] = array('Substance'=>'183_g,m,q or g,q-2','Spot ID'=>97,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[67] = array('Substance'=>'188_m,t_g,m,t','Spot ID'=>98,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[68] = array('Substance'=>'190_g,p_g.p.s','Spot ID'=>99,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[69] = array('Substance'=>'210_Pullorum-1','Spot ID'=>102,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[70] = array('Substance'=>'213_Enteritidis-1','Spot ID'=>103,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[71] = array('Substance'=>'216_i-2','Spot ID'=>104,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[72] = array('Substance'=>'219_gp-1','Spot ID'=>105,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[73] = array('Substance'=>'223_Kottbus','Spot ID'=>106,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[74] = array('Substance'=>'224_O:61-k1','Spot ID'=>107,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[75] = array('Substance'=>'D1/D2-5','Spot ID'=>108,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[76] = array('Substance'=>'gpu-2','Spot ID'=>111,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[77] = array('Substance'=>'gpu-gp-fg-gst','Spot ID'=>112,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[78] = array('Substance'=>'fgt-gmt-mptu-mt','Spot ID'=>113,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[79] = array('Substance'=>'gp-2','Spot ID'=>114,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[76] = array('Substance'=>'gp-3','Spot ID'=>115,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[77] = array('Substance'=>'gmq-gq-5','Spot ID'=>116,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[78] = array('Substance'=>'gt-1','Spot ID'=>117,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[79] = array('Substance'=>'d-4','Spot ID'=>118,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[80] = array('Substance'=>'1,2,7-5','Spot ID'=>119,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[81] = array('Substance'=>'1,5,7','Spot ID'=>120,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[82] = array('Substance'=>'r,[i]-4','Spot ID'=>121,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[83] = array('Substance'=>'Ent-shdA','Spot ID'=>122,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[84] = array('Substance'=>'Dublin-nupC','Spot ID'=>123,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[85] = array('Substance'=>'Ent-rfbE','Spot ID'=>124,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[86] = array('Substance'=>'pepT','Spot ID'=>125,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[87] = array('Substance'=>'pepT-3','Spot ID'=>126,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[87] = array('Substance'=>'p','Spot ID'=>127,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[208] = array('Substance'=>'0,1M NaPP Standard pH 9','Spot ID'=>128,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[209] = array('Substance'=>'Biotin-Marke_2,5µM','Spot ID'=>129,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[210] = array('Substance'=>'1_A','Spot ID'=>131,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[211] = array('Substance'=>'4_C1','Spot ID'=>132,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[212] = array('Substance'=>'5_C2-C3','Spot ID'=>133,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[213] = array('Substance'=>'9_G','Spot ID'=>134,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[214] = array('Substance'=>'10_H','Spot ID'=>135,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[215] = array('Substance'=>'12_J','Spot ID'=>136,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[216] = array('Substance'=>'13_K','Spot ID'=>137,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[217] = array('Substance'=>'14_L','Spot ID'=>138,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[218] = array('Substance'=>'16_M','Spot ID'=>139,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[219] = array('Substance'=>'18_N','Spot ID'=>140,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[220] = array('Substance'=>'19_0','Spot ID'=>141,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[221] = array('Substance'=>'21_P','Spot ID'=>142,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[222] = array('Substance'=>'23_S','Spot ID'=>143,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[223] = array('Substance'=>'24_V','Spot ID'=>144,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[224] = array('Substance'=>'26_Y','Spot ID'=>145,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[225] = array('Substance'=>'28_O-58','Spot ID'=>146,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[226] = array('Substance'=>'29_O-61','Spot ID'=>147,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[227] = array('Substance'=>'33_c','Spot ID'=>148,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[228] = array('Substance'=>'35_d-2','Spot ID'=>149,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[229] = array('Substance'=>'36_e,h','Spot ID'=>150,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[230] = array('Substance'=>'39_f sub 1-2','Spot ID'=>151,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[231] = array('Substance'=>'40_f,g','Spot ID'=>152,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[232] = array('Substance'=>'42_f,g,t-2','Spot ID'=>153,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[233] = array('Substance'=>'44_f,g,s','Spot ID'=>154,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[234] = array('Substance'=>'45_f,g,m,t','Spot ID'=>155,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[235] = array('Substance'=>'46_f,g,m,t-2','Spot ID'=>156,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[234] = array('Substance'=>'56_g,s,t or g,t','Spot ID'=>157,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[235] = array('Substance'=>'57_g,z51','Spot ID'=>158,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[236] = array('Substance'=>'59_k','Spot ID'=>159,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[237] = array('Substance'=>'61_l,z13_l,v','Spot ID'=>160,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[238] = array('Substance'=>'64_l,z28','Spot ID'=>161,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[239] = array('Substance'=>'67_r','Spot ID'=>162,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[240] = array('Substance'=>'69_r,[i]','Spot ID'=>163,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[241] = array('Substance'=>'71_z','Spot ID'=>164,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[242] = array('Substance'=>'73_z10','Spot ID'=>165,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[243] = array('Substance'=>'74_z29','Spot ID'=>166,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[244] = array('Substance'=>'75_z38','Spot ID'=>167,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[245] = array('Substance'=>'76_z4,z23','Spot ID'=>168,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[246] = array('Substance'=>'78_z4,z24','Spot ID'=>169,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[247] = array('Substance'=>'82_1,2_1,5_1,2,7','Spot ID'=>170,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[248] = array('Substance'=>'83_1,7_Indiana','Spot ID'=>171,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[249] = array('Substance'=>'86_1,7','Spot ID'=>172,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[250] = array('Substance'=>'87_1,6','Spot ID'=>173,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[251] = array('Substance'=>'88_1,5_1,2,7','Spot ID'=>174,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[252] = array('Substance'=>'93_1,5','Spot ID'=>175,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[253] = array('Substance'=>'94_1,5-2','Spot ID'=>176,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[254] = array('Substance'=>'96_1,5-4','Spot ID'=>177,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[255] = array('Substance'=>'106_e,n,x-4','Spot ID'=>178,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[256] = array('Substance'=>'107_e,n,z15 and e,n,x,z15','Spot ID'=>179,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[257] = array('Substance'=>'108_ e,n,x,z15','Spot ID'=>180,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[258] = array('Substance'=>'114_l,w','Spot ID'=>181,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[259] = array('Substance'=>'116_z6 and z67 -2','Spot ID'=>182,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[260] = array('Substance'=>'120_invA','Spot ID'=>183,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[261] = array('Substance'=>'122_Vi','Spot ID'=>184,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[262] = array('Substance'=>'137_RHS-Gallinarum-2','Spot ID'=>185,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[263] = array('Substance'=>'139_1,2','Spot ID'=>186,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[264] = array('Substance'=>'140_B-3','Spot ID'=>187,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[265] = array('Substance'=>'141_B-4','Spot ID'=>188,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[266] = array('Substance'=>'142_D1/D2-3','Spot ID'=>189,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[266] = array('Substance'=>'147_E1-2','Spot ID'=>190,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[267] = array('Substance'=>'152_m,t_mtpu_gmt','Spot ID'=>191,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[268] = array('Substance'=>'153_m,t_mtpu_gmt-2','Spot ID'=>192,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[267] = array('Substance'=>'154_m,t_mtpu_gmt-3','Spot ID'=>193,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[268] = array('Substance'=>'157_fgt,fg,gmt','Spot ID'=>194,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[269] = array('Substance'=>'161_a-3','Spot ID'=>195,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[270] = array('Substance'=>'168_b-5','Spot ID'=>196,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[271] = array('Substance'=>'169_y-2','Spot ID'=>197,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[272] = array('Substance'=>'170_y-3','Spot ID'=>198,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[273] = array('Substance'=>'173_g,m,s-4 (g,m[p]s)','Spot ID'=>199,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[274] = array('Substance'=>'179_l,z13,z28-2 (FliC)','Spot ID'=>200,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[275] = array('Substance'=>'180_l,z13,z28-2 (FljB)','Spot ID'=>201,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[275] = array('Substance'=>'183_g,m,q or g,q-2','Spot ID'=>202,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[276] = array('Substance'=>'188_m,t_g,m,t','Spot ID'=>203,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[277] = array('Substance'=>'190_g,p_g.p.s','Spot ID'=>204,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[278] = array('Substance'=>'210_Pullorum-1','Spot ID'=>205,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[279] = array('Substance'=>'213_Enteritidis-1','Spot ID'=>206,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[280] = array('Substance'=>'216_i-2','Spot ID'=>207,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[281] = array('Substance'=>'219_gp-1','Spot ID'=>208,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[282] = array('Substance'=>'223_Kottbus','Spot ID'=>209,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[283] = array('Substance'=>'224_O:61-k1','Spot ID'=>210,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[284] = array('Substance'=>'D1/D2-5','Spot ID'=>211,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[285] = array('Substance'=>'gpu-2','Spot ID'=>212,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[286] = array('Substance'=>'gpu-gp-fg-gst','Spot ID'=>213,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[287] = array('Substance'=>'fgt-gmt-mptu-mt','Spot ID'=>214,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[288] = array('Substance'=>'gp-2','Spot ID'=>215,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[285] = array('Substance'=>'gp-3','Spot ID'=>216,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[286] = array('Substance'=>'gmq-gq-5','Spot ID'=>217,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[287] = array('Substance'=>'gt-1','Spot ID'=>218,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[288] = array('Substance'=>'d-4','Spot ID'=>219,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[289] = array('Substance'=>'1,2,7-5','Spot ID'=>220,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[290] = array('Substance'=>'1,5,7','Spot ID'=>221,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[291] = array('Substance'=>'r,[i]-4','Spot ID'=>222,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[292] = array('Substance'=>'Ent-shdA','Spot ID'=>223,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[293] = array('Substance'=>'Dublin-nupC','Spot ID'=>224,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[294] = array('Substance'=>'Ent-rfbE','Spot ID'=>225,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[295] = array('Substance'=>'pepT','Spot ID'=>226,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[296] = array('Substance'=>'pepT-3','Spot ID'=>227,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[296] = array('Substance'=>'p','Spot ID'=>228,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[417] = array('Substance'=>'0,1M NaPP Standard pH 9','Spot ID'=>229,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[418] = array('Substance'=>'Biotin-Marke_2,5µM','Spot ID'=>230,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[419] = array('Substance'=>'1_A','Spot ID'=>232,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[420] = array('Substance'=>'4_C1','Spot ID'=>235,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[421] = array('Substance'=>'5_C2-C3','Spot ID'=>236,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[422] = array('Substance'=>'9_G','Spot ID'=>237,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[423] = array('Substance'=>'10_H','Spot ID'=>238,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[424] = array('Substance'=>'12_J','Spot ID'=>239,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[425] = array('Substance'=>'13_K','Spot ID'=>240,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[426] = array('Substance'=>'14_L','Spot ID'=>241,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[427] = array('Substance'=>'16_M','Spot ID'=>242,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[428] = array('Substance'=>'18_N','Spot ID'=>245,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[429] = array('Substance'=>'19_0','Spot ID'=>246,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[430] = array('Substance'=>'21_P','Spot ID'=>247,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[431] = array('Substance'=>'23_S','Spot ID'=>248,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[432] = array('Substance'=>'24_V','Spot ID'=>249,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[433] = array('Substance'=>'26_Y','Spot ID'=>250,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[434] = array('Substance'=>'28_O-58','Spot ID'=>251,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[435] = array('Substance'=>'29_O-61','Spot ID'=>254,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[436] = array('Substance'=>'33_c','Spot ID'=>255,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[437] = array('Substance'=>'35_d-2','Spot ID'=>256,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[438] = array('Substance'=>'36_e,h','Spot ID'=>257,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[439] = array('Substance'=>'39_f sub 1-2','Spot ID'=>258,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[440] = array('Substance'=>'40_f,g','Spot ID'=>259,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[441] = array('Substance'=>'42_f,g,t-2','Spot ID'=>260,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[442] = array('Substance'=>'44_f,g,s','Spot ID'=>261,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[443] = array('Substance'=>'45_f,g,m,t','Spot ID'=>264,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[444] = array('Substance'=>'46_f,g,m,t-2','Spot ID'=>265,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[443] = array('Substance'=>'56_g,s,t or g,t','Spot ID'=>266,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[444] = array('Substance'=>'57_g,z51','Spot ID'=>267,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[445] = array('Substance'=>'59_k','Spot ID'=>268,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[446] = array('Substance'=>'61_l,z13_l,v','Spot ID'=>269,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[447] = array('Substance'=>'64_l,z28','Spot ID'=>270,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[448] = array('Substance'=>'67_r','Spot ID'=>273,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[449] = array('Substance'=>'69_r,[i]','Spot ID'=>274,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[450] = array('Substance'=>'71_z','Spot ID'=>275,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[451] = array('Substance'=>'73_z10','Spot ID'=>276,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[452] = array('Substance'=>'74_z29','Spot ID'=>277,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[453] = array('Substance'=>'75_z38','Spot ID'=>278,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[454] = array('Substance'=>'76_z4,z23','Spot ID'=>279,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[455] = array('Substance'=>'78_z4,z24','Spot ID'=>280,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[456] = array('Substance'=>'82_1,2_1,5_1,2,7','Spot ID'=>281,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[457] = array('Substance'=>'83_1,7_Indiana','Spot ID'=>282,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[458] = array('Substance'=>'86_1,7','Spot ID'=>283,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[459] = array('Substance'=>'87_1,6','Spot ID'=>284,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[460] = array('Substance'=>'88_1,5_1,2,7','Spot ID'=>285,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[461] = array('Substance'=>'93_1,5','Spot ID'=>287,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[462] = array('Substance'=>'94_1,5-2','Spot ID'=>288,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[463] = array('Substance'=>'96_1,5-4','Spot ID'=>289,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[464] = array('Substance'=>'106_e,n,x-4','Spot ID'=>290,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[465] = array('Substance'=>'107_e,n,z15 and e,n,x,z15','Spot ID'=>291,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[466] = array('Substance'=>'108_ e,n,x,z15','Spot ID'=>292,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[467] = array('Substance'=>'114_l,w','Spot ID'=>293,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[468] = array('Substance'=>'116_z6 and z67 -2','Spot ID'=>294,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[469] = array('Substance'=>'120_invA','Spot ID'=>295,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[470] = array('Substance'=>'122_Vi','Spot ID'=>296,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[471] = array('Substance'=>'137_RHS-Gallinarum-2','Spot ID'=>297,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[472] = array('Substance'=>'139_1,2','Spot ID'=>298,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[473] = array('Substance'=>'140_B-3','Spot ID'=>299,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[474] = array('Substance'=>'141_B-4','Spot ID'=>300,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[475] = array('Substance'=>'142_D1/D2-3','Spot ID'=>301,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[475] = array('Substance'=>'147_E1-2','Spot ID'=>302,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[476] = array('Substance'=>'152_m,t_mtpu_gmt','Spot ID'=>303,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[477] = array('Substance'=>'153_m,t_mtpu_gmt-2','Spot ID'=>307,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[476] = array('Substance'=>'154_m,t_mtpu_gmt-3','Spot ID'=>308,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[477] = array('Substance'=>'157_fgt,fg,gmt','Spot ID'=>309,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[478] = array('Substance'=>'161_a-3','Spot ID'=>310,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[479] = array('Substance'=>'168_b-5','Spot ID'=>311,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[480] = array('Substance'=>'169_y-2','Spot ID'=>312,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[481] = array('Substance'=>'170_y-3','Spot ID'=>313,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[482] = array('Substance'=>'173_g,m,s-4 (g,m[p]s)','Spot ID'=>314,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[483] = array('Substance'=>'179_l,z13,z28-2 (FliC)','Spot ID'=>315,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[484] = array('Substance'=>'180_l,z13,z28-2 (FljB)','Spot ID'=>316,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[484] = array('Substance'=>'183_g,m,q or g,q-2','Spot ID'=>317,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[485] = array('Substance'=>'188_m,t_g,m,t','Spot ID'=>318,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[486] = array('Substance'=>'190_g,p_g.p.s','Spot ID'=>319,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[487] = array('Substance'=>'210_Pullorum-1','Spot ID'=>320,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[488] = array('Substance'=>'213_Enteritidis-1','Spot ID'=>321,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[489] = array('Substance'=>'216_i-2','Spot ID'=>327,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[490] = array('Substance'=>'219_gp-1','Spot ID'=>328,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[491] = array('Substance'=>'223_Kottbus','Spot ID'=>329,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[492] = array('Substance'=>'224_O:61-k1','Spot ID'=>330,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[493] = array('Substance'=>'D1/D2-5','Spot ID'=>331,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[494] = array('Substance'=>'gpu-2','Spot ID'=>332,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[495] = array('Substance'=>'gpu-gp-fg-gst','Spot ID'=>333,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[496] = array('Substance'=>'fgt-gmt-mptu-mt','Spot ID'=>334,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[497] = array('Substance'=>'gp-2','Spot ID'=>335,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[494] = array('Substance'=>'gp-3','Spot ID'=>336,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[495] = array('Substance'=>'gmq-gq-5','Spot ID'=>337,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[496] = array('Substance'=>'gt-1','Spot ID'=>338,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[497] = array('Substance'=>'d-4','Spot ID'=>339,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[498] = array('Substance'=>'1,2,7-5','Spot ID'=>347,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[499] = array('Substance'=>'1,5,7','Spot ID'=>348,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[500] = array('Substance'=>'r,[i]-4','Spot ID'=>349,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[501] = array('Substance'=>'Ent-shdA','Spot ID'=>350,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[502] = array('Substance'=>'Dublin-nupC','Spot ID'=>351,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[503] = array('Substance'=>'Ent-rfbE','Spot ID'=>352,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[504] = array('Substance'=>'pepT','Spot ID'=>353,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[505] = array('Substance'=>'pepT-3','Spot ID'=>354,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[505] = array('Substance'=>'p','Spot ID'=>355,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[626] = array('Substance'=>'0,1M NaPP Standard pH 9','Spot ID'=>356,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[627] = array('Substance'=>'Biotin-Marke_2,5µM','Spot ID'=>357,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
    return $template;
}

function template1()
{
$template[1] = array('Substance'=>'1_A','Spot ID'=>5,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[2] = array('Substance'=>'4_C1','Spot ID'=>6,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[3] = array('Substance'=>'5_C2-C3','Spot ID'=>7,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[4] = array('Substance'=>'9_G','Spot ID'=>8,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[5] = array('Substance'=>'10_H','Spot ID'=>9,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[6] = array('Substance'=>'12_J','Spot ID'=>10,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[7] = array('Substance'=>'13_K','Spot ID'=>11,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[8] = array('Substance'=>'14_L','Spot ID'=>12,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[9] = array('Substance'=>'16_M','Spot ID'=>13,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[10] = array('Substance'=>'18_N','Spot ID'=>14,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[11] = array('Substance'=>'19_0','Spot ID'=>23,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[12] = array('Substance'=>'21_P','Spot ID'=>24,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[13] = array('Substance'=>'23_S','Spot ID'=>25,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[14] = array('Substance'=>'24_V','Spot ID'=>26,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[15] = array('Substance'=>'26_Y','Spot ID'=>27,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[16] = array('Substance'=>'28_O-58','Spot ID'=>28,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[17] = array('Substance'=>'29_O-61','Spot ID'=>29,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[18] = array('Substance'=>'33_c','Spot ID'=>30,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[19] = array('Substance'=>'35_d-2','Spot ID'=>31,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[20] = array('Substance'=>'36_e,h','Spot ID'=>32,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[21] = array('Substance'=>'39_f sub 1-2','Spot ID'=>33,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[22] = array('Substance'=>'40_f,g','Spot ID'=>34,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[23] = array('Substance'=>'42_f,g,t-2','Spot ID'=>35,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[24] = array('Substance'=>'44_f,g,s','Spot ID'=>41,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[25] = array('Substance'=>'45_f,g,m,t','Spot ID'=>42,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[26] = array('Substance'=>'46_f,g,m,t-2','Spot ID'=>43,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[27] = array('Substance'=>'56_g,s,t or g,t','Spot ID'=>44,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[28] = array('Substance'=>'57_g,z51','Spot ID'=>45,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[29] = array('Substance'=>'59_k','Spot ID'=>46,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[30] = array('Substance'=>'61_l,z13_l,v','Spot ID'=>47,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[31] = array('Substance'=>'64_l,z28','Spot ID'=>48,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[32] = array('Substance'=>'67_r','Spot ID'=>49,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[33] = array('Substance'=>'69_r,[i]','Spot ID'=>50,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[34] = array('Substance'=>'71_z','Spot ID'=>51,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[35] = array('Substance'=>'73_z10','Spot ID'=>52,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[36] = array('Substance'=>'74_z29','Spot ID'=>53,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[37] = array('Substance'=>'75_z38','Spot ID'=>54,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[38] = array('Substance'=>'76_z4,z23','Spot ID'=>55,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[39] = array('Substance'=>'78_z4,z24','Spot ID'=>59,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[40] = array('Substance'=>'82_1,2_1,5_1,2,7','Spot ID'=>60,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[41] = array('Substance'=>'83_1,7_Indiana','Spot ID'=>61,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[42] = array('Substance'=>'86_1,7','Spot ID'=>62,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[43] = array('Substance'=>'87_1,6','Spot ID'=>63,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[44] = array('Substance'=>'88_1,5_1,2,7','Spot ID'=>64,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[45] = array('Substance'=>'93_1,5','Spot ID'=>65,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[46] = array('Substance'=>'94_1,5-2','Spot ID'=>66,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[47] = array('Substance'=>'96_1,5-4','Spot ID'=>67,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[48] = array('Substance'=>'106_e,n,x-4','Spot ID'=>68,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[49] = array('Substance'=>'107_e,n,z15 and e,n,x,z15','Spot ID'=>69,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[50] = array('Substance'=>'108_ e,n,x,z15','Spot ID'=>70,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[51] = array('Substance'=>'114_l,w','Spot ID'=>71,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[52] = array('Substance'=>'116_z6 and z67 -2','Spot ID'=>72,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[53] = array('Substance'=>'120_invA','Spot ID'=>73,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[54] = array('Substance'=>'122_Vi','Spot ID'=>74,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[55] = array('Substance'=>'137_RHS-Gallinarum-2','Spot ID'=>75,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[56] = array('Substance'=>'139_1,2','Spot ID'=>77,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[57] = array('Substance'=>'140_B-3','Spot ID'=>78,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[58] = array('Substance'=>'141_B-4','Spot ID'=>79,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[59] = array('Substance'=>'142_D1/D2-3','Spot ID'=>80,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[60] = array('Substance'=>'147_E1-2','Spot ID'=>83,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[61] = array('Substance'=>'152_m,t_mtpu_gmt','Spot ID'=>84,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[62] = array('Substance'=>'153_m,t_mtpu_gmt-2','Spot ID'=>85,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[63] = array('Substance'=>'154_m,t_mtpu_gmt-3','Spot ID'=>86,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[64] = array('Substance'=>'157_fgt,fg,gmt','Spot ID'=>87,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[65] = array('Substance'=>'161_a-3','Spot ID'=>88,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[66] = array('Substance'=>'168_b-5','Spot ID'=>89,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[67] = array('Substance'=>'169_y-2','Spot ID'=>92,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[68] = array('Substance'=>'170_y-3','Spot ID'=>93,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[69] = array('Substance'=>'173_g,m,s-4 (g,m[p]s)','Spot ID'=>94,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[70] = array('Substance'=>'179_l,z13,z28-2 (FliC)','Spot ID'=>95,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[71] = array('Substance'=>'180_l,z13,z28-2 (FljB)','Spot ID'=>96,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[72] = array('Substance'=>'183_g,m,q or g,q-2','Spot ID'=>97,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[73] = array('Substance'=>'188_m,t_g,m,t','Spot ID'=>98,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[74] = array('Substance'=>'190_g,p_g.p.s','Spot ID'=>99,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[75] = array('Substance'=>'210_Pullorum-1','Spot ID'=>102,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[76] = array('Substance'=>'213_Enteritidis-1','Spot ID'=>103,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[77] = array('Substance'=>'216_i-2','Spot ID'=>104,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[78] = array('Substance'=>'219_gp-1','Spot ID'=>105,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[79] = array('Substance'=>'223_Kottbus','Spot ID'=>106,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[80] = array('Substance'=>'224_O:61-k1','Spot ID'=>107,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[81] = array('Substance'=>'D1/D2-5','Spot ID'=>108,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[82] = array('Substance'=>'gpu-2','Spot ID'=>111,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[83] = array('Substance'=>'gpu-gp-fg-gst','Spot ID'=>112,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[84] = array('Substance'=>'fgt-gmt-mptu-mt','Spot ID'=>113,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[85] = array('Substance'=>'gp-2','Spot ID'=>114,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[86] = array('Substance'=>'gp-3','Spot ID'=>115,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[87] = array('Substance'=>'gmq-gq-5','Spot ID'=>116,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[88] = array('Substance'=>'gt-1','Spot ID'=>117,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[89] = array('Substance'=>'d-4','Spot ID'=>118,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[90] = array('Substance'=>'1,2,7-5','Spot ID'=>119,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[91] = array('Substance'=>'1,5,7','Spot ID'=>120,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[92] = array('Substance'=>'r,[i]-4','Spot ID'=>121,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[93] = array('Substance'=>'Ent-shdA','Spot ID'=>122,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[94] = array('Substance'=>'Dublin-nupC','Spot ID'=>123,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[95] = array('Substance'=>'Ent-rfbE','Spot ID'=>124,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[96] = array('Substance'=>'pepT','Spot ID'=>125,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[97] = array('Substance'=>'pepT-3','Spot ID'=>126,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[98] = array('Substance'=>'p','Spot ID'=>127,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[99] = array('Substance'=>'0,1M NaPP Standard pH 9','Spot ID'=>128,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[100] = array('Substance'=>'Biotin-Marke_2,5µM','Spot ID'=>129,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[101] = array('Substance'=>'1_A','Spot ID'=>131,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[102] = array('Substance'=>'4_C1','Spot ID'=>132,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[103] = array('Substance'=>'5_C2-C3','Spot ID'=>133,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[104] = array('Substance'=>'9_G','Spot ID'=>134,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[105] = array('Substance'=>'10_H','Spot ID'=>135,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[106] = array('Substance'=>'12_J','Spot ID'=>136,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[107] = array('Substance'=>'13_K','Spot ID'=>137,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[108] = array('Substance'=>'14_L','Spot ID'=>138,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[109] = array('Substance'=>'16_M','Spot ID'=>139,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[110] = array('Substance'=>'18_N','Spot ID'=>140,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[111] = array('Substance'=>'19_0','Spot ID'=>141,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[112] = array('Substance'=>'21_P','Spot ID'=>142,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[113] = array('Substance'=>'23_S','Spot ID'=>143,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[114] = array('Substance'=>'24_V','Spot ID'=>144,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[115] = array('Substance'=>'26_Y','Spot ID'=>145,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[116] = array('Substance'=>'28_O-58','Spot ID'=>146,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[117] = array('Substance'=>'29_O-61','Spot ID'=>147,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[118] = array('Substance'=>'33_c','Spot ID'=>148,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[119] = array('Substance'=>'35_d-2','Spot ID'=>149,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[120] = array('Substance'=>'36_e,h','Spot ID'=>150,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[121] = array('Substance'=>'39_f sub 1-2','Spot ID'=>151,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[122] = array('Substance'=>'40_f,g','Spot ID'=>152,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[123] = array('Substance'=>'42_f,g,t-2','Spot ID'=>153,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[124] = array('Substance'=>'44_f,g,s','Spot ID'=>154,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[125] = array('Substance'=>'45_f,g,m,t','Spot ID'=>155,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[126] = array('Substance'=>'46_f,g,m,t-2','Spot ID'=>156,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[127] = array('Substance'=>'56_g,s,t or g,t','Spot ID'=>157,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[128] = array('Substance'=>'57_g,z51','Spot ID'=>158,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[129] = array('Substance'=>'59_k','Spot ID'=>159,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[130] = array('Substance'=>'61_l,z13_l,v','Spot ID'=>160,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[131] = array('Substance'=>'64_l,z28','Spot ID'=>161,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[132] = array('Substance'=>'67_r','Spot ID'=>162,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[133] = array('Substance'=>'69_r,[i]','Spot ID'=>163,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[134] = array('Substance'=>'71_z','Spot ID'=>164,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[135] = array('Substance'=>'73_z10','Spot ID'=>165,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[136] = array('Substance'=>'74_z29','Spot ID'=>166,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[137] = array('Substance'=>'75_z38','Spot ID'=>167,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[138] = array('Substance'=>'76_z4,z23','Spot ID'=>168,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[139] = array('Substance'=>'78_z4,z24','Spot ID'=>169,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[140] = array('Substance'=>'82_1,2_1,5_1,2,7','Spot ID'=>170,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[141] = array('Substance'=>'83_1,7_Indiana','Spot ID'=>171,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[142] = array('Substance'=>'86_1,7','Spot ID'=>172,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[143] = array('Substance'=>'87_1,6','Spot ID'=>173,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[144] = array('Substance'=>'88_1,5_1,2,7','Spot ID'=>174,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[145] = array('Substance'=>'93_1,5','Spot ID'=>175,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[146] = array('Substance'=>'94_1,5-2','Spot ID'=>176,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[147] = array('Substance'=>'96_1,5-4','Spot ID'=>177,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[148] = array('Substance'=>'106_e,n,x-4','Spot ID'=>178,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[149] = array('Substance'=>'107_e,n,z15 and e,n,x,z15','Spot ID'=>179,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[150] = array('Substance'=>'108_ e,n,x,z15','Spot ID'=>180,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[151] = array('Substance'=>'114_l,w','Spot ID'=>181,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[152] = array('Substance'=>'116_z6 and z67 -2','Spot ID'=>182,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[153] = array('Substance'=>'120_invA','Spot ID'=>183,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[154] = array('Substance'=>'122_Vi','Spot ID'=>184,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[155] = array('Substance'=>'137_RHS-Gallinarum-2','Spot ID'=>185,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[156] = array('Substance'=>'139_1,2','Spot ID'=>186,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[157] = array('Substance'=>'140_B-3','Spot ID'=>187,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[158] = array('Substance'=>'141_B-4','Spot ID'=>188,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[159] = array('Substance'=>'142_D1/D2-3','Spot ID'=>189,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[160] = array('Substance'=>'147_E1-2','Spot ID'=>190,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[161] = array('Substance'=>'152_m,t_mtpu_gmt','Spot ID'=>191,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[162] = array('Substance'=>'153_m,t_mtpu_gmt-2','Spot ID'=>192,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[163] = array('Substance'=>'154_m,t_mtpu_gmt-3','Spot ID'=>193,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[164] = array('Substance'=>'157_fgt,fg,gmt','Spot ID'=>194,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[165] = array('Substance'=>'161_a-3','Spot ID'=>195,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[166] = array('Substance'=>'168_b-5','Spot ID'=>196,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[167] = array('Substance'=>'169_y-2','Spot ID'=>197,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[168] = array('Substance'=>'170_y-3','Spot ID'=>198,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[169] = array('Substance'=>'173_g,m,s-4 (g,m[p]s)','Spot ID'=>199,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[170] = array('Substance'=>'179_l,z13,z28-2 (FliC)','Spot ID'=>200,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[171] = array('Substance'=>'180_l,z13,z28-2 (FljB)','Spot ID'=>201,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[172] = array('Substance'=>'183_g,m,q or g,q-2','Spot ID'=>202,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[173] = array('Substance'=>'188_m,t_g,m,t','Spot ID'=>203,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[174] = array('Substance'=>'190_g,p_g.p.s','Spot ID'=>204,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[175] = array('Substance'=>'210_Pullorum-1','Spot ID'=>205,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[176] = array('Substance'=>'213_Enteritidis-1','Spot ID'=>206,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[177] = array('Substance'=>'216_i-2','Spot ID'=>207,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[178] = array('Substance'=>'219_gp-1','Spot ID'=>208,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[179] = array('Substance'=>'223_Kottbus','Spot ID'=>209,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[180] = array('Substance'=>'224_O:61-k1','Spot ID'=>210,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[181] = array('Substance'=>'D1/D2-5','Spot ID'=>211,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[182] = array('Substance'=>'gpu-2','Spot ID'=>212,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[183] = array('Substance'=>'gpu-gp-fg-gst','Spot ID'=>213,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[184] = array('Substance'=>'fgt-gmt-mptu-mt','Spot ID'=>214,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[185] = array('Substance'=>'gp-2','Spot ID'=>215,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[186] = array('Substance'=>'gp-3','Spot ID'=>216,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[187] = array('Substance'=>'gmq-gq-5','Spot ID'=>217,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[188] = array('Substance'=>'gt-1','Spot ID'=>218,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[189] = array('Substance'=>'d-4','Spot ID'=>219,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[190] = array('Substance'=>'1,2,7-5','Spot ID'=>220,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[191] = array('Substance'=>'1,5,7','Spot ID'=>221,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[192] = array('Substance'=>'r,[i]-4','Spot ID'=>222,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[193] = array('Substance'=>'Ent-shdA','Spot ID'=>223,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[194] = array('Substance'=>'Dublin-nupC','Spot ID'=>224,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[195] = array('Substance'=>'Ent-rfbE','Spot ID'=>225,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[196] = array('Substance'=>'pepT','Spot ID'=>226,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[197] = array('Substance'=>'pepT-3','Spot ID'=>227,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[198] = array('Substance'=>'p','Spot ID'=>228,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[199] = array('Substance'=>'0,1M NaPP Standard pH 9','Spot ID'=>229,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[200] = array('Substance'=>'Biotin-Marke_2,5µM','Spot ID'=>230,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[201] = array('Substance'=>'1_A','Spot ID'=>232,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[202] = array('Substance'=>'4_C1','Spot ID'=>235,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[203] = array('Substance'=>'5_C2-C3','Spot ID'=>236,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[204] = array('Substance'=>'9_G','Spot ID'=>237,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[205] = array('Substance'=>'10_H','Spot ID'=>238,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[206] = array('Substance'=>'12_J','Spot ID'=>239,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[207] = array('Substance'=>'13_K','Spot ID'=>240,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[208] = array('Substance'=>'14_L','Spot ID'=>241,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[209] = array('Substance'=>'16_M','Spot ID'=>242,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[210] = array('Substance'=>'18_N','Spot ID'=>245,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[211] = array('Substance'=>'19_0','Spot ID'=>246,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[212] = array('Substance'=>'21_P','Spot ID'=>247,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[213] = array('Substance'=>'23_S','Spot ID'=>248,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[214] = array('Substance'=>'24_V','Spot ID'=>249,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[215] = array('Substance'=>'26_Y','Spot ID'=>250,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[216] = array('Substance'=>'28_O-58','Spot ID'=>251,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[217] = array('Substance'=>'29_O-61','Spot ID'=>254,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[218] = array('Substance'=>'33_c','Spot ID'=>255,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[219] = array('Substance'=>'35_d-2','Spot ID'=>256,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[220] = array('Substance'=>'36_e,h','Spot ID'=>257,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[221] = array('Substance'=>'39_f sub 1-2','Spot ID'=>258,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[222] = array('Substance'=>'40_f,g','Spot ID'=>259,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[223] = array('Substance'=>'42_f,g,t-2','Spot ID'=>260,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[224] = array('Substance'=>'44_f,g,s','Spot ID'=>261,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[225] = array('Substance'=>'45_f,g,m,t','Spot ID'=>264,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[226] = array('Substance'=>'46_f,g,m,t-2','Spot ID'=>265,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[227] = array('Substance'=>'56_g,s,t or g,t','Spot ID'=>266,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[228] = array('Substance'=>'57_g,z51','Spot ID'=>267,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[229] = array('Substance'=>'59_k','Spot ID'=>268,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[230] = array('Substance'=>'61_l,z13_l,v','Spot ID'=>269,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[231] = array('Substance'=>'64_l,z28','Spot ID'=>270,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[232] = array('Substance'=>'67_r','Spot ID'=>273,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[233] = array('Substance'=>'69_r,[i]','Spot ID'=>274,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[234] = array('Substance'=>'71_z','Spot ID'=>275,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[235] = array('Substance'=>'73_z10','Spot ID'=>276,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[236] = array('Substance'=>'74_z29','Spot ID'=>277,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[237] = array('Substance'=>'75_z38','Spot ID'=>278,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[238] = array('Substance'=>'76_z4,z23','Spot ID'=>279,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[239] = array('Substance'=>'78_z4,z24','Spot ID'=>280,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[240] = array('Substance'=>'82_1,2_1,5_1,2,7','Spot ID'=>281,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[241] = array('Substance'=>'83_1,7_Indiana','Spot ID'=>282,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[242] = array('Substance'=>'86_1,7','Spot ID'=>283,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[243] = array('Substance'=>'87_1,6','Spot ID'=>284,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[244] = array('Substance'=>'88_1,5_1,2,7','Spot ID'=>285,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[245] = array('Substance'=>'93_1,5','Spot ID'=>287,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[246] = array('Substance'=>'94_1,5-2','Spot ID'=>288,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[247] = array('Substance'=>'96_1,5-4','Spot ID'=>289,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[248] = array('Substance'=>'106_e,n,x-4','Spot ID'=>290,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[249] = array('Substance'=>'107_e,n,z15 and e,n,x,z15','Spot ID'=>291,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[250] = array('Substance'=>'108_ e,n,x,z15','Spot ID'=>292,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[251] = array('Substance'=>'114_l,w','Spot ID'=>293,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[252] = array('Substance'=>'116_z6 and z67 -2','Spot ID'=>294,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[253] = array('Substance'=>'120_invA','Spot ID'=>295,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[254] = array('Substance'=>'122_Vi','Spot ID'=>296,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[255] = array('Substance'=>'137_RHS-Gallinarum-2','Spot ID'=>297,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[256] = array('Substance'=>'139_1,2','Spot ID'=>298,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[257] = array('Substance'=>'140_B-3','Spot ID'=>299,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[258] = array('Substance'=>'141_B-4','Spot ID'=>300,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[259] = array('Substance'=>'142_D1/D2-3','Spot ID'=>301,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[260] = array('Substance'=>'147_E1-2','Spot ID'=>302,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[261] = array('Substance'=>'152_m,t_mtpu_gmt','Spot ID'=>303,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[262] = array('Substance'=>'153_m,t_mtpu_gmt-2','Spot ID'=>307,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[263] = array('Substance'=>'154_m,t_mtpu_gmt-3','Spot ID'=>308,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[264] = array('Substance'=>'157_fgt,fg,gmt','Spot ID'=>309,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[265] = array('Substance'=>'161_a-3','Spot ID'=>310,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[266] = array('Substance'=>'168_b-5','Spot ID'=>311,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[267] = array('Substance'=>'169_y-2','Spot ID'=>312,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[268] = array('Substance'=>'170_y-3','Spot ID'=>313,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[269] = array('Substance'=>'173_g,m,s-4 (g,m[p]s)','Spot ID'=>314,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[270] = array('Substance'=>'179_l,z13,z28-2 (FliC)','Spot ID'=>315,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[271] = array('Substance'=>'180_l,z13,z28-2 (FljB)','Spot ID'=>316,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[272] = array('Substance'=>'183_g,m,q or g,q-2','Spot ID'=>317,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[273] = array('Substance'=>'188_m,t_g,m,t','Spot ID'=>318,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[274] = array('Substance'=>'190_g,p_g.p.s','Spot ID'=>319,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[275] = array('Substance'=>'210_Pullorum-1','Spot ID'=>320,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[276] = array('Substance'=>'213_Enteritidis-1','Spot ID'=>321,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[277] = array('Substance'=>'216_i-2','Spot ID'=>327,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[278] = array('Substance'=>'219_gp-1','Spot ID'=>328,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[279] = array('Substance'=>'223_Kottbus','Spot ID'=>329,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[280] = array('Substance'=>'224_O:61-k1','Spot ID'=>330,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[281] = array('Substance'=>'D1/D2-5','Spot ID'=>331,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[282] = array('Substance'=>'gpu-2','Spot ID'=>332,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[283] = array('Substance'=>'gpu-gp-fg-gst','Spot ID'=>333,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[284] = array('Substance'=>'fgt-gmt-mptu-mt','Spot ID'=>334,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[285] = array('Substance'=>'gp-2','Spot ID'=>335,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[286] = array('Substance'=>'gp-3','Spot ID'=>336,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[287] = array('Substance'=>'gmq-gq-5','Spot ID'=>337,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[288] = array('Substance'=>'gt-1','Spot ID'=>338,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[289] = array('Substance'=>'d-4','Spot ID'=>339,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[290] = array('Substance'=>'1,2,7-5','Spot ID'=>347,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[291] = array('Substance'=>'1,5,7','Spot ID'=>348,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[292] = array('Substance'=>'r,[i]-4','Spot ID'=>349,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[293] = array('Substance'=>'Ent-shdA','Spot ID'=>350,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[294] = array('Substance'=>'Dublin-nupC','Spot ID'=>351,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[295] = array('Substance'=>'Ent-rfbE','Spot ID'=>352,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[296] = array('Substance'=>'pepT','Spot ID'=>353,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[297] = array('Substance'=>'pepT-3','Spot ID'=>354,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[298] = array('Substance'=>'p','Spot ID'=>355,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[299] = array('Substance'=>'0,1M NaPP Standard pH 9','Spot ID'=>356,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
$template[300] = array('Substance'=>'Biotin-Marke_2,5µM','Spot ID'=>357,'Confidence'=>0,'Signal'=>0,'Valid'=>0,'Background'=>0,'Mean'=>0);
return $template;
}
    
}