<?php
/****  YOU MUST BE CONFIGURED ******/

$DBSERVER="LOCALHOST";
$tbl = "TABLE";
$db_uname = 'USERBBDD';
$db_passwd = 'USERPWD';
$db = 'DATABASE';
$conn = mysql_pconnect($DBSERVER,$db_uname, $db_passwd);

// Path to dbase file
$db_path = "DBFILE.dbf";

/****  YOU MUST BE CONFIGURED ******/

// Open dbase file
$dbh = dbase_open($db_path, 0)
or die("Error! Could not open dbase database file '$db_path'.");

// Get column information
$column_info = dbase_get_header_info($dbh);

// Display information
//print_r($column_info);

$line = array();

foreach($column_info as $col)
{
switch($col['type'])
{
case 'character':
$line[]= "`$col[name]` VARCHAR( $col[length] )";
break;
case 'number':
$line[]= "`$col[name]` FLOAT";
break;
case 'boolean':
$line[]= "`$col[name]` BOOL";
break;
case 'date':
$line[]= "`$col[name]` DATE";
break;
case 'memo':
$line[]= "`$col[name]` TEXT";
break;
}
}
$str = implode(",",$line);

#Conectamos BBDD
mysql_select_db($db,$conn);

#Borramos Tabla primero
$sql = "drop TABLE `$tbl`";
mysql_query($sql,$conn);

#Creamos Tabla
$sql = "CREATE TABLE `$tbl` ( $str ) Engine=MyISAM;";
mysql_query($sql,$conn);

set_time_limit(0); // I added unlimited time limit here, because the records I imported were in the hundreds of thousands.

// This is part 2 of the code

import_dbf($db, $tbl, $db_path);

function import_dbf($db, $table, $dbf_file)
{
global $conn;
if (!$dbf = dbase_open ($dbf_file, 0)){ die("Could not open $dbf_file for import."); }
$num_rec = dbase_numrecords($dbf);
$num_fields = dbase_numfields($dbf);
$fields = array();

for ($i=1; $i<=$num_rec; $i++){
$row = @dbase_get_record_with_names($dbf,$i);
$q = "insert into $db.$table values (";
foreach ($row as $key => $val){
if ($key == 'deleted'){ continue; }
$q .= "'" . addslashes(trim($val)) . "',"; // Code modified to trim out whitespaces
}
if (isset($extra_col_val)){ $q .= "'$extra_col_val',"; }
$q = substr($q, 0, -1);
$q .= ')';
//if the query failed - go ahead and print a bunch of debug info
if (!$result = mysql_query($q, $conn)){
print (mysql_error() . " SQL: $q
\n");
print (substr_count($q, ',') + 1) . " Fields total.

";
$problem_q = explode(',', $q);
$q1 = "desc $db.$table";
$result1 = mysql_query($q1, $conn);
$columns = array();
$i = 1;
while ($row1 = mysql_fetch_assoc($result1)){
$columns[$i] = $row1['Field'];
$i++;
}
$i = 1;
foreach ($problem_q as $pq){
print "$i column: {$columns[$i]} data: $pq
\n";
$i++;
}
die();
}
}
}


?>