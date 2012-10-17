<?php
/*
    FEIDIAN: The Freaking Easy, Indispensable Dot-Image formAt coNverter
    Copyright (C) 2003, 2004 Derrick Sobodash
    Version: 0.85
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
// FEIDIAN Custom Tile Insertion Module
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
// bmp2cust - converts a bitmap to a custom bitplane tile definition
//-----------------------------------------------------------------------------
function bmp2cust($rows, $columns, $tiledef, $seekstart, $in_file, $out_file, $invert) {
  $mode = "insert";
  // Include the user defined tile where we will get the replacement
  // patter, tile width, height, and byte count
  include("tiles/$tiledef.php");

  if(COLOR_DEPTH=="4"){
    bmp2cust2bpp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file);
    die();
  }
  if(COLOR_DEPTH=="8"){
    bmp2cust3bpp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file);
    die();
  }
  if(COLOR_DEPTH=="16"){
    bmp2cust4bpp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file);
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
    print "Injecting $prefix into $out_file...\n";

    if(GRAPHIC_FORMAT=="xpm")
      $bitmap = xpm2bitstring($in_file, 0);
    elseif(GRAPHIC_FORMAT=="bmp")
      $bitmap = strrev(binaryread($in_file, filesize($in_file)-62, 62, $invert));
    else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  
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
    $hackplane = "";
    // The routine matches the characters in our pattern definition to
    // points in the binary string, then writes whatever bit is present to
    // a new byte string.
    for ($z=0; $z<strlen($bitplane); $z=$z+$pat_size*8) {
      $tiles = substr($bitplane, $z, $tile_width*$pat_size);
      $temp_pattern = array("","","");
      for($g=0; $g<$pat_size; $g++) {
        $posx = 0;
        if($g<26) $offvar = 0x41;
        else if($g<52) $offvar = 0x61 - 26;
        else if($g<60) $offvar = 0x32 - 52;
        else if($g<61) $offvar = 0x21 - 60;
        else if($g<62) $offvar = 0x3f - 61;
        else if($g<63) $offvar = 0x40 - 62;
        else if($g<64) $offvar = 0x2a - 63;
        else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
        while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_pattern[$g] .= $tiles[$getpos];
          $posx=$getpos+1;
        }
      }
      for($g=0; $g<count($temp_pattern); $g++)
        $hackplane .= $temp_pattern[$g];
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
  }
  else die(print "ERROR: COLOR_DEPTH has not been defined.\n");
}

function bmp2cust2bpp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file) {
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
  if(strlen($plane1)!=strlen($plane2))
    die(print "ERROR: The planes in your tile definition are not the same size!\n");
  $pattern_rows = strlen($plane1)/$tile_width;
  $bytes = ($tile_width*$tile_height)/8;

  
  // Create a file suffix specifying font width/height
  $prefix = $tile_width . "x" . $tile_height;
  print "Injecting $prefix into $out_file...\n";

  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp")
    $bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76, $invert));
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  
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
  for($i=0; $i<strlen($bitplane); $i++) {
    list($bit1, $bit2) = demux_two_planes($bitplane[$i]);
    $bitplane1.=$bit1;
    $bitplane2.=$bit2;
  }
  
  $hackplane = "";
  // The routine matches the characters in our pattern definition to
  // points in the hex string, then writes whatever bit is present to
  // a new byte string.
  for ($z=0; $z<strlen($bitplane1); $z=$z+strlen($plane1)) {
    $tiles_plane1 = substr($bitplane1, $z, strlen($plane1));
    $tiles_plane2 = substr($bitplane2, $z, strlen($plane2));
    $temp_plane1 = array("","","");
    for($g=0; $g<$pat_size; $g++) {
      $posx = 0;
      if($g<26) $offvar = 0x41;
      else if($g<52) $offvar = 0x61 - 26;
      else if($g<60) $offvar = 0x32 - 52;
      else if($g<61) $offvar = 0x21 - 60;
      else if($g<62) $offvar = 0x3f - 61;
      else if($g<63) $offvar = 0x40 - 62;
      else if($g<64) $offvar = 0x2a - 63;
      else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
      if($order=="linear") {
        while((strpos($plane1, chr($offvar+$g), $posx) !== FALSE)&&(strpos($plane2, chr($offvar+$g), $posx) !== FALSE)) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane1[$getpos];
          $getpos = strpos($plane2, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane2[$getpos];
          $posx=$getpos+1;
        }
      }
      else {
        while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane1[$getpos];
          $posx=$getpos+1;
        }
        while(strpos($plane2, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane2, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane2[$getpos];
          $posx=$getpos+1;
        }
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
}

function bmp2cust3bpp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file) {
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
  print "Injecting $prefix into $out_file...\n";

  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp")
    $bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76, $invert));
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  
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
  for($i=0; $i<strlen($bitplane); $i++) {
    list($bit1, $bit2, $bit3) = demux_three_planes($bitplane[$i]);
    $bitplane1.=$bit1;
    $bitplane2.=$bit2;
    $bitplane3.=$bit3;
  }
  
  $hackplane = "";
  // The routine matches the characters in our pattern definition to
  // points in the hex string, then writes whatever bit is present to
  // a new byte string.
  for ($z=0; $z<strlen($bitplane1); $z=$z+strlen($plane1)) {
    $tiles_plane1 = substr($bitplane1, $z, strlen($plane1));
    $tiles_plane2 = substr($bitplane2, $z, strlen($plane2));
    $tiles_plane3 = substr($bitplane3, $z, strlen($plane3));
    $temp_plane1 = array("","","");
    for($g=0; $g<$pat_size; $g++) {
      $posx = 0;
      if($g<26) $offvar = 0x41;
      else if($g<52) $offvar = 0x61 - 26;
      else if($g<60) $offvar = 0x32 - 52;
      else if($g<61) $offvar = 0x21 - 60;
      else if($g<62) $offvar = 0x3f - 61;
      else if($g<63) $offvar = 0x40 - 62;
      else if($g<64) $offvar = 0x2a - 63;
      else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
      if($order=="linear") {
        while((strpos($plane1, chr($offvar+$g), $posx) !== FALSE)&&(strpos($plane2, chr($offvar+$g), $posx) !== FALSE)&&(strpos($plane3, chr($offvar+$g), $posx) !== FALSE)) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane1[$getpos];
          $getpos = strpos($plane2, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane2[$getpos];
          $getpos = strpos($plane3, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane3[$getpos];
          $posx=$getpos+1;
        }
      }
      else {
        while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane1[$getpos];
          $posx=$getpos+1;
        }
        while(strpos($plane2, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane2, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane2[$getpos];
          $posx=$getpos+1;
        }
        while(strpos($plane3, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane3, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane3[$getpos];
          $posx=$getpos+1;
        }
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
}

function bmp2cust4bpp($rows, $columns, $tiledef, $seekstart, $in_file, $out_file) {
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
  $plane4 = preg_replace("/( *)/", "", $plane4);
  $plane4 = preg_replace("/(\\r*)/", "", $plane4);
  $plane4= preg_replace("/(\\n*)/", "", $plane4);
  if(strlen($plane1)!=strlen($plane2))
    die(print "ERROR: The planes in your tile definition are not the same size!\n");
  $pattern_rows = strlen($plane1)/$tile_width;
  $bytes = ($tile_width*$tile_height)/8;

  
  // Create a file suffix specifying font width/height
  $prefix = $tile_width . "x" . $tile_height;
  print "Injecting $prefix into $out_file...\n";

  if(GRAPHIC_FORMAT=="xpm")
    $bitmap = xpm2bitstring($in_file, 0);
  elseif(GRAPHIC_FORMAT=="bmp")
    $bitmap = strrev(hexread($in_file, filesize($in_file)-0x76, 0x76, $invert));
  else die(print "FATAL ERROR: You haven't defined an image format! Please check your setings.php\n");
  
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
    $tiles_plane2 = substr($bitplane2, $z, strlen($plane2));
    $tiles_plane3 = substr($bitplane3, $z, strlen($plane3));
    $tiles_plane4 = substr($bitplane4, $z, strlen($plane4));
    $temp_plane1 = array("","","");
    for($g=0; $g<$pat_size; $g++) {
      $posx = 0;
      if($g<26) $offvar = 0x41;
      else if($g<52) $offvar = 0x61 - 26;
      else if($g<60) $offvar = 0x32 - 52;
      else if($g<61) $offvar = 0x21 - 60;
      else if($g<62) $offvar = 0x3f - 61;
      else if($g<63) $offvar = 0x40 - 62;
      else if($g<64) $offvar = 0x2a - 63;
      else if(($g<128)&&(EXTEND_LETTERS==TRUE)) $offvar = 0xa0 - 64;
      if($order=="linear") {
        while((strpos($plane1, chr($offvar+$g), $posx) !== FALSE)&&(strpos($plane2, chr($offvar+$g), $posx) !== FALSE)&&(strpos($plane3, chr($offvar+$g), $posx) !== FALSE)&&(strpos($plane4, chr($offvar+$g), $posx) !== FALSE)) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane1[$getpos];
          $getpos = strpos($plane2, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane2[$getpos];
          $getpos = strpos($plane3, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane3[$getpos];
          $getpos = strpos($plane4, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane4[$getpos];
          $posx=$getpos+1;
        }
      }
      else {
        while(strpos($plane1, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane1, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane1[$getpos];
          $posx=$getpos+1;
        }
        while(strpos($plane2, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane2, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane2[$getpos];
          $posx=$getpos+1;
        }
        while(strpos($plane3, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane3, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane3[$getpos];
          $posx=$getpos+1;
        }
        while(strpos($plane4, chr($offvar+$g), $posx) !== FALSE) {
          $getpos = strpos($plane4, chr($offvar+$g), $posx);
          $temp_plane1[$g] .= $tiles_plane4[$getpos];
          $posx=$getpos+1;
        }
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
}

?>
