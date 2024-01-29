<?php
// Default directory path
$rootDirectory = "."; // Replace with your root directory path
$currentDirectory = isset($_GET['dir']) ? $_GET['dir'] : $rootDirectory;

// Function to list directories and .prg files
function listDir($dir) {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_dir("$dir/$file")) {
                        echo "<a href='?dir=" . urlencode("$dir/$file") . "'>$file</a><br>";
                    } elseif (pathinfo($file, PATHINFO_EXTENSION) == "prg") {
                        echo "<a href='$dir/$file'>$file</a><br>";
                    }
                }
            }
            closedir($dh);
        }
    }
}

// List directories and .prg files in the current directory
listDir($currentDirectory);
?>
