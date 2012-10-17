<?php

//-----------------------------------------------------------------------------
// Generic NeoGeo MVS 2bpp Tile Definition
// Written by Derrick Sobodash
//
// Byte Count: 64
// Dimensions: 16x16 (mirrored)
// Structure :
//     Plane1: AAAAAAAAgggggggg  Plane2: BBBBBBBBhhhhhhhh
//             CCCCCCCCiiiiiiii          DDDDDDDDjjjjjjjj
//             EEEEEEEEkkkkkkkk          FFFFFFFFllllllll
//             GGGGGGGGmmmmmmmm          HHHHHHHHnnnnnnnn
//             IIIIIIIIoooooooo          JJJJJJJJpppppppp
//             KKKKKKKKqqqqqqqq          LLLLLLLLrrrrrrrr
//             MMMMMMMMssssssss          NNNNNNNNtttttttt
//             OOOOOOOOuuuuuuuu          PPPPPPPPvvvvvvvv
//             QQQQQQQQwwwwwwww          RRRRRRRRxxxxxxxx
//             SSSSSSSSyyyyyyyy          TTTTTTTTzzzzzzzz
//             UUUUUUUU22222222          VVVVVVVV33333333
//             WWWWWWWW44444444          XXXXXXXX55555555
//             YYYYYYYY66666666          ZZZZZZZZ77777777
//             aaaaaaaa88888888          bbbbbbbb99999999
//             cccccccc!!!!!!!!          dddddddd????????
//             eeeeeeee@@@@@@@@          ffffffff********
//-----------------------------------------------------------------------------

// Define what tile definition format this is
//define("COLOR_DEPTH", "2"); // Monochrome    (Single Plane)
  define("COLOR_DEPTH", "4"); // Four Color    (Two Plane)
//define("COLOR_DEPTH", "8"); // Eight Color   (Three Plane)
//define("COLOR_DEPTH","16"); // Sixteen Color (Four Plane)

// Tiles width and height in pixels (bits)
$tile_width  = 16;
$tile_height = 16;

// How many bytes are in the pattern?
$pat_size    = 64;

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

$plane1 = "AAAAAAAAgggggggg
           CCCCCCCCiiiiiiii
           EEEEEEEEkkkkkkkk
           GGGGGGGGmmmmmmmm
           IIIIIIIIoooooooo
           KKKKKKKKqqqqqqqq
           MMMMMMMMssssssss
           OOOOOOOOuuuuuuuu
           QQQQQQQQwwwwwwww
           SSSSSSSSyyyyyyyy
           UUUUUUUU22222222
           WWWWWWWW44444444
           YYYYYYYY66666666
           aaaaaaaa88888888
           cccccccc!!!!!!!!
           eeeeeeee@@@@@@@@";
           
$plane2 = "BBBBBBBBhhhhhhhh
           DDDDDDDDjjjjjjjj
           FFFFFFFFllllllll
           HHHHHHHHnnnnnnnn
           JJJJJJJJpppppppp
           LLLLLLLLrrrrrrrr
           NNNNNNNNtttttttt
           PPPPPPPPvvvvvvvv
           RRRRRRRRxxxxxxxx
           TTTTTTTTzzzzzzzz
           VVVVVVVV33333333
           XXXXXXXX55555555
           ZZZZZZZZ77777777
           bbbbbbbb99999999
           dddddddd????????
           ffffffff********";

// Pallette: 2bpp supports four color graphics. Please define the four colors
//           to use here. Hex numbers can be done by prefixing with 0x[hex]
//           The format is R, G, B

// Color 0 [binary 0 0]
$color0 = array(0x00, 0x00, 0x00);

// Color 1 [binary 0 1]
$color1 = array(0x63, 0xcf, 0x63);

// Color 2 [binary 1 0]
$color2 = array(0x39, 0x33, 0xff);

// Color 3 [binary 1 1]
$color3 = array(0xdc, 0xff, 0xff);

//-----------------------------------------------------------------------------
// Custom replacement dump routine
//-----------------------------------------------------------------------------
include("settings.php");
if($mode=="extract") {
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
          $line[$i][$z+$g] = strrev(merge_two_planes(substr($temp_plane1, $g*$tile_width, $tile_width), substr($temp_plane2, $g*$tile_width, $tile_width)));
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
    $color0 = "#" . str_pad(dechex($color0[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color0[2]), 2, "0", STR_PAD_LEFT);
    $color1 = "#" . str_pad(dechex($color1[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color1[2]), 2, "0", STR_PAD_LEFT);
    $color2 = "#" . str_pad(dechex($color2[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color2[2]), 2, "0", STR_PAD_LEFT);
    $color3 = "#" . str_pad(dechex($color3[0]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($color3[2]), 2, "0", STR_PAD_LEFT);
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
  
    $palette = make_pal($color0, $color1, $color2, $color3,
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
elseif($mode=="insert") {
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
        $bitplane .= $tile[$z][$i];
      }
    }
    unset($tile);
  }
  // Check if we need to deinterleave pixels
  if($interleave>0) {
    $bitplane_new = "";
    for ($z=0; $z<strlen($bitplane); $z+=2)
      $bitplane_new .= $bitplane[$z+1] . $bitplane[$z];
    $bitplane = $bitplane_new;
    unset($bitplane_new);
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
die;

?>
