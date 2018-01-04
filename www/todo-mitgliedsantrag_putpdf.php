<?php

echo "not yet implemented";
exit;

/*
 * Implement the below later on when needed.
 */
require_once("/usr/share/php/libphp-phpmailer/class.phpmailer.php");
// config variables
//$debug = false;
$debug = true;
$allowedExts = array("pdf");
$allowedType = array("application/pdf");
$allowedFilesize = 20971520;
$uploadFolder = "/var/www/mitgliedsantrag_upload";
$fileStamp = "ONI-TEST";
$mailto = "mathias@opennet-initiative.de";
$mailto_name = "Opennet Mitgliedsverwaltung";
$mailfrom = "mitgliedsverwaltung@opennet-initiative.de";
$mailfrom_name = "Opennet Mitgliedsverwaltung";
$mailsubject = "Opennet Mitgliedsverwaltung (upload): Membership Application / Mitgliedsantrag";
$mailfooter = "-- \r\nOpennet Initiative e.V.\r\nhttp://www.opennet-initiative.de\r\nMitgliedsverwaltung: http://mitgliedsantrag.opennet-initiative.de";
$pdfurl = "http://mitgliedsantrag.opennet-initiative.de/mitgliedsantrag_getpdf.php?";
$approveurl = "https://mitgliedsantrag.opennet-initiative.de/internal/mitgliedsantrag_approve.php?";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>Opennet Mitgliedsantrag - Opennet Initiative e.V.</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  </head>
  <body>
    <h2>Opennet Initiative e.V.</h2>
    Willkommen. Hier kannst du deinen Mitgliedsantrag erstellen und absenden.<br/>
    Welcome. You can create and send your membership application here.
    <h3>Mitgliedsantrag / Membership Application</h3>
    <div style="background-color:#eeeeee;padding:5px;width:650px">
    <table>

<?php
// debug only (do not show to the user in normal operation)
if ($debug) echo "<tr><td>Debugging:</td><td>Enabled</td></tr>";
// get file data
$name = $_FILES["file"]["name"];
$type = $_FILES["file"]["type"];
$size = $_FILES["file"]["size"];
$store = $_FILES["file"]["tmp_name"];
$error = $_FILES["file"]["error"];
// prepare variables
$extension = end(explode(".", $name));
// inform user
echo "<tr><td>Datei / File:</td><td>" . $name . "</td></tr>";
// debug only (do not show to the user in normal operation)
if ($debug) {
  echo "<tr><td>Type:</td><td>" . $type . "</td></tr>";
  echo "<tr><td>Extension:</td><td>" . $extension . "</td></tr>";
  echo "<tr><td>Size:</td><td>" . $size . " Byte</td></tr>";
  echo "<tr><td>Temp stored:</td><td>" . $store . "</td></tr>";
}
// process file
if (in_array($extension, $allowedExts)
  && in_array($type, $allowedType)
  && $size < $allowedFilesize)
{
  // check for errors
  if ($error > 0)
  {
    echo "<p><b>Fehler / Error</b>: ID" . $error. "</p>";
  }

}
?>

    </table>
    </div>
    <p>
    Zur&uuml;ck zu / Back to: <a href="/">Opennet Mitgliedsantrag</a>.
    </p>
    <p>
    <img src="/Opennet_logo_quer.gif">
    </p>
  </body>
</html>
