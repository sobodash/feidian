#!/usr/bin/php -q
<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003 Derrick Sobodash
    Version: 0.3
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

echo ("\nFEIDIAN\nV0.3 Copyright (C) 2003 Derrick Sobodash\n");
set_time_limit(6000000);

if ($argc < 5) { DisplayOptions(); die; }
else { $mode = $argv[1]; $command = $argv[2]; $in_file = $argv[3]; $out_file = $argv[4]; }

if ($mode == "-r") {
	list($width, $height, $columns, $rows, $start) = split(",", $command);
	if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
	if ($width%8==0) {
		include("include/8width.php");
		tile2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file);
	}
	else {
		include("include/anywidth.php");
		bit2bmp($rows, $columns, $height, $width, $start, $in_file, $out_file);
	}
}
elseif ($mode == "-i") {
	list($width, $height, $columns, $rows, $start) = split(",", $command);
	if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
	if ($width%8==0) {
		include("include/8width.php");
		bmp2tile($rows, $columns, $height, $width, $start, $in_file, $out_file);
	}
	else {
		include("include/anywidth.php");
		bit2tile($rows, $columns, $height, $width, $start, $in_file, $out_file);
	}
}
elseif ($mode == "-cr") {
	list($tiledef, $columns, $rows, $start) = split(",", $command);
	if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
	include("include/customtile.php");
	cust2bmp($rows, $columns, $tiledef, $start, $in_file, $out_file);
}
elseif ($mode == "-ci") {
	list($tiledef, $columns, $rows, $start) = split(",", $command);
	if (substr($start, 0, 2) == "0x") $start = hexdec(substr($start, 2));
	include("include/customtile.php");
	bmp2cust($rows, $columns, $tiledef, $start, $in_file, $out_file);
}
elseif ($mode == "-d") {
	list($width, $height, $tile_list) = split(",", $command);
	include("include/dualtile.php");
	bit2tile($height, $width, $tile_list, $in_file, $out_file);
}

function DisplayOptions() {
	echo ("\nFEIDIAN is a generic converter for monochrome bitplane and bitmap images. It\ncan work with any tile size (x,y does not matter) as well as custom tile\ndefinitions you can download or create yourself. Conversions are done between\nbitplane tiles and bitmap line graphics (suitable for Paintbrush or Photoshop).\n\nIt is also capable for creating dual-letter full width tile sets using a\nlist of two-letter combos and a half-width, bitmap font. This is useful if you\nare hacking a game with a large quantity of tiles but on a system where\nassembler modifications aren't an option (or if it's simply beyond your skill\nto do such).\n\nFor more information, review the readme.txt included with this program. For a\nbrief overview of commands, check commands.txt.\n\nSyntax:  FEIDIAN -[command] [string] [input] [output]\n\n");
}

?>
