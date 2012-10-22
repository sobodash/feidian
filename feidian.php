#!/usr/bin/php -q
<?php
/*

FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter

A tool for all kinds of tasks involving bitplane to bitmap graphic conversion.

Version:   0.90b
Author:    Derrick Sobodash <derrick@sobodash.com>
Copyright: (c) 2003, 2004, 2012 Derrick Sobodash
Web site:  https://github.com/sobodash/feidian/
License:   BSD License <http://opensource.org/licenses/bsd-license.php>

*/

error_reporting (E_WARNING | E_PARSE);
include("include/subs.php");
include("settings.php");

echo ("FEIDIAN 0.90b (cli)\nCopyright (c) 2003, 2004, 2012 Derrick Sobodash\n");
set_time_limit(6000000);

if ($argc < 5) { DisplayOptions(); die; }
else { 
  if($argv[2] == "i"){ $mode = $argv[1]; $command = $argv[3]; $in_file = $argv[4]; $out_file = $argv[5]; $invert = 1; }
  else { $mode = $argv[1]; $command = $argv[2]; $in_file = $argv[3]; $out_file = $argv[4]; $invert = 0; }
}

if ($mode == "-r") {
  list($width, $height, $columns, $rows, $start) = split(",", $command);
  // Make sure the command string isn't missing anything
  if($width==''||$height==''||$columns==''||$rows==''||$start=='') die(print "ERROR: Your command string is incomplete!\n");
  // Check width/height/columns/rows
  if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
  if(!is_numeric($width)||!is_numeric($height)||!is_numeric($columns)||!is_numeric($rows)||!is_numeric($start)) die(print "ERROR: Your width, height, columns, or rows is a non-integer value!\n");
  // Make sure the input file exists
  if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
  $out_file = str_replace(".bmp", "", $out_file);
  if ($invert==1) {
    include("include/anywidth.php");
    bit2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file, 1);
  }
  else {
    include("include/anywidth.php");
    bit2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file, 0);
  }
}
elseif ($mode == "-i") {
  list($width, $height, $columns, $rows, $start) = split(",", $command);
  // Make sure the command string isn't missing anything
  if($width==''||$height==''||$columns==''||$rows==''||$start=='') die(print "ERROR: Your command string is incomplete!\n");
  // Check width/height/columns/rows
  if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
  if(!is_numeric($width)||!is_numeric($height)||!is_numeric($columns)||!is_numeric($rows)||!is_numeric($start)) die(print "ERROR: Your width, height, columns, or rows is a non-integer value!\n");
  // Make sure the input bitmap exists
  if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
  $out_file = str_replace(".bmp", "", $out_file);
  if ($invert==1) {
    include("include/anywidth.php");
    bit2tile($rows, $columns, $height, $width, $start, $in_file, $out_file, 1);
  }
  else {
    include("include/anywidth.php");
    bit2tile($rows, $columns, $height, $width, $start, $in_file, $out_file, 0);
  }
}
elseif ($mode == "-cr") {
  if($argc > 5) {
    if($argv[2] != "i") $tlp_pal = $argv[5];
    else $tlp_pal = $argv[6];
  }
  list($tiledef, $columns, $rows, $start) = split(",", $command);
  // Make sure the command string isn't missing anything
  if($tiledef==''||$columns==''||$rows==''||$start=='') die(print "ERROR: Your command string is incomplete!\n");
  // Make sure columns/rows/offset are an integer
  if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
  if(!is_numeric($columns)||!is_numeric($rows)||!is_numeric($start)) die(print "ERROR: Your columns, rows, or offeset are a non-integer value!\n");
  // Make sure the input file exists
  if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
  if(!file_exists($out_file)) print "$out_file does not exist. It will be created.\n";
  if($tlp_pal) {
    if(!file_exists($tlp_pal)) die(print "ERROR: The palette you specified doesn't exist!\n");
  }
  else $tlp_pal = 0;
  include("include/customrip.php");
  if ($invert==1) cust2bmp($rows, $columns, $tiledef, $start, $in_file, $out_file, 1, $tlp_pal);
  else cust2bmp($rows, $columns, $tiledef, $start, $in_file, $out_file, 0, $tlp_pal);
}
elseif ($mode == "-ci") {
  list($tiledef, $columns, $rows, $start) = split(",", $command);
  // Make sure the command string isn't missing anything
  if($tiledef==''||$columns==''||$rows==''||$start=='') die(print "ERROR: Your command string is incomplete!\n");
  // Make sure columns/rows/offset are an integer
  if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
  if(!is_numeric($columns)||!is_numeric($rows)||!is_numeric($start)) die(print "ERROR: Your columns, rows, or offeset are a non-integer value!\n");
  // Make sure the input bitmap exists
  if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
  if(!file_exists($out_file)) print "$out_file does not exist. It will be created.\n";
  include("include/custominj.php");
  if ($invert==1) bmp2cust($rows, $columns, $tiledef, $start, $in_file, $out_file, 1);
  else bmp2cust($rows, $columns, $tiledef, $start, $in_file, $out_file, 0);
}
elseif ($mode == "-d") {
  list($width, $height, $tile_list) = split(",", $command);
  // Make sure the command string isn't missing anything
  if($width==''||$height==''||$tile_list=='') die(print "ERROR: Your command string is incomplete!\n");
  // Test to make sure width and height are numeric
  if(!is_numeric($width)||!is_numeric($height)) die(print "ERROR: Your width or height consist of a non-integer value!\n");
  // Make sure the tile list exists
  if(!file_exists($tile_list)) die(print "ERROR: $tile_list does not exist!\n");
  // Make sure the input bitmap exists
  if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
  include("include/dualtile.php");
  bit2tile($height, $width, $tile_list, $in_file, $out_file);
}
elseif ($mode == "-s") {
  list($width, $height) = split(",", $command);
  // Make sure the command string isn't missing anything
  if($width==''||$height=='') die(print "ERROR: Your command string is incomplete!\n");
  // Test to make sure width and height are numeric
  if(!is_numeric($width)||!is_numeric($height)) die(print "ERROR: Your width or height consist of a non-integer value!\n");
  // Make sure the input bitmap exists
  if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
  include("include/scale.php");
  if ($invert==1) scale($height, $width, $in_file, $out_file, 1);
  else scale($height, $width, $in_file, $out_file, 0);
}
elseif ($mode == "-wf") {
  list($width, $height, $format, $vwf, $spacing, $descent) = split(",", $command);
  // Test all input, yeah, I'm too lazy to add a comment for everything now :P
  if ($width==''||$height==''||$format==''||$vwf==''||$spacing==''||$descent=='') die(print "ERROR: Your command string is incomplete!\n");
  if(!is_numeric($width)||!is_numeric($height)) die(print "ERROR: Your width or height is a non-integer value!\n");
  if($format!='fd'&&$format!='bdf') die(print "ERROR: You must specify FD or BDF format!\n");
  if($vwf=='v'||$vwf=='f') die(print "ERROR: You must specify (v)ariable or (f)ixed width!\n");
  if(!is_numeric($spacing)) die(print "ERROR: You must specify a spacing value!\n");
  if(!is_numeric($descent)) die(print "ERROR: You must specify a descent value!\n");
  include("include/fontfile.php");
  if ($format=='bdf') makebdf($width, $height, $vwf, $spacing, $descent, $in_file, $out_file);
  else makefd($width, $height, $vwf, $spacing, $descent, $in_file, $out_file);
}
elseif ($mode == "-p") {
  list($width, $height, $pad_width, $pad_height) = split(",", $command);
  // Test all input, yeah, I'm too lazy to add a comment for everything now :P
  if ($width==''||$height==''||$pad_width==''||$pad_height=='') die(print "ERROR: Your command string is incomplete!\n");
  if(!is_numeric($width)||!is_numeric($height)||!is_numeric($pad_width)||!is_numeric($pad_height)) die(print "ERROR: Your width or height is a non-integer value!\n");
  include("include/pad.php");
  padtile($width, $height, $pad_width, $pad_height, $in_file, $out_file);
}
elseif ($mode == "-b") {
  list($width, $height, $source_file, $text_rep) = split(",", $command);
  // Test all input, yeah, I'm too lazy to add a comment for everything now :P
  if ($width==''||$height==''||$source_file==''||$text_rep=='') die(print "ERROR: Your command string is incomplete!\n");
  if(!is_numeric($width)||!is_numeric($height)||!file_exists($source_file)||!file_exists($text_rep)) die(print "ERROR: Your width or height is a non-integer value!\n");
  include("include/bcr.php");
  bcrtile($width, $height, $source_file, $text_rep, $in_file, $out_file);
}
else die(print "ERROR: You did not specify a valid mode!\n");

function DisplayOptions() {
  echo <<<OPTIONS
FEIDIAN is a generic converter for bitplane and bitmap images. It can work with
any tile size (x,y does not matter) and custom tile definitions up to
16 colors. These can be created yourself or downloaded from the FEIDIAN website
(http://feidian.sourceforge.net/). Conversions are done between bitplane tiles
and bitmap linear graphics (suitable for Paintbrush or Photoshop).

There are many other features supported to aid in game translation and fonr
development. For more information, review the readme.txt included with this
program. For a brief overview of commands, check commands.txt.

Syntax:  feidian.php -[mode] [string] [input] [output]

OPTIONS;
}

?>
