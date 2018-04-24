<!DOCTYPE html>
<html lang="en">
<title>SGSA-Salmonella GenoSerotyping Array</title>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<style>
.progress { position:relative; width:400px; border: 1px solid #ddd; padding: 1px; border-radius: 3px; }
.bar { background-color: #B4F5B4; width:0%; height:20px; border-radius: 3px; }
.percent { position:absolute; display:inline-block; top:3px; left:48%; }
</style>
</head>
<body>
  <div class="container">
    <h1>
        <strong>SGSA v. 2.0</strong>
        <strong><font size="3" color="#428BCA"><br/><i>Salmonella </i>GenoSerotyping Array Classifier<br/></font></strong>
    </h1>
<br/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

    

    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="raw_data">
        <br/>
        <p class="lead"><font size=2.5 color="#848484">*Select one or more output files produced by the array reader for upload</font>
        <br/>
        <form id="data" role="form" action="upload.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="inputFile">Please select your input file: </label>
            <input type="file" id="inputFiles" name="inputFiles[]" multiple="multiple" required>
          </div>
          <input type="submit" value="Analyze Results" name="submit" onClick="clearform();" >
	</form>
      </div>

    </div>

<br/>


    <p class="text-center">Copyright 2015 @ National Microbiology Laboratory at Guelph.</p>
</p>
  </div>
  



</body>

<script>
function clearform()
{
    document.forms["data"].submit();
    document.getElementById("inputFiles").value="";
}
</script>
</html>