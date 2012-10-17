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
//-----------------------------------------------------------------------------
// FEIDIAN Font File Module
//-----------------------------------------------------------------------------
// This module is used for handling font files (BDF or FD).
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// makefd - converts bitmap to FD font descriptor
//-----------------------------------------------------------------------------
function makefd($tile_width, $tile_height, $vwf, $spacing, $descent, $in_file, $out_file){
	$rows=16; $columns=16;

	$bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, 0));

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
	include("copyright.php");
	$output = "facename " . $fontname . "\ncopyright " . $copyright . "\n\nheight " . $tile_height . "\nascent " . ($tile_height-$descent) . "\n\n";
	for($i=0; $i<256; $i++)
		$tilebank[$i] = substr($bitplane, $i*(($tile_width*$tile_height)), (($tile_width*$tile_height)));
	for($i=0; $i<256; $i++) {
		if($vwf==0){
			$output .= "char $i\nwidth $tile_width\n" . wordwrap($tilebank[$i], $tile_width, "\n", 1) . "\n\n";
		}
		elseif($vwf==1) {
			list($thistile, $tilewidth) = makevwftile($tilebank[$i], $tile_width, $spacing);
			$output .= "char $i\nwidth $tilewidth\n" . wordwrap($thistile, $tilewidth, "\n", 1) . "\n\n";
		}
	}
	$fo=fopen($out_file, "wb");
	fputs($fo, $output);
	fclose($fo);
}

//-----------------------------------------------------------------------------
// makebdf - converts bitmap to BDF text font descriptor
//-----------------------------------------------------------------------------
function makebdf($tile_width, $tile_height, $vwf, $spacing, $descent, $in_file, $out_file){
	die("This feature is currently under development.");
}

?>
