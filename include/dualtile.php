<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003,2004 Derrick Sobodash
    Version: 0.4
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
//-----------------------------------------------------------------------------
// FEIDIAN Dual-Tile Module
//-----------------------------------------------------------------------------
// This module is used for building a new full-width font using a text list of
// two letter combos and a half-width font. The font must be at least a set of
// 256 tiles following the standard ASCII ordering.
//
// This routine will ONLY output a Biitmap. It will not inject into the ROM.
// Before you yell at me, yes, I could have EASILY added injecting the font
// right into the ROM. But you don't want me to.
//
// You know your number tiles? Or icons which you can't change or the game goes
// splat? Well if I wrote straight into the rom, those would get tanked.
//
// Instead, I write a bitmap so that if those tiles happen to fall where a DTE
// was writte, you can just move that dual tile somewhere else and write the
// icon back there instead.
//
// Then just insert the bitmap normally as you would any other bitmap.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// bit2tile - converts bitmap to bitplane tile data
//-----------------------------------------------------------------------------
function bit2tile($tile_height, $tile_width, $tile_list, $in_file, $out_file) {
	$rows=16; $columns=16;
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Injecting $prefix into $out_file...\n";

	$bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62));

	$ptr=0; $bitplane = "";
	print "  Converting bitmap to bitplane...\n";
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$tile_height; $i++) {
			for ($z=0; $z<$columns; $z++) {
				$tile[$z][$i] = strrev(substr($bitmap, $ptr, $tile_width));
				$ptr += $tile_width;
			}
		}
		for ($z=$columns-1; $z>-1; $z--) {
			for ($i=0; $i<$tile_height; $i++) {
				$bitplane .= $tile[$z][$i];
			}
		}
		unset($tile);
	}
	$output = "";
	// Transform back from binary string to data
	for($i=0; $i<strlen($bitplane)/8; $i++)
		$output .= chr(bindec(substr($bitplane, $i*8, 8)));
	for($i=0; $i<256; $i++)
		$tilebank[$i] = substr($output, $i*(($tile_width*$tile_height)/8), (($tile_width*$tile_height)/8));
	
	$tl = fopen($tile_list, "rb");
	$tldump = fread($tl, filesize($tile_list));
	fclose($tl);
	$tldump = preg_replace("/(\\r*)/", "", $tldump);
	$tldump = preg_replace("/(\\n*)/", "", $tldump);
	
	$binarydump=""; $fddump="";
	$columns = $columns * 2;
	while(strlen($tldump) % $columns != 0)
		$tldump .= " ";
	for($i=0; $i<strlen($tldump); $i++)
		$fddump .= $tilebank[hexdec(bin2hex($tldump[$i]))];
	for($i=0; $i<strlen($fddump); $i++) {
		$binarydump .= str_pad(decbin(hexdec(bin2hex(substr($fddump, $i, 1)))), 8, "0", STR_PAD_LEFT);
	}
	$rows = strlen($tldump)/$columns;
	$bitmap = "";
	print "  Converting to bitmap...\n";
	$pointer=0;
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$columns; $i++) {
			for ($z=0; $z<$tile_height; $z++) {
				// Grab $tile_width bits from the string and
				// store them as a row
				$line[$i][$z] = substr($binarydump, $pointer, $tile_width);
				$pointer = $pointer + $tile_width;
			}
		}
		for ($z=0; $z<$tile_height; $z++) {
			for ($i=$columns-1; $i>-1; $i--) {
				$bitmap .= strrev($line[$i][$z]);
			}
		}
	}
	$bitmap2 = "";
	$bitmap = strrev($bitmap);
	// Transform back from bit string to data
	for($i=0; $i<strlen($bitmap)/8; $i++)
		$bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
	$bitmap = strrev($bitmap2);
	
	$fo = fopen($out_file . "_$prefix.BMP", "wb");
	fputs($fo, bitmapheader(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
	fclose($fo);
	print $out_file . "_$prefix.BMP was written!\n\n";
}

?>
