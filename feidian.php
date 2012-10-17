#!/usr/bin/php -q
<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003,2004 Derrick Sobodash
    Version: 0.5
    Web    : https://github.com/sobodash/feidian
    E-mail : derrick@sobodash.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program (license.txt); if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

include("include/subs.php");

echo ("\nFEIDIAN\nV0.5 Copyright (C) 2003, 2004 Derrick Sobodash\n");
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
	if ($width%8==0 && $invert==0) {
		include("include/8width.php");
		tile2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file);
	}
	else if ($width%8==0 && $invert==1) {
		include("include/anywidth.php");
		bit2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file, 1);
	}
	else {
		if ($invert==1) {
			include("include/anywidth.php");
			bit2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file, 1);
		}
		else {
			include("include/anywidth.php");
			bit2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file, 0);
		}
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
	if ($width%8==0 && $invert==0) {
		include("include/8width.php");
		bmp2tile($rows, $columns, $height, $width, $start, $in_file, $out_file);
	}
	else if ($width%8==0 && $invert==1) {
		include("include/anywidth.php");
		bit2tile($rows, $columns, $height, $width, $start, $in_file, $out_file, 1);
	}
	else {
		if ($invert==1) {
			include("include/anywidth.php");
			bit2tile($rows, $columns, $height, $width, $start, $in_file, $out_file, 1);
		}
		else {
			include("include/anywidth.php");
			bit2tile($rows, $columns, $height, $width, $start, $in_file, $out_file, 0);
		}
	}
}
elseif ($mode == "-cr") {
	list($tiledef, $columns, $rows, $start) = split(",", $command);
	// Make sure the command string isn't missing anything
	if($tiledef==''||$columns==''||$rows==''||$start=='') die(print "ERROR: Your command string is incomplete!\n");
	// Make sure columns/rows/offset are an integer
	if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
	if(!is_numeric($columns)||!is_numeric($rows)||!is_numeric($start)) die(print "ERROR: Your columns, rows, or offeset are a non-integer value!\n");
	// Make sure the input file exists
	if(!file_exists($in_file)) die(print "ERROR: $in_file does not exist!\n");
	if(!file_exists($out_file)) print "$out_file does not exist. It will be created.\n";
	include("include/customtile.php");
	if ($invert==1) cust2bmp($rows, $columns, $tiledef, $start, $in_file, $out_file, 1);
	else cust2bmp($rows, $columns, $tiledef, $start, $in_file, $out_file, 0);
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
	include("include/customtile.php");
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
	if(!is_numeric($spacing)) die(print "ERROR: You must specify a descent value!\n");
	include("include/fontfile.php");
	if ($format=='bdf') makebdf($width, $height, $vwf, $spacing, $descent, $in_file, $out_file);
	else makefd($width, $height, $vwf, $spacing, $descent, $in_file, $out_file);
}
else die(print "ERROR: You did not specify a valid mode!\n");

function DisplayOptions() {
	echo ("\nFEIDIAN is a generic converter for monochrome bitplane and bitmap images. It\ncan work with any tile size (x,y does not matter) as well as custom tile\ndefinitions you can download or create yourself. Conversions are done between\nbitplane tiles and bitmap line graphics (suitable for Paintbrush or Photoshop).\n\nIt is also capable for creating dual-letter full width tile sets using a\nlist of two-letter combos and a half-width, bitmap font. This is useful if you\nare hacking a game with a large quantity of tiles but on a system where\nassembler modifications aren't an option (or if it's simply beyond your skill\nto do such).\n\nFor more information, review the readme.txt included with this program. For a\nbrief overview of commands, check commands.txt.\n\nSyntax:  feidian.php -[mode] [string] [input] [output]\n\n");
}

?>
