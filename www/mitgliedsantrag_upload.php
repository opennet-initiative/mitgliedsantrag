<?php
/*
 * Input: data of <form> element of index.html
 * Output: - Save pdf,xfdf,json files in upload folder
 *         - send email to Mitgliederverwaltung@on-i.de
 *         - send email to user (with pdf document for signing)
 *         - show webpage with link for downloading pdf
 */

require_once("php-iban/php-iban.php");
require_once("libphp-phpmailer/class.phpmailer.php"); //should be installed via OS package. Access via php include path variable (see php.ini)

// config variables
$debug = false;
//$debug = true;
$uploadFolder = "/var/www/mitgliedsantrag_upload";
$pdfTemplateIndividual = "aufnahmeantrag.pdf";
$pdfTemplateLegalentity = "aufnahmeantrag_verein.pdf";
$fileStamp = "ONI-";
if ($_SERVER['SERVER_NAME']=='localhost') {
  //we are in developer mode because somebody typed 'localhost' in browser
  $mailto = "mitgliedsverwaltung@localhost";
} else {
  $mailto = "mitgliedsverwaltung@opennet-initiative.de";
}
$mailto_name = "Opennet Mitgliedsverwaltung";
$mailfrom = "mitgliedsverwaltung@opennet-initiative.de";
$mailfrom_name = "Opennet Mitgliedsverwaltung";
$mailsubject = "Opennet Mitgliedsverwaltung (upload): Membership Application / Mitgliedsantrag";
$mailfooter = "-- \r\nOpennet Initiative e.V.\r\nhttp://www.opennet-initiative.de\r\nMitgliedsverwaltung: http://mitgliedsantrag.opennet-initiative.de";
$pdfurl = "mitgliedsantrag_getpdf.php?";
$approveurl = "internal/mitgliedsantrag_approve.php?";
$confirmurl = "mitgliedsantrag_putpdf.php?";
$investgrant_min = 10;
$sponsorfee_min = 25;

function array2xfdf($xfdf_data, $pdf_file) {
  // Creates an XFDF File from a 2 dimensional
  // Array Format: "array ("key1" => "content1", "key2" => "content2");
  $xfdf = "<?xml version='1.0' encoding='UTF-8'>\n";
  $xfdf .= "<xfdf xmlns='http://ns.adobe.com/xfdf/' xml:space='preserve'>\n";
  $xfdf .= "<fields>\n";
  // Loop -> Array to XFDF Data
  foreach ($xfdf_data as $key => $val) {
    $xfdf .= "<field name='".$key."'>\n";
    $xfdf .= "<value>".$val."</value>\n";
    $xfdf .= "</field>\n";
  }
  // XFDF "Footer"
  $xfdf .= "</fields>";
  $xfdf .= "<f href='".$pdf_file."'/>";
  $xfdf .= "</xfdf>";
  return $xfdf;
}
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
    Willkommen. Hier kannst du deinen Mitgliedsantrag herunterladen. Bitte unterschreibe diesen und sende ihn an mitgliedsverwaltung@opennet-initiative.de.<br/>
    Welcome. You can download your membership application here. Please sign the application and send it to mitgliedsverwaltung@opennet-initiative.de.<br/>
    <h3>Mitgliedsantrag / Membership Application</h3>
    <div style="background-color:#eeeeee;padding:5px;width:650px">
    <table>

<?php
// debug only (do not show to the user in normal operation)
if ($debug) echo "<tr><td>Debugging:</td><td>Enabled</td></tr>";
// clean post
unset($_POST["submit"]);
// add metadata to post
$_POST["date"] = date("d.m.Y");
$digest = hash("crc32", implode($_POST));
$_POST["digest"] = $fileStamp." ".$digest;
// get data
$error = 0;
if ($debug) echo "Start of input validation";
echo "<p><ul>";
// get data - member
if ($_POST["member"]) {
  $opt_member = $_POST["member"];
  if ($debug) echo "<li>Member okay</li>";
} else {
  $opt_member = "";
  echo "<li>Mitglied als fehlt / Member as missing</li>";
  $error++;
}
// get data - organization
if ( $opt_member == "legalentity" ) {
  if ($_POST["organization"]) {
    $opt_organization = trim($_POST["organization"]);
    if ( strlen($opt_organization) > 0 ) {
      if ($debug) echo "<li>Organization okay</li>";
    } else {
      echo "<li>Organisation zu kurz / Organization to short</li>";
      $error++;
    }
  } else {
    $opt_organization = "";
    echo "<li>Organisation fehlt / Organization missing</li>";
    $error++;
  }
} else {
  $opt_organization = "";
  if ($debug) echo "<li>Organizaion not needed (non legal entity application), set to empty string</li>";
}
// get data - firstname
if ($_POST["firstname"]) {
  $opt_firstname = trim($_POST["firstname"]);
  if ( strlen($opt_firstname) > 0 ) {
    if ($debug) echo "<li>Firstname okay</li>";
  } else {
    echo "<li>Vorname zu kurz / Firstname to short</li>";
    $error++;
  }
} else {
  $opt_firstname = "";
  echo "<li>Vorname fehlt / Firstname missing</li>";
  $error++;
}
// get data - lastname
if ($_POST["lastname"]) {
  $opt_lastname = trim($_POST["lastname"]);
  if ( strlen($opt_lastname) > 0 ) {
    if ($debug) echo "<li>Lastname okay</li>";
  } else {
    echo "<li>Nachname zu kurz / Lastname to short</li>";
    $error++;
  }
} else {
  $opt_lastname = "";
  echo "<li>Nachname fehlt / Lastname missing</li>";
  $error++;
}
// get data - street
if ($_POST["street"]) {
  $opt_street = trim($_POST["street"]);
  if ( strlen($opt_street) > 0 ) {
    if ($debug) echo "<li>Street okay</li>";
  } else {
    echo "<li>Stra&szlig;e zu kurz / Street to short</li>";
    $error++;
  }
} else {
  $opt_street = "";
  echo "<li>Stra&szlig;e fehlt / Street missing</li>";
  $error++;
}
// get data - housenumber
if ($_POST["housenumber"]) {
  $opt_housenumber = trim($_POST["housenumber"]);
  if ( strlen($opt_housenumber) > 0 ) {
    if ($debug) echo "<li>Housenumber okay</li>";
  } else {
    echo "<li>Hausnummer zu kurz / Housenumber to short</li>";
    $error++;
  }
} else {
  $opt_housenumber = "";
  echo "<li>Hausnummer fehlt / Housenumber missing</li>";
  $error++;
}
// get data - zip
if ($_POST["zip"]) {
  $opt_zip = trim($_POST["zip"]);
  if ( strlen($opt_zip) == 5 ) {
    if ($debug) echo "<li>ZIP okay</li>";
  } else {
    echo "<li>Postleitzahl falsche Länge / ZIP code wrong length</li>";
    $error++;
  }
} else {
  $opt_zip = "";
  echo "<li>Postleitzahl fehlt / ZIP code missing</li>";
  $error++;
}
// get data - place
if ($_POST["place"]) {
  $opt_place = trim($_POST["place"]);
  if ( strlen($opt_place) > 0 ) {
    if ($debug) echo "<li>Place okay</li>";
  } else {
    echo "<li>Ort zu kurz / Place to short</li>";
    $error++;
  }
} else {
  $opt_place = "";
  echo "<li>Ort fehlt / Place missing</li>";
  $error++;
}
// get data - mail
if ($_POST["mail"]) {
  $opt_mail = trim($_POST["mail"]);
  if ( filter_var($opt_mail, FILTER_VALIDATE_EMAIL) ) {
    if ($debug) echo "<li>Mail okay</li>";
  } else {
    echo "<li>E-Mail Adresse fehlerhaft / E-Mail address incorrect</li>";
    $error++;
  }
} else {
  $opt_mail = "";
  echo "<li>E-Mail fehlt / E-Mail missing</li>";
  $error++;
}
// get data - membership
if ($_POST["membership"]) {
  $opt_membership = $_POST["membership"];
  if ( in_array($opt_membership, array("full", "active", "sponsor")) ) {
    if ($debug) echo "<li>Membership okay</li>";
  } else {
    echo "<li>Art der Mitgliedschaft fehlerhaft / Type of membership incorrect</li>";
    $error++;
  }
} else {
  $opt_membership = "";
  echo "<li>Art der Mitgliedschaft fehlt / Type of membership missing</li>";
  $error++;
}
// get data - investgrant
if ($_POST["investgrant"]) {
  $opt_investgrant = $_POST["investgrant"];
  if ( $opt_investgrant < $investgrant_min ) {
    echo "<li>Investzuschuss zu gering / Invest grant to low</li>";
    $error++;
  } else {
    if ($debug) echo "<li>Investgrant okay</li>";
  }
} else {
  $opt_investgrant = "";
  if ($debug) echo "<li>Investgrant not found, set to empty string</li>";
}
// get data - payment
if ( $opt_membership == "active" ) {
  if ($_POST["payment"]) {
    $opt_payment = $_POST["payment"];
    if ( in_array($opt_payment, array("yearly", "quarterly")) ) {
      if ($debug) echo "<li>Payment okay</li>";
    } else {
      echo "<li>Zahlungsweise fehlerhaft / Form of payment incorrect</li>";
      $error++;
    }
  } else {
    echo "<li>Zahlungsweise fehlt / Form of payment missing</li>";
    $error++;
  }
} else {
  $opt_payment = "";
  if ($debug) echo "<li>Form of payment not needed (non-active member), set to empty string</li>";
}
// get data - sponsorfee
if ( $opt_membership == "sponsor" ) {
  if ($_POST["sponsorfee"]) {
    $opt_sponsorfee = $_POST["sponsorfee"];
    if ( $opt_sponsorfee < $sponsorfee_min ) {
      echo "<li>Beitrag Förderndes Mitglied zu gering / Fee sponsor member to low</i>";
      $error++;
    } else {
      if ($debug) echo "<li>Sponsorfee okay</li>";
    }
  } else {
    $opt_sponsorfee = "";
    echo "<li>Beitrag Förderndes Mitglied fehlt / Fee sponsor member missing</li>";
    $error++;
  }
} else {
  $opt_sponsorfee = "";
  if ($debug) echo "<li>Sponsorfee not needed (non-sponsor member), set to empty string</li>";
}
// get data - accountholder
if ($_POST["accountholder"]) {
  $opt_accountholder = trim($_POST["accountholder"]);
  if ( strlen($opt_accountholder) > 0 ) {
    if ($debug) echo "<li>Account holder okay</li>";
  } else {
    echo "<li>Kontoinhaber zu kurz / Account holder to short</li>";
    $error++;
  }
} else {
  $opt_accountholder = "";
  echo "<li>Kontoinhaber fehlt / Account holder missing</li>";
  $error++;
}
// get data - iban
if ($_POST["iban"]) {
  $opt_iban = $_POST["iban"];
  if ( !verify_iban($opt_iban) || !iban_verify_checksum($opt_iban) ) {
    echo "<li>IBAN fehlerhaft / IBAN verify error</li>";
    // sugguest iban correction
    $suggestions = iban_mistranscription_suggestions($opt_iban);
    if ( count($suggestions) == 1 ) {
      echo "<li>IBAN Vorschlag / IBAN suggestion: " . $suggestions[0] . "</li>";
    }
    $error++;
  } elseif ( !iban_country_is_sepa(iban_get_country_part($opt_iban)) ) {
    echo "<li>IBAN kein SEPA Land / IBAN no SEPA country</li>";
    $error++;
  } else {
    if ($debug) echo "<li>IBAN okay</li>";
    $opt_iban = iban_to_machine_format($opt_iban);
  }
} else {
  $opt_iban = "";
  echo "<li>IBAN fehlt / IBAN missing</li>";
  $error++;
}
// get data - bank
if ($_POST["bank"]) {
  $opt_bank = trim($_POST["bank"]);
  if ( strlen($opt_bank) > 0 ) {
    if ($debug) echo "<li>Account holder okay</li>";
  } else {
    echo "<li>Bank zu kurz / Bank to short</li>";
    $error++;
  }
} else {
  $opt_bank = "";
  echo "<li>Bank fehlt / Bank missing</li>";
  $error++;
}
// get data - opennetid
if ($_POST["opennetid"]) {
  $opt_opennetid = trim($_POST["opennetid"]);
} else {
  $opt_opennetid = "";
  if ($debug) echo "<li>Opennetid not found, set to empty string</li>";
}
// get data - mailconfirm
if ($_POST["mailconfirm"]) {
  $opt_mailconfirm = 1;
} else {
  $opt_mailconfirm = "";
  if ($debug) echo "<li>Mailconfirm not found, set to empty string</li>";
}
echo "</ul></p>";
if ($debug) echo "End of input validation";
// inform user
if ($debug)
{
  echo "<tr><td>Mitglied als / Member as:</td><td>" . $opt_member . "</td></tr>";
  echo "<tr><td>Organisation / Organization:</td><td>" . $opt_organization . "</td></tr>";
  echo "<tr><td>Vorname / First Name:</td><td>" . $opt_firstname . "</td></tr>";
  echo "<tr><td>Nachname / Last Name :</td><td>" . $opt_lastname . "</td></tr>";
  echo "<tr><td>Stra&szlig;e / Street:</td><td>" . $opt_street . "</td></tr>";
  echo "<tr><td>Hausnr. / House Num.:</td><td>" . $opt_housenumber . "</td></tr>";
  echo "<tr><td>PLZ / ZIP:</td><td>" . $opt_zip . "</td></tr>";
  echo "<tr><td>Ort / Place:</td><td>" . $opt_place . "</td></tr>";
  echo "<tr><td>E-Mail:</td><td>" . $opt_mail . "</td></tr>";
  echo "<tr><td>Art der Mitgliedschaft / Type of membership:</td><td>" . $opt_membership . "</td></tr>";
  echo "<tr><td>Investzuschuss / Invest grant:</td><td>" . $opt_investgrant . "</td></tr>";
  echo "<tr><td>Zahlungsweise / Form of payment:</td><td>" . $opt_payment . "</td></tr>";
  echo "<tr><td>Beitrag pro Jahr / Fee per year:</td><td>" . $opt_sponsorfee . "</td></tr>";
  echo "<tr><td>Kontoinhaber / Account holder:</td><td>" . $opt_accountholder . "</td></tr>";
  echo "<tr><td>IBAN:</td><td>" . $opt_iban . "</td></tr>";
  echo "<tr><td>Bank:</td><td>" . $opt_bank . "</td></tr>";
  echo "<tr><td>Opennet ID:</td><td>" . $opt_opennetid . "</td></tr>";
  echo "<tr><td>Mailconfirm:</td><td>" . $opt_mailconfirm . "</td></tr>";
}
if ($error > 0) {
  echo "<p><strong>Fehler</strong>: Fehlerhafte Eingabe im Antrag, bitte deine Angaben pr&uuml;fen.<br/><strong>Error</strong>: Invalid input in application, please review your data.</p>";
  echo "<p><a href=\"#\" onclick=\"history.back();\">Eingabe pr&uuml;fen / Review input</a></p>";
} else {
  // prepare metadata
  echo "<p>Dein Mitgliedsantrag ist angekommen. Your membership application arrived.</p>";
  $timestamp = time();
  $json = array(
    "meta_type"=>"Opennet_Mitgliedsantrag_JSON_v1", "meta_created"=>$timestamp,
    "firstname"=>$opt_firstname, "lastname"=>$opt_lastname, "street"=>$opt_street,
    "housenumber"=>$opt_housenumber, "zip"=>$opt_zip, "place"=>$opt_place,
    "mail"=>$opt_mail, "membership"=>$opt_membership,
    "investgrant"=>$opt_investgrant, "payment"=>$opt_payment,
    "sponsorfee"=>$opt_sponsorfee, "accountholder"=>$opt_accountholder,
    "iban"=>$opt_iban, "bank"=>$opt_bank, "opennetid"=>$opt_opennetid,
    "mailconfirm"=>$opt_mailconfirm,
    "digest"=>$digest, "upload_timestamp"=>$timestamp,
    "status"=>"Upload", "approve_message"=>"", "approve_timestamp"=>"",
    "error_message"=>"", "error_timestamp"=>""
  );
  // prepare file
  if ( $_POST["member"]  == "legalentity" ) {
    $templatefile = $pdfTemplateLegalentity;
    $membername = $_POST["organization"];
  } else {
    $templatefile = $pdfTemplateIndividual;
    $membername = $_POST["firstname"].$_POST["lastname"];
  }
  // cleanup old (>48h) generated file
  $files = glob("$uploadFolder/$fileStamp*");
  $now = time();
  foreach ($files as $file) {
    if (is_file($file)) {
      if ($now - filemtime($file) >= 60 * 60 * 24 * 2) { // 2 days
        unlink($file);
      }
    }
  }
  // prevent DDoS (massive file writing). If there are more then 100 files do not write more files.
  $files = glob("$uploadFolder/*");
  if ( sizeof($files) > 100 ) {
    echo "\n\nFehler (5) beim Schreiben der Datei: Bitte berichte dieses Problem an $mailto";
    exit;
  }
  // store metadata as json
  umask(0002);
  $filename_short = filter_var($membername."_".$digest,FILTER_SANITIZE_URL);
  $filename = $fileStamp . "_" . $filename_short;
  $ret = file_put_contents($uploadFolder . "/" . $filename . ".json", str_replace('\n', '', json_encode($json)));
  if ($ret == FALSE) { //problems when writing file? e.g. disc full or no access rights?
    echo "\n\nFehler (4) beim Schreiben der Datei: Bitte berichte dieses Problem an $mailto";
    exit;
  }
  if ($debug) echo "<tr><td>JSON:</td><td>done, " . $filename . ".json</td></tr>";
  echo "<p><strong>Erfolgreich</strong>: Gespeichert als " . $digest . "<br/><strong>Success</strong>: Stored as " . $digest . "</p>";
  // store fdf
  $fdf = array2xfdf($_POST, $templatefile);
  $fdffile = $uploadFolder . "/" . $filename . ".xfdf";
  file_put_contents($fdffile, $fdf);
  if ($debug) echo "<tr><td>FDF:</td><td>done, " . $filename . ".xdfd</td></tr>";
  // store pdf
  $pdffile = $uploadFolder . "/" . $filename . ".pdf";
  exec("pdftk ".$templatefile." fill_form ".$fdffile." output ".$pdffile." flatten");
  if ($debug) echo "<tr><td>PDF:</td><td>done, " . $filename . ".pdf</td></tr>";
  // send mail to Mitgliederverwaltung team
  $mailtext = "A new membership application arrived.\r\n".
    "Eine neuer Mitgliedsantrag ist eingetroffen.\r\n\r\n".
    "memberName: " . $membername . "\r\n".
    "digest: " . $digest . "\r\n\r\n".
    //"approve: <" . $approveurl . $filename_short . ">\r\n\r\n". //TOIMPLEMENT
    json_encode($json, JSON_PRETTY_PRINT) .
    "\n\n".$mailfooter;
  $phpmailer = new PHPMailer();
  $phpmailer->setFrom($mailfrom, $mailfrom_name);
  $phpmailer->Subject=$mailsubject." $digest";
  $phpmailer->Body=$mailtext;
  $phpmailer->addAddress($mailto, $mailto_name);
  if (!$phpmailer->send()) {
    echo "<p><strong>Fehler / Error</strong> (1): Mailversand nicht erfolgreich / Sending mail not successfull</p>";
  } else {
    if ($debug) echo "<tr><td>Approval mail:</td><td>done, " . $mailto . "</td></tr>";
  }
  // show download link
  echo "<p><a href=\"" . $pdfurl . $filename_short . "\">Mitgliedsantrag herunterladen / Download membership application</a></p>";
  if ($debug) echo "<tr><td>Download link:</td><td>done, " . $filename_short . "</td></tr>";
  // send mail to applicant
  if ($opt_mail) {
    $mailtext = "Your membership application arrived.\r\nDein Mitgliedsantrag ist eingetroffen.\r\n\r\n"
      . "memberName: " . $membername . "\r\ndigest: " . $digest . "\r\n\r\n"
      . "Bitte unterschreibe deinen Mitgliedsantrag (siehe Anhang) und sende ihn an mitgliedsverwaltung@opennet-initiative.de.\n"
      . "Please sign the membership application (see attachement) and send it to mitgliedsverwaltung@opennet-initiative.de.\r\n\r\n"
      . $mailfooter;
    $phpmailer = new PHPMailer();
    $phpmailer->setFrom($mailfrom, $mailfrom_name);
    $phpmailer->Subject=$mailsubject;
    $phpmailer->Body=$mailtext;
    $phpmailer->addAddress($opt_mail);
    if ($opt_mailconfirm) $phpmailer->addAttachment($pdffile);
    if (!$phpmailer->send()) {
      echo "<p><strong>Fehler / Error</strong> (2): Mailversand nicht erfolgreich / Sending mail not successfull</p>";
    } else {
      if ($debug) echo "<tr><td>Confirmation mail:</td><td>done, " . $opt_mail . ", attachment " . $opt_mailconfirm . "</td></tr>";
    }
  } else {
    if ($debug) echo "<tr><td>Confirmation mail:</td><td>not sent, no mail address provided</td></tr>";
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
