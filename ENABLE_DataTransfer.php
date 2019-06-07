<?php
/*
Author: Jim Delmonico
Date: 3/14/19
Notes: This pulls data from Practice Cluster Personnel and imports it to a second project
	
*/
	
//Pull the information from the URCC18110CD ENABLE - Practice Clusters Evaluation project
$data = array(
    'token' => 'D740421E3B139C7BC9E122A0800C7E72',
    'content' => 'record',
    'format' => 'json',
    'type' => 'flat',
    'fields' => array('personnel_ctepid','personnel_email','personnel_name','personnel_role'),
    'forms' => array('practice_cluster_personnel'),
    'rawOrLabel' => 'raw',
    'rawOrLabelHeaders' => 'raw',
    'exportCheckboxLabel' => 'false',
    'exportSurveyFields' => 'false',
    'exportDataAccessGroups' => 'false',
    'returnFormat' => 'json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://redcap.urmc.rochester.edu/redcap/api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
$jsonExport = curl_exec($ch);

//Print the original export format
echo("This is the original export data:");
echo("<hr />");
print_r($jsonExport);
echo("<br>");
 

//Decode original json data export into a modifiable array
$array = json_decode($jsonExport, true);

$cleaned_array = array(); //Intializes a new array to hold the "sanitized data"
$count = 0; //Counter for placing validated items into the new array

for ($i = 0; $i < count($array) ; $i++){
	$sub_array = $array[$i];
	if (trim($sub_array['personnel_name']) != ""){ // Using the personnel_name as the validation variable since I am assuming entries will always have this.
		$sub_array += array('personnel_id' => $count);
		unset($sub_array['personnel_phone']);
	$cleaned_array[$count] = $sub_array;
	$count ++;
	}
}

//Print modified format
echo("<br>");
echo("This is the modified export data(array format):");
echo("<hr />");
print_r($cleaned_array);
echo("<br>");


//Re-encode array into json for REDCap import
$jsonImport = json_encode($cleaned_array);

//Print final import format
echo("<br>");
echo("This is the modified export data (to be uploaded):");
echo("<hr />");
print_r($jsonImport);
echo("<br>");
 

//Set import to json data
$fields = array(
	'token'   => '149D6D3C4DE79CD7078D7628F91C1BB3',
	'content' => 'record',
	'format'  => 'json',
	'type'    => 'flat',
	'overwriteBehavior' => 'normal',
	'forceAutoNumber' => 'true',
	'data'    => $jsonImport,
);

$curl_log = fopen("curlverbose.txt", 'w');	
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://redcap.urmc.rochester.edu/redcap/api/');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields, '', '&'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_STDERR, $curl_log);
$output = curl_exec($ch);

//Check for import errors
if($errno = curl_errno($ch)) {
	$error_message = curl_strerror($errno);
	echo "cURL error ({$errno}):\n {$error_message}";
}

$file = 'curlverbose.txt';
$orig = file_get_contents($file);
$a = htmlentities($orig);

echo '<code>';
echo '<pre>';

echo $a;

echo '</pre>';
echo '</code>';

$result = json_decode($output, TRUE); // parse output string to array
echo '<pre>';print_r($result);echo '<pre';

curl_close($ch);


/* This Section was for Testing IGNORE
$data = array(
    'token' => '149D6D3C4DE79CD7078D7628F91C1BB3',
    'content' => 'record',
    'format' => 'json',
    'type' => 'flat',
    'fields' => '',
    'forms' => array('practice_cluster_personnel'),
    'rawOrLabel' => 'raw',
    'rawOrLabelHeaders' => 'raw',
    'exportCheckboxLabel' => 'false',
    'exportSurveyFields' => 'false',
    'exportDataAccessGroups' => 'false',
    'returnFormat' => 'json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://redcap.urmc.rochester.edu/redcap/api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
$jsonExport = curl_exec($ch);

print_r($jsonExport);

*/

?>