<?php

//-----------------------------------------------------------------------------
// PC-Engine 16x16 Sprite Mode
// Written by Derrick Sobodash
//
// Byte Count: 16
// Dimensions: 16x16 font
// Structure :
//     Plane1: BBBBBBBBAAAAAAAA   Plane2: repeat
//             DDDDDDDDCCCCCCCC
//             FFFFFFFFEEEEEEEE
//             HHHHHHHHGGGGGGGG
//             JJJJJJJJIIIIIIII
//             LLLLLLLLKKKKKKKK
//             NNNNNNNNMMMMMMMM
//             PPPPPPPPOOOOOOOO
//             RRRRRRRRQQQQQQQQ
//             TTTTTTTTSSSSSSSS
//             VVVVVVVVUUUUUUUU
//             XXXXXXXXWWWWWWWW
//             ZZZZZZZZYYYYYYYY
//             bbbbbbbbaaaaaaaa
//             ddddddddcccccccc
//             ffffffffeeeeeeee
//
//     Plane3: repeat             Plane4: repeat
//-----------------------------------------------------------------------------

// Define what tile definition format this is
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
//define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
  define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 16;
$tile_height = 16;

// How many bytes are in the pattern?
$pat_size    = 32;

// What kind of byte ordering?
  $order = "planar";
//$order = "linear";

// You only need to define the pattern up till where it repeats. You may use
// whitespaces to help you align everything (they will be stripped when your
// string is used). 64 bytes is the limit.
//
// The hirearchy of letters is [A-Z] [a-z] [2-9] [!?@*]
//
// Basically, each unique letter is a byte, and the repeats of that letter are
// bits within that byte.

$plane1 = "BBBBBBBBAAAAAAAA
           DDDDDDDDCCCCCCCC
           FFFFFFFFEEEEEEEE
           HHHHHHHHGGGGGGGG
           JJJJJJJJIIIIIIII
           LLLLLLLLKKKKKKKK
           NNNNNNNNMMMMMMMM
           PPPPPPPPOOOOOOOO
           RRRRRRRRQQQQQQQQ
           TTTTTTTTSSSSSSSS
           VVVVVVVVUUUUUUUU
           XXXXXXXXWWWWWWWW
           ZZZZZZZZYYYYYYYY
           bbbbbbbbaaaaaaaa
           ddddddddcccccccc
           ffffffffeeeeeeee";

// Pallette: 2bpp supports four color graphics. Please define the four colors
//           to use here. Hex numbers can be done by prefixing with 0x[hex]
//           The format is R, G, B

// Color 0 [binary 0 0]
$color0 = array(0x00, 0x00, 0x00);

// Color 1 [binary 0 1]
$color1 = array(0x00, 0x00, 0xa8);

// Color 2 [binary 1 0]
$color2 = array(0x00, 0xa8, 0x00);

// Color 3 [binary 1 1]
$color3 = array(0x00, 0xa8, 0xa8);

// Color 4 [binary 0 0]
$color4 = array(0xa8, 0x00, 0x00);

// Color 5 [binary 0 1]
$color5 = array(0xa8, 0x00, 0xa8);

// Color 6 [binary 1 0]
$color6 = array(0xa8, 0x84, 0x00);

// Color 7 [binary 1 1]
$color7 = array(0xa8, 0xa8, 0xa8);

// Color 8 [binary 0 0]
$color8 = array(0x54, 0x54, 0x54);

// Color 9 [binary 0 1]
$color9 = array(0x54, 0x54, 0xfc);

// Color 10 [binary 1 0]
$colorA = array(0x54, 0xfc, 0x54);

// Color 11 [binary 1 1]
$colorB = array(0x54, 0xfc, 0xfc);

// Color 12 [binary 0 0]
$colorC = array(0xfc, 0x54, 0x54);

// Color 13 [binary 0 1]
$colorD = array(0xfc, 0x54, 0xfc);

// Color 14 [binary 1 0]
$colorE = array(0xfc, 0xfc, 0x54);

// Color 15 [binary 1 1]
$colorF = array(0xfc, 0xfc, 0xfc);

//-----------------------------------------------------------------------------
// Custom replacement dump routine
//-----------------------------------------------------------------------------
if($mode=="extract") {
	// Nuke all the user's whitespaces from the pattern
	$plane1 = preg_replace("/( *)/", "", $plane1);
	$plane1 = preg_replace("/(\\r*)/", "", $plane1);
	$plane1= preg_replace("/(\\n*)/", "", $plane1);
	$pattern_rows = strlen($plane1)/$tile_width;
	$bytes = ($tile_width*$tile_height)/8;
	
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Dumping $prefix from $in_file...\n";
	$fd = fopen($in_file, "rb");
	$bitmap = "";
	fseek($fd, $seekstart, SEEK_SET);
	print "  Converting to bitmap...\n";
	$pointer=0;
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$columns; $i++) {
			for ($z=0; $z<$tile_height; $z=$z+0) {
				// Get the number of bytes required for the
				// pattern to repeat
				$lenbyte = fread($fd, $pat_size);
				$temp_plane1 = $plane1;
				$temp_plane2 = $plane1;
				$temp_plane3 = $plane1;
				$temp_plane4 = $plane1;
				
				// Transform the bytes to bits, then place them
				// accordingly in our pattern string.
				for ($g=0; $g<strlen($lenbyte); $g++) {
					$binstring = str_pad(decbin(hexdec(bin2hex($lenbyte[$g]))), 8, "0", STR_PAD_LEFT);
					if($g<26) $offvar = 0x41;
					else if($g<52) $offvar = 0x61 - 26;
					else if($g<60) $offvar = 0x32 - 52;
					else if($g<61) $offvar = 0x21 - 60;
					else if($g<62) $offvar = 0x3f - 61;
					else if($g<63) $offvar = 0x40 - 62;
					else if($g<64) $offvar = 0x2a - 63;
					$lele = 0;
					while(strpos($temp_plane1, chr($offvar+$g)) !== FALSE) {
						$temp_plane1[strpos($temp_plane1, chr($offvar+$g))] = $binstring[$lele];
						$lele++;
					}
				}
				$lenbyte = fread($fd, $pat_size);
				for ($g=0; $g<strlen($lenbyte); $g++) {
					$binstring = str_pad(decbin(hexdec(bin2hex($lenbyte[$g]))), 8, "0", STR_PAD_LEFT);
					if($g<26) $offvar = 0x41;
					else if($g<52) $offvar = 0x61 - 26;
					else if($g<60) $offvar = 0x32 - 52;
					else if($g<61) $offvar = 0x21 - 60;
					else if($g<62) $offvar = 0x3f - 61;
					else if($g<63) $offvar = 0x40 - 62;
					else if($g<64) $offvar = 0x2a - 63;
					$lele = 0;
					while(strpos($temp_plane2, chr($offvar+$g)) !== FALSE) {
						$temp_plane2[strpos($temp_plane2, chr($offvar+$g))] = $binstring[$lele];
						$lele++;
					}
				}
				$lenbyte = fread($fd, $pat_size);
				for ($g=0; $g<strlen($lenbyte); $g++) {
					$binstring = str_pad(decbin(hexdec(bin2hex($lenbyte[$g]))), 8, "0", STR_PAD_LEFT);
					if($g<26) $offvar = 0x41;
					else if($g<52) $offvar = 0x61 - 26;
					else if($g<60) $offvar = 0x32 - 52;
					else if($g<61) $offvar = 0x21 - 60;
					else if($g<62) $offvar = 0x3f - 61;
					else if($g<63) $offvar = 0x40 - 62;
					else if($g<64) $offvar = 0x2a - 63;
					$lele = 0;
					while(strpos($temp_plane3, chr($offvar+$g)) !== FALSE) {
						$temp_plane3[strpos($temp_plane3, chr($offvar+$g))] = $binstring[$lele];
						$lele++;
					}
				}
				$lenbyte = fread($fd, $pat_size);
				for ($g=0; $g<strlen($lenbyte); $g++) {
					$binstring = str_pad(decbin(hexdec(bin2hex($lenbyte[$g]))), 8, "0", STR_PAD_LEFT);
					if($g<26) $offvar = 0x41;
					else if($g<52) $offvar = 0x61 - 26;
					else if($g<60) $offvar = 0x32 - 52;
					else if($g<61) $offvar = 0x21 - 60;
					else if($g<62) $offvar = 0x3f - 61;
					else if($g<63) $offvar = 0x40 - 62;
					else if($g<64) $offvar = 0x2a - 63;
					$lele = 0;
					while(strpos($temp_plane4, chr($offvar+$g)) !== FALSE) {
						$temp_plane4[strpos($temp_plane4, chr($offvar+$g))] = $binstring[$lele];
						$lele++;
					}
				}
				// Split the pattern string to rows and store
				// them to an array
				for($g=0; $g<strlen($temp_plane1)/$tile_width; $g++)
					$line[$i][$z+$g] = merge_four_planes(substr($temp_plane1, $g*$tile_width, $tile_width), substr($temp_plane2, $g*$tile_width, $tile_width), substr($temp_plane3, $g*$tile_width, $tile_width), substr($temp_plane4, $g*$tile_width, $tile_width));
				$z=$z+$g;
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
	// Transform the string from hex to bytes using the chr() command
	// (Note: It's faster than pack() in this case)
	for($i=0; $i<strlen($bitmap)/2; $i++)
		$bitmap2 .= chr(hexdec(substr($bitmap, $i*2, 2)));
	$bitmap = strrev($bitmap2);
	
	$palette = make_pal($color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
	$fo = fopen($out_file . "_$prefix.BMP", "wb");
	fputs($fo, bitmapheader_xbpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height, $palette) . strrev($bitmap));
	fclose($fo);
	print $out_file . "_$prefix.BMP was written!\n\n";
	
	die();
}
elseif($mode=="insert") {
	// Nuke all the user's whitespaces from the pattern
	$plane1 = preg_replace("/( *)/", "", $plane1);
	$plane1 = preg_replace("/(\\r*)/", "", $plane1);
	$plane1= preg_replace("/(\\n*)/", "", $plane1);
	$plane2 = preg_replace("/( *)/", "", $plane2);
	$plane2 = preg_replace("/(\\r*)/", "", $plane2);
	$plane2= preg_replace("/(\\n*)/", "", $plane2);
	$plane3 = preg_replace("/( *)/", "", $plane3);
	$plane3 = preg_replace("/(\\r*)/", "", $plane3);
	$plane3= preg_replace("/(\\n*)/", "", $plane3);
	$plane4 = preg_replace("/( *)/", "", $plane4);
	$plane4 = preg_replace("/(\\r*)/", "", $plane4);
	$plane4= preg_replace("/(\\n*)/", "", $plane4);
	$pattern_rows = strlen($plane1)/$tile_width;
	$bytes = ($tile_width*$tile_height)/8;

	
	// Create a file suffix specifying font width/height
	$prefix = $tile_width . "x" . $tile_height;
	print "Injecting $prefix into $out_file...\n";

	$bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76));

	$ptr=0; $bitplane="";
	print "  Converting bitmap to bitplane...\n";
	for ($k=0; $k<$rows; $k++) {
		for ($i=0; $i<$tile_height; $i++) {
			for ($z=0; $z<$columns; $z++) {
				$tile[$z][$i] = substr($bitmap, $ptr, $tile_width);
				$ptr += $tile_width;
			}
		}
		for ($z=$columns-1; $z>-1; $z--) {
			for ($i=0; $i<$tile_height; $i++) {
				$bitplane .= strrev($tile[$z][$i]);
			}
		}
		unset($tile);
	}
	$bitplane1="";
	$bitplane2="";
	$bitplane3="";
	$bitplane4="";
	for($i=0; $i<strlen($bitplane); $i++) {
		list($bit1, $bit2, $bit3, $bit4) = demux_four_planes($bitplane[$i]);
		$bitplane1.=$bit1;
		$bitplane2.=$bit2;
		$bitplane3.=$bit3;
		$bitplane4.=$bit4;
	}
	
	$hackplane = "";
	// The routine matches the characters in our pattern definition to
	// points in the hex string, then writes whatever bit is present to
	// a new byte string.
	for ($z=0; $z<strlen($bitplane1); $z=$z+strlen($plane1)) {
		$tiles_plane1 = substr($bitplane1, $z, strlen($plane1));
		$tiles_plane2 = substr($bitplane2, $z, strlen($plane1));
		$tiles_plane3 = substr($bitplane3, $z, strlen($plane1));
		$tiles_plane4 = substr($bitplane4, $z, strlen($plane1));
		$temp_plane1 = array("","","");
		for($g=0; $g<$pat_size; $g++) {
			$posx = 0;
			// Ascend through A-Z, a-z, 0-9
			if($g<26) $offvar = 0x41;
			else if($g<52) $offvar = 0x61 - 26;
			else if($g<60) $offvar = 0x32 - 52;
			else if($g<61) $offvar = 0x21 - 60;
			else if($g<62) $offvar = 0x3f - 61;
			else if($g<63) $offvar = 0x40 - 62;
			else if($g<64) $offvar = 0x2a - 63;
			while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
				$getpos = strpos($plane1, chr($offvar+$g), $posx);
				$temp_plane1[$g] .= $tiles_plane1[$getpos];
				$posx=$getpos+1;
			}
		}
		for($g=0; $g<$pat_size; $g++) {
			$posx = 0;
			// Ascend through A-Z, a-z, 0-9
			if($g<26) $offvar = 0x41;
			else if($g<52) $offvar = 0x61 - 26;
			else if($g<60) $offvar = 0x32 - 52;
			else if($g<61) $offvar = 0x21 - 60;
			else if($g<62) $offvar = 0x3f - 61;
			else if($g<63) $offvar = 0x40 - 62;
			else if($g<64) $offvar = 0x2a - 63;
			while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
				$getpos = strpos($plane1, chr($offvar+$g), $posx);
				$temp_plane1[$g+($pat_size)] .= $tiles_plane2[$getpos];
				$posx=$getpos+1;
			}
		}
		for($g=0; $g<$pat_size; $g++) {
			$posx = 0;
			// Ascend through A-Z, a-z, 0-9
			if($g<26) $offvar = 0x41;
			else if($g<52) $offvar = 0x61 - 26;
			else if($g<60) $offvar = 0x32 - 52;
			else if($g<61) $offvar = 0x21 - 60;
			else if($g<62) $offvar = 0x3f - 61;
			else if($g<63) $offvar = 0x40 - 62;
			else if($g<64) $offvar = 0x2a - 63;
			while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
				$getpos = strpos($plane1, chr($offvar+$g), $posx);
				$temp_plane1[$g+($pat_size*2)] .= $tiles_plane3[$getpos];
				$posx=$getpos+1;
			}
		}
		for($g=0; $g<$pat_size; $g++) {
			$posx = 0;
			// Ascend through A-Z, a-z, 0-9
			if($g<26) $offvar = 0x41;
			else if($g<52) $offvar = 0x61 - 26;
			else if($g<60) $offvar = 0x32 - 52;
			else if($g<61) $offvar = 0x21 - 60;
			else if($g<62) $offvar = 0x3f - 61;
			else if($g<63) $offvar = 0x40 - 62;
			else if($g<64) $offvar = 0x2a - 63;
			while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
				$getpos = strpos($plane1, chr($offvar+$g), $posx);
				$temp_plane1[$g+($pat_size*3)] .= $tiles_plane4[$getpos];
				$posx=$getpos+1;
			}
		}
		for($g=0; $g<count($temp_plane1); $g++)
			$hackplane .= $temp_plane1[$g];
	}
	$bitplane = $hackplane;
	
	// Transform out bitstring to bytes by evaling every 8 bits within a
	// chr();
	$output = "";
	for($i=0; $i<strlen($bitplane)/8; $i++)
		$output .= chr(bindec(substr($bitplane, $i*8, 8)));

	print "  Injecting new bitplane data...\n";
	injectfile($out_file, $seekstart, $output);
	
	print "$out_file was updated!\n\n";
	
	die();
}

?>
