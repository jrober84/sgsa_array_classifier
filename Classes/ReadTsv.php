<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ReadTSV
{
var $fileName;
var $fileHandle;
var $header;
var $count_rows;
var $line;

function __construct($fileName) 
{
    $this->setfileName($fileName);
    $this->openFile();
    $this->setHeader($this->getNextLine());
}


function setfileName($filename)
{
    $this->fileName = $filename;
}

function getfileName()
{
    return $this->fileName;
}


function setHeader($header_line)
{
    $this->header = explode("\t",rtrim($header_line));
}
function getHeader()
{
    return $this->header;
}
function getHeaderByCol($col_num)
{
    if(array_key_exists($col_num, $this->header))
    {
        return $this->header[$col_num];
    }
    else{
        return "";
    }
}

function openFile()
{
    $file = $this->getfileName();
    if(!isset($file))
    {
        echo "Error, the tsv file name does not appear to be set. Cannot continue\n";
        die;
    }
    if(!file_exists($file))
    {
        echo "Error, the tsv $file file does not exist. Cannot continue\n";
        die;        
    }
    if(!is_readable($file))
    {
        echo "Error, the tsv $file file cannot be read, check permissions to file. Cannot continue\n";
        die;        
    }
    $this->fileHandle = fopen($file, 'r');
    if(!$this->fileHandle)
    {
        echo "Unknown Error, the file could not be opened for reading. Cannot continue\n";
        die;           
    }
}

function getNextLine()
{
    if(!feof($this->fileHandle))
    {
        return fgets($this->fileHandle);
        
        $this->count_rows++;
    }
    else{
        fclose($this->fileHandle);
        return "";
    }
}

function getRowCount()
{
    return $this->count_rows;
}
    
    
}


?>
