<?php
/*
 * download mitgliedsantrag pdf file
 * input: string with username and digest
 * output: pdf document or error message
 */
 
$uploadFolder = "/var/www/mitgliedsantrag_upload";
$pdfStamp = "ONI-";
// get input
$filename = basename(filter_var($_SERVER["QUERY_STRING"], FILTER_SANITIZE_STRING));
// read fdf
$pdffile = $uploadFolder."/".$pdfStamp."_".$filename.".pdf";

// check pdf
if ( file_exists($pdffile) && is_file($pdffile) )
{
  if ( time() - filemtime($pdffile) < 3600 )
  {
    // return pdf (within 1 hour timeframe)
    header("Content-type: application/pdf");
    header("Content-Disposition: attachment; filename=\"".$pdfStamp."_".$filename.".pdf\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($pdffile));
    readfile($pdffile);
  }
  else
  {
    // return error (not within 1 hour timeframe)
    echo "Error (1)";
  }
}
else
{
  // return error (file not found)
  echo "Error (2) test";
}
?>
