<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003, 2004 Derrick Sobodash
    Version: 0.89
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
// FEIDIAN Custom Tile Ripping Module
//-----------------------------------------------------------------------------
// This module is used for dumping user defined, non-standard tile formats.
// The code is a little bit slow (there's a shitload of transformations and
// pattern matches needed to make this work) but on my 1GHz machine, most fonts
// were dumpable within 3 seconds.
//
// If you're insane, that might be too long for you. But for everyone else
// it should be fine.
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// cust2bmp - converts a custom bitplane tile definition to bitmap
//-----------------------------------------------------------------------------
function cust2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $invert, $tlp_pal){
  $mode = "extract";
  // Include the user defined tile where we will get the replacement
  // patter, tile width, height, and byte count
  include("tiles/$tiledef.php");

  if(COLOR_DEPTH=="4"){
    cust2bpp2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $tlp_pal);
    die();
  }
  if(COLOR_DEPTH=="8"){
    cust3bpp2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $tlp_pal);
    die();
  }
  if(COLOR_DEPTH=="16"){
    cust4bpp2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $tlp_pal);
    die();
  }
  if(COLOR_DEPTH=="2"){
    include("settings.php");
    if(FORCE_PROPER_WIDTH==TRUE){
      if(($columns*$tile_width)%8!=0)
        die(print "NOTICE: Based on your tile width and columns, there is a chance your dumping\n        may error! Consider dumping with a multiple of 8 columns. To disable\n        this check, edit settings.php.\n\n        FEIDIAN will now terminate.\n");
    }
    // Nuke all the user's whitespaces from the pattern
    $plane1 = preg_replace("/( *)/", "", $plane1);
    $plane1 = preg_replace("/(\\r*)/", "", $plane1);
    $plane1 = preg_replace("/(\\n*)/", "", $plane1);
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
          $temp_pattern = $plane1;
          
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
            else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
            $lele = 0;
            while(strpos($temp_pattern, chr($offvar+$g)) !== FALSE) {
              $temp_pattern[strpos($temp_pattern, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
          }
          
          // Split the pattern string to rows and store
          // them to an array
          for($g=0; $g<strlen($temp_pattern)/$tile_width; $g++)
            $line[$i][$z+$g] = substr($temp_pattern, $g*$tile_width, $tile_width);
          $z=$z+$g;
        }      
      }
      for ($z=0; $z<$tile_height; $z++) {
        for ($i=$columns-1; $i>-1; $i--) {
          $bitmap .= strrev($line[$i][$z]);
        }
      }
    }
    if ($invert==1) {
      $invertmap = "";
      for ($lala=0; $lala<strlen($bitmap); $lala++) {
        if ($bitmap[$lala]==0) $invertmap.=1;
        else if ($bitmap[$lala]==1) $invertmap.=0;
        else die("What'chu talkin' bout Willis?\n");
      }
      $bitmap = $invertmap;
    }
    // Check if we need to interleave pixels
    if($interleave>0) {
      $bitmap_new = "";
      for ($z=0; $z<strlen($bitmap); $z+=2)
        $bitmap_new .= $bitmap[$z+1] . $bitmap[$z];
      $bitmap = $bitmap_new;
      unset($bitmap_new);
    }
    if(GRAPHIC_FORMAT=="xpm") {
      $color0 = "#000000";
      $color1 = "#FFFFFF";
      $color2 = "#000000";
      $color3 = "#000000";
      $color4 = "#000000";
      $color5 = "#000000";
      $color6 = "#000000";
      $color7 = "#000000";
      $color8 = "#000000";
      $color9 = "#000000";
      $colorA = "#000000";
      $colorB = "#000000";
      $colorC = "#000000";
      $colorD = "#000000";
      $colorE = "#000000";
      $colorF = "#000000";
      writexpm($bitmap, $tile_width*$columns, $rows*$tile_height, $out_file, $prefix, $color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
    }
    elseif(GRAPHIC_FORMAT=="bmp") {
      $bitmap2 = "";
      $bitmap = strrev($bitmap);
          
      // Transform the string from bits to bytes using the chr() command
      // (Note: It's faster than pack() in this case)
      for($i=0; $i<strlen($bitmap)/8; $i++)
        $bitmap2 .= chr(bindec(substr($bitmap, $i*8, 8)));
      $bitmap = strrev($bitmap2);
      
      $fo = fopen($out_file . "_$prefix.bmp", "wb");
      fputs($fo, bitmapheader_1bpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height) . strrev($bitmap));
      fclose($fo);
      print $out_file . "_$prefix.bmp was written!\n\n";
    }
    else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  }
  else die(print "ERROR: COLOR_DEPTH has not been defined.\n");
}

function cust2bpp2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $tlp_pal) {
  // Include the user defined tile where we will get the replacement
  // patter, tile width, height, and byte count
  include("tiles/$tiledef.php");
  include("settings.php");
  if(FORCE_PROPER_WIDTH==TRUE){
    if(($columns*$tile_width)%8!=0)
      die(print "NOTICE: Based on your tile width and columns, there is a chance your dumping\n        may error! Consider dumping with a multiple of 8 columns. To disable\n        this check, edit settings.php.\n\n        FEIDIAN will now terminate.\n");
  }
  
  // Nuke all the user's whitespaces from the pattern
  $plane1 = preg_replace("/( *)/", "", $plane1);
  $plane1 = preg_replace("/(\\r*)/", "", $plane1);
  $plane1 = preg_replace("/(\\n*)/", "", $plane1);
  $plane2 = preg_replace("/( *)/", "", $plane2);
  $plane2 = preg_replace("/(\\r*)/", "", $plane2);
  $plane2= preg_replace("/(\\n*)/", "", $plane2);
  if(strlen($plane1)!=strlen($plane2))
    die(print "ERROR: The planes in your tile definition are not the same size!\n");
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
        $temp_plane2 = $plane2;
        
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
          else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
          $lele = 0;
          if($order=="linear") {
            while(strpos($temp_plane2, chr($offvar+$g)) !== FALSE) {
              $temp_plane1[strpos($temp_plane1, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
              $temp_plane2[strpos($temp_plane2, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
          }
          else {
            while(strpos($temp_plane1, chr($offvar+$g)) !== FALSE) {
              $temp_plane1[strpos($temp_plane1, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
            while(strpos($temp_plane2, chr($offvar+$g)) !== FALSE) {
              $temp_plane2[strpos($temp_plane2, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
          }
        }
        // Split the pattern string to rows and store
        // them to an array
        for($g=0; $g<strlen($temp_plane1)/$tile_width; $g++)
          $line[$i][$z+$g] = merge_two_planes(substr($temp_plane1, $g*$tile_width, $tile_width), substr($temp_plane2, $g*$tile_width, $tile_width));
        $z=$z+$g;
      }
    }
    for ($z=0; $z<$tile_height; $z++) {
      for ($i=$columns-1; $i>-1; $i--) {
      	$bitmap .= strrev($line[$i][$z]);
      }
    }
  }
  // Check if we need to interleave pixels
  if($interleave>0) {
    $bitmap_new = "";
    for ($z=0; $z<strlen($bitmap); $z+=2)
      $bitmap_new .= $bitmap[$z+1] . $bitmap[$z];
    $bitmap = $bitmap_new;
    unset($bitmap_new);
  }
  if(GRAPHIC_FORMAT=="xpm") {
    if($tlp_pal) {
      $palstream = fopen($tlp_pal, "rb");
      if(fread($palstream, 3) != "TLP") die(print "ERROR: The supplied palette is not from Tile Layer Pro!'n");
      $null = fread($palstream, 1); unset($null);
      $color0 = "#" . bin2hex(fread($palstream, 3));
      $color1 = "#" . bin2hex(fread($palstream, 3));
      $color2 = "#" . bin2hex(fread($palstream, 3));
      $color3 = "#" . bin2hex(fread($palstream, 3));
      fclose($palstream);
    }
    else {
      $color0 = "#" . str_pad(dechex($color0[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[2]), 2, "0", STR_PAD_LEFT);
      $color1 = "#" . str_pad(dechex($color1[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[2]), 2, "0", STR_PAD_LEFT);
      $color2 = "#" . str_pad(dechex($color2[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[2]), 2, "0", STR_PAD_LEFT);
      $color3 = "#" . str_pad(dechex($color3[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[2]), 2, "0", STR_PAD_LEFT);
    }
    $color4 = "#000000";
    $color5 = "#000000";
    $color6 = "#000000";
    $color7 = "#000000";
    $color8 = "#000000";
    $color9 = "#000000";
    $colorA = "#000000";
    $colorB = "#000000";
    $colorC = "#000000";
    $colorD = "#000000";
    $colorE = "#000000";
    $colorF = "#000000";
    writexpm($bitmap, $tile_width*$columns, $rows*$tile_height, $out_file, $prefix, $color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    $bitmap2 = "";
    $bitmap = strrev($bitmap);

    // Transform the string from hex to bytes using the chr() command
    // (Note: It's faster than pack() in this case)
    for($i=0; $i<strlen($bitmap)/2; $i++)
      $bitmap2 .= chr(hexdec(substr($bitmap, $i*2, 2)));
    $bitmap = strrev($bitmap2);
    if($tlp_pal) {
      $palstream = fopen($tlp_pal, "rb");
      if(fread($palstream, 3) != "TLP") die(print "ERROR: The supplied palette is not from Tile Layer Pro!'n");
      $null = fread($palstream, 1); unset($null);
      $palette = "";
      for($pnum=0; $pnum<4; $pnum++)
        $palette .= strrev(fread($palstream, 3)) . chr(0);
      for($pnum=0; $pnum<12; $pnum++)
        $palette .= chr(0) . chr(0) . chr(0) . chr(0);
      fclose($palstream);
    }  
    else $palette = make_pal($color0, $color1, $color2, $color3,
                             array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0),
                             array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0),
                             array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0));
    $fo = fopen($out_file . "_$prefix.bmp", "wb");
    fputs($fo, bitmapheader_xbpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height, $palette) . strrev($bitmap));
    fclose($fo);
    print $out_file . "_$prefix.bmp was written!\n\n";
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
}

function cust3bpp2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $tlp_pal) {
  // Include the user defined tile where we will get the replacement
  // patter, tile width, height, and byte count
  include("tiles/$tiledef.php");
  include("settings.php");
  if(FORCE_PROPER_WIDTH==TRUE){
    if(($columns*$tile_width)%8!=0)
      die(print "NOTICE: Based on your tile width and columns, there is a chance your dumping\n        may error! Consider dumping with a multiple of 8 columns. To disable\n        this check, edit settings.php.\n\n        FEIDIAN will now terminate.\n");
  }

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
  if(strlen($plane1)!=strlen($plane2))
    die(print "ERROR: The planes in your tile definition are not the same size!\n");
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
        $temp_plane2 = $plane2;
        $temp_plane3 = $plane3;
        
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
          else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
          $lele = 0;
          while(strpos($temp_plane1, chr($offvar+$g)) !== FALSE) {
            $temp_plane1[strpos($temp_plane1, chr($offvar+$g))] = $binstring[$lele];
            $lele++;
          }
          while(strpos($temp_plane2, chr($offvar+$g)) !== FALSE) {
            $temp_plane2[strpos($temp_plane2, chr($offvar+$g))] = $binstring[$lele];
            $lele++;
          }
          while(strpos($temp_plane3, chr($offvar+$g)) !== FALSE) {
            $temp_plane3[strpos($temp_plane3, chr($offvar+$g))] = $binstring[$lele];
            $lele++;
          }
        }
        // Split the pattern string to rows and store
        // them to an array
        for($g=0; $g<strlen($temp_plane1)/$tile_width; $g++)
          $line[$i][$z+$g] = merge_three_planes(substr($temp_plane1, $g*$tile_width, $tile_width), substr($temp_plane2, $g*$tile_width, $tile_width), substr($temp_plane3, $g*$tile_width, $tile_width));
        $z=$z+$g;
      }
    }
    for ($z=0; $z<$tile_height; $z++) {
      for ($i=$columns-1; $i>-1; $i--) {
      	$bitmap .= strrev($line[$i][$z]);
      }
    }
  }
  // Check if we need to interleave pixels
  if($interleave>0) {
    $bitmap_new = "";
    for ($z=0; $z<strlen($bitmap); $z+=2)
      $bitmap_new .= $bitmap[$z+1] . $bitmap[$z];
    $bitmap = $bitmap_new;
    unset($bitmap_new);
  }
  if(GRAPHIC_FORMAT=="xpm") {
    if($tlp_pal) {
      $palstream = fopen($tlp_pal, "rb");
      if(fread($palstream, 3) != "TLP") die(print "ERROR: The supplied palette is not from Tile Layer Pro!'n");
      $null = fread($palstream, 1); unset($null);
      $color0 = "#" . bin2hex(fread($palstream, 3));
      $color1 = "#" . bin2hex(fread($palstream, 3));
      $color2 = "#" . bin2hex(fread($palstream, 3));
      $color3 = "#" . bin2hex(fread($palstream, 3));
      $color4 = "#" . bin2hex(fread($palstream, 3));
      $color5 = "#" . bin2hex(fread($palstream, 3));
      $color6 = "#" . bin2hex(fread($palstream, 3));
      $color7 = "#" . bin2hex(fread($palstream, 3));
      fclose($palstream);
    }
    else {
      $color0 = "#" . str_pad(dechex($color0[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[2]), 2, "0", STR_PAD_LEFT);
      $color1 = "#" . str_pad(dechex($color1[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[2]), 2, "0", STR_PAD_LEFT);
      $color2 = "#" . str_pad(dechex($color2[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[2]), 2, "0", STR_PAD_LEFT);
      $color3 = "#" . str_pad(dechex($color3[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[2]), 2, "0", STR_PAD_LEFT);
      $color4 = "#" . str_pad(dechex($color4[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color4[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color4[2]), 2, "0", STR_PAD_LEFT);
      $color5 = "#" . str_pad(dechex($color5[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color5[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color5[2]), 2, "0", STR_PAD_LEFT);
      $color6 = "#" . str_pad(dechex($color6[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color6[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color6[2]), 2, "0", STR_PAD_LEFT);
      $color7 = "#" . str_pad(dechex($color7[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color7[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color7[2]), 2, "0", STR_PAD_LEFT);
    }
    $color8 = "#000000";
    $color9 = "#000000";
    $colorA = "#000000";
    $colorB = "#000000";
    $colorC = "#000000";
    $colorD = "#000000";
    $colorE = "#000000";
    $colorF = "#000000";
    writexpm($bitmap, $tile_width*$columns, $rows*$tile_height, $out_file, $prefix, $color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    $bitmap2 = "";
    $bitmap = strrev($bitmap);

    // Transform the string from hex to bytes using the chr() command
    // (Note: It's faster than pack() in this case)
    for($i=0; $i<strlen($bitmap)/2; $i++)
      $bitmap2 .= chr(hexdec(substr($bitmap, $i*2, 2)));
    $bitmap = strrev($bitmap2);
    if($tlp_pal) {
      $palstream = fopen($tlp_pal, "rb");
      if(fread($palstream, 3) != "TLP") die(print "ERROR: The supplied palette is not from Tile Layer Pro!'n");
      $null = fread($palstream, 1); unset($null);
      $palette = "";
      for($pnum=0; $pnum<8; $pnum++)
        $palette .= strrev(fread($palstream, 3)) . chr(0);
      for($pnum=0; $pnum<8; $pnum++)
        $palette .= chr(0) . chr(0) . chr(0) . chr(0);
      fclose($palstream);
    }
    else $palette = make_pal($color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7,
                             array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0),
                             array(0, 0, 0), array(0, 0, 0), array(0, 0, 0), array(0, 0, 0));
    $fo = fopen($out_file . "_$prefix.bmp", "wb");
    fputs($fo, bitmapheader_xbpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height, $palette) . strrev($bitmap));
    fclose($fo);
    print $out_file . "_$prefix.bmp was written!\n\n";
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
}

function cust4bpp2bmp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $tlp_pal) {
  // Include the user defined tile where we will get the replacement
  // patter, tile width, height, and byte count
  include("settings.php");
  include("tiles/$tiledef.php");
  if(FORCE_PROPER_WIDTH==TRUE){
    if(($columns*$tile_width)%8!=0)
      die(print "NOTICE: Based on your tile width and columns, there is a chance your dumping\n        may error! Consider dumping with a multiple of 8 columns. To disable\n        this check, edit settings.php.\n\n        FEIDIAN will now terminate.\n");
  }
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
  if(strlen($plane1)!=strlen($plane2))
    die(print "ERROR: The planes in your tile definition are not the same size!\n");
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
        $temp_plane2 = $plane2;
        $temp_plane3 = $plane3;
        $temp_plane4 = $plane4;
        
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
          else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
          $lele = 0;
          if($order=="linear") {
            while(strpos($temp_plane1, chr($offvar+$g)) !== FALSE) {
              $temp_plane1[strpos($temp_plane1, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
              $temp_plane2[strpos($temp_plane2, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
              $temp_plane3[strpos($temp_plane3, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
              $temp_plane4[strpos($temp_plane4, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
          }
          else {
            while(strpos($temp_plane1, chr($offvar+$g)) !== FALSE) {
              $temp_plane1[strpos($temp_plane1, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
            while(strpos($temp_plane2, chr($offvar+$g)) !== FALSE) {
              $temp_plane2[strpos($temp_plane2, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
            while(strpos($temp_plane3, chr($offvar+$g)) !== FALSE) {
              $temp_plane3[strpos($temp_plane3, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
            while(strpos($temp_plane4, chr($offvar+$g)) !== FALSE) {
              $temp_plane4[strpos($temp_plane4, chr($offvar+$g))] = $binstring[$lele];
              $lele++;
            }
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
  // Check if we need to interleave pixels
  if($interleave>0) {
    $bitmap_new = "";
    for ($z=0; $z<strlen($bitmap); $z+=2)
      $bitmap_new .= $bitmap[$z+1] . $bitmap[$z];
    $bitmap = $bitmap_new;
    unset($bitmap_new);
  }
  if(GRAPHIC_FORMAT=="xpm") {
    if($tlp_pal) {
      $palstream = fopen($tlp_pal, "rb");
      if(fread($palstream, 3) != "TLP") die(print "ERROR: The supplied palette is not from Tile Layer Pro!'n");
      $null = fread($palstream, 1); unset($null);
      $color0 = "#" . bin2hex(fread($palstream, 3));
      $color1 = "#" . bin2hex(fread($palstream, 3));
      $color2 = "#" . bin2hex(fread($palstream, 3));
      $color3 = "#" . bin2hex(fread($palstream, 3));
      $color4 = "#" . bin2hex(fread($palstream, 3));
      $color5 = "#" . bin2hex(fread($palstream, 3));
      $color6 = "#" . bin2hex(fread($palstream, 3));
      $color7 = "#" . bin2hex(fread($palstream, 3));
      $color8 = "#" . bin2hex(fread($palstream, 3));
      $color9 = "#" . bin2hex(fread($palstream, 3));
      $colorA = "#" . bin2hex(fread($palstream, 3));
      $colorB = "#" . bin2hex(fread($palstream, 3));
      $colorC = "#" . bin2hex(fread($palstream, 3));
      $colorD = "#" . bin2hex(fread($palstream, 3));
      $colorE = "#" . bin2hex(fread($palstream, 3));
      $colorF = "#" . bin2hex(fread($palstream, 3));
      fclose($palstream);
    }
    else {
      $color0 = "#" . str_pad(dechex($color0[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[2]), 2, "0", STR_PAD_LEFT);
      $color1 = "#" . str_pad(dechex($color1[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[2]), 2, "0", STR_PAD_LEFT);
      $color2 = "#" . str_pad(dechex($color2[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[2]), 2, "0", STR_PAD_LEFT);
      $color3 = "#" . str_pad(dechex($color3[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[2]), 2, "0", STR_PAD_LEFT);
      $color4 = "#" . str_pad(dechex($color4[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color4[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color4[2]), 2, "0", STR_PAD_LEFT);
      $color5 = "#" . str_pad(dechex($color5[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color5[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color5[2]), 2, "0", STR_PAD_LEFT);
      $color6 = "#" . str_pad(dechex($color6[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color6[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color6[2]), 2, "0", STR_PAD_LEFT);
      $color7 = "#" . str_pad(dechex($color7[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color7[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color7[2]), 2, "0", STR_PAD_LEFT);
      $color8 = "#" . str_pad(dechex($color8[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color8[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color8[2]), 2, "0", STR_PAD_LEFT);
      $color9 = "#" . str_pad(dechex($color9[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color9[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color9[2]), 2, "0", STR_PAD_LEFT);
      $colorA = "#" . str_pad(dechex($colorA[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorA[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorA[2]), 2, "0", STR_PAD_LEFT);
      $colorB = "#" . str_pad(dechex($colorB[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorB[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorB[2]), 2, "0", STR_PAD_LEFT);
      $colorC = "#" . str_pad(dechex($colorC[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorC[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorC[2]), 2, "0", STR_PAD_LEFT);
      $colorD = "#" . str_pad(dechex($colorD[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorD[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorD[2]), 2, "0", STR_PAD_LEFT);
      $colorE = "#" . str_pad(dechex($colorE[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorE[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorE[2]), 2, "0", STR_PAD_LEFT);
      $colorF = "#" . str_pad(dechex($colorF[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorF[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($colorF[2]), 2, "0", STR_PAD_LEFT);
    }
    writexpm($bitmap, $tile_width*$columns, $rows*$tile_height, $out_file, $prefix, $color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
  }
  elseif(GRAPHIC_FORMAT=="bmp") {
    $bitmap2 = "";
    $bitmap = strrev($bitmap);
    // Transform the string from hex to bytes using the chr() command
    // (Note: It's faster than pack() in this case)
    for($i=0; $i<strlen($bitmap)/2; $i++)
      $bitmap2 .= chr(hexdec(substr($bitmap, $i*2, 2)));
    $bitmap = strrev($bitmap2);
    if($tlp_pal) {
      //die (print $tlp_pal);
      $palstream = fopen($tlp_pal, "rb");
      if(fread($palstream, 3) != "TPL") die(print "ERROR: The supplied palette is not from Tile Layer Pro!\n");
      $null = fread($palstream, 1); unset($null);
      $palette = "";
      for($pnum=0; $pnum<16; $pnum++)
        $palette .= strrev(fread($palstream, 3)) . chr(0);
      fclose($palstream);
    }
    else $palette=make_pal($color0, $color1, $color2, $color3, $color4, $color5, $color6, $color7, $color8, $color9, $colorA, $colorB, $colorC, $colorD, $colorE, $colorF);
    $fo = fopen($out_file . "_$prefix.bmp", "wb");
    fputs($fo, bitmapheader_xbpp(strlen($bitmap), $tile_width*$columns, $rows*$tile_height, $palette) . strrev($bitmap));
    fclose($fo);
    print $out_file . "_$prefix.bmp was written!\n\n";
  }
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  
}

?>
