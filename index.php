<?php
//
// Meatloaf - A Commodore 64/128 multi-device emulator
// https://github.com/idolpx/meatloaf
// Copyright(C) 2022 James Johnston
//
// Meatloaf Server Script-----------------------------------------
// Create a directory listing as a Commodore Basic Program
// Responds with binary PRG file ready to load and list
// ---------------------------------------------------------------
//
// Meatloaf is free software : you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Meatloaf is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Meatloaf. If not, see <http://www.gnu.org/licenses/>.
//

//
// https://gist.github.com/idolpx/ab8874f8396b6fa0d89cc9bab1e4dee2
//

//
// Requirments: Apache (mod_rewrite), PHP 7+
//
// Instructions:
// 1. Save this script as "index.php" in the root of your web server.
// 2. Create a ".htaccess" file in the root of your web server with the following lines
//
//    RewriteEngine on
//    RewriteCond %{REQUEST_FILENAME}/index.prg -f
//    RewriteRule ^(.*)$ $1/index.prg
//    RewriteCond %{REQUEST_FILENAME} !-f
//    RewriteRule ^.*$ /index.php [L,QSA]
//    AddType application/octet-stream .bas .prg .p00
//    AddType application/octet-stream .bin .rom .crt
//    AddType application/octet-stream .bbt .d8b .dfi .rp9
//    AddType application/octet-stream .d64 .d71 .d80 .d81 .d82 .d90 .dnp
//    AddType application/octet-stream .g41 .g64 .g71 .nib .nbz
//    AddType application/octet-stream .t64 .tcrt .tap .htap
//


// This header will be displayed at the top of directory listings
// It should be no longer than 16 characters
$header = substr("C64 FOR LIFE", 0, 16);

/////////////////////////////////////////////////////////////////

$basic_start = 0x0801;
$next_entry = $basic_start;

$root = $_SERVER["DOCUMENT_ROOT"]."/";
$url = $_SERVER['SERVER_NAME'];

$dir = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$dir = urldecode($dir);


function get_type($name)
{
    global $root;

    if(is_dir($root.$name))
    {
        $ext = "DIR";
    }
    else
    {
        $ext = pathinfo($root.$name, PATHINFO_EXTENSION);
        if (strlen($ext) < 3)
            $ext = "PRG";
    }
    return strtoupper($ext);
}

function sendLine($blocks, $line)
{
    global $next_entry;

    $line .= "\x00";
    //$next_entry = $next_entry + 4 + strlen($line);
    //echo pack('v', $next_entry);
	echo pack('v', 0x0101);
    echo pack('v', $blocks);
    echo strtoupper("$line");
}

function sendListing($dir, $exp)
{
    global $url, $root, $basic_start, $header;

    // Send basic load address
    echo pack('v', $basic_start);

    // Send List HEADER
    sendLine(0, "\x12\"$header\" 08 2A");

    //echo "[$dir]"; exit();
	$directory = preg_filter($exp, '$0', scandir($root.$dir));

    // Send Extra INFO
    sendLine(0, sprintf("\"%-19s\" NFO", "[URL]"));
    sendLine(0, sprintf("\"%-19s\" NFO", $url));
    if (strlen($dir) > 1)
    {
        sendLine(0, sprintf("\"%-19s\" NFO", "[PATH]"));
        sendLine(0, sprintf("\"%-19s\" NFO", $dir));
    }
    sendLine(0, "\"-------------------\" NFO");

    // Send file entries
    foreach($directory as $key => $file) {
        $stat = stat("$root$dir/$file");
        $type = get_type("$dir/$file");
        $blocks = 0;
        $block_spc = 3;
        if ( $type != "DIR" ) {
            $blocks = ceil($stat['size']/256);
            if ($blocks > 9) $block_spc--;
            if ($blocks > 99) $block_spc--;
        }
        $line = sprintf("%s%-18s %s", str_repeat(" ", $block_spc), "\"".$file."\"", $type);
        sendLine( $blocks, $line );
    }

    sendLine( 65535, "BLOCKS FREE" );

    // Send 0000 to end basic program
    echo "\x00\x00";
}

if (strstr($_SERVER['HTTP_USER_AGENT'], "MEATLOAF"))
{
    //Set Content Type
    header('Content-Type: application/octet-stream');

    //Use Content-Disposition: attachment to specify the filename
    header('Content-Disposition: attachment; filename="index.prg"');
    header('Meatloaf-Debug: '.$dir);

    sendListing($dir, '/(?!^\..*?$|^.*?.html|^.*?.php|^api$|^web.config$)^.*?$/i');
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>C64 for life</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>
<body>
    <header>
        <div class="container">
            <table>
            <tr>
                <td>
                    <img src="img/C64_logo.png" alt="C64 Graphic"><br>
                </td>
                <td>
                    <h1>C64 for life</h1>
                </td>
            </tr>
        </table>
            <nav>
                    <!--
                <ul>
                    <li class="current"><a href="#">Home</a></li>
                    <li><a href="https://en.wikipedia.org/wiki/Commodore_64">About C64</a></li>
                    <li><a href="https://www.c64-wiki.com/">C64 Wiki</a></li>
                    <li><a href="https://csdb.dk/">C64 Scene Database</a></li>
                    <li><a href="https://www.lemon64.com/">Lemon64</a></li>
                </ul>
                    -->
                    </nav>
        </div>
    </header>

    <div class="container">
        <section id="showcase">
            <!-- <img src="img/C64_logo.png" alt="C64 Graphic"> -->
            <p>Welcome to the Commodore 64 Resource Page.<br>
            This website is dedicated to some information, resources, and links related to the Commodore 64.</p>
<!--            <img src="OhhC64.png" alt="C64 Graphic"> -->
            <br>
            <h1>Some links</h1>
            <ul>
                <li><a href="https://meatloaf.cc" target="_blank">Meatloaf</a> A Commodore IEC Serial Floppy Drive and WiFi Modem multi-device emulator.</li>
                <li><a href="https://github.com/idolpx/meatloaf-specialty" target="_blank">Meatloaf speciality</a> the code.</li>
                <li><a href="https://github.com/Hartland/C64-Keyboard" target="_blank">C64-Keyboard</a> C64 PS2/USB Keyboard.</li>
                <li><a href="https://www.pictorial64.com/" target="_blank">Pictorial C64</a> The Pictorial C64 Fault Guide</li>
                <li><a href="https://www.pagetable.com/?p=568 target="_blank">Fast Loader</a> A 256 Byte Autostart Fast Loader for the Commodore 64</li>
                <li><a href="https://c64os.com/post/loadrunfromasm1 target="_blank">Read C64 data</a> Load and Run from 6502 ASM</li>
            </ul>
        <h1>Available PRG Files</h1>
        <ul>
 <?php include 'list_prg.php'; ?>
        </ul>
    <br>
  <img src="img/c64.webp" alt="C64 Graphic">
        </section>
    </div>
</body>
</html>
