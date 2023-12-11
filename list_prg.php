<?php
$directory = "."; // Replace with your directory path

// Open a directory, and read its contents
if (is_dir($directory)){
  if ($dh = opendir($directory)){
    while (($file = readdir($dh)) !== false){
      if (pathinfo($file, PATHINFO_EXTENSION) == "prg") {
        echo "<a href='$directory/$file'>$file</a><br>";
      }
    }
    closedir($dh);
  }
}
?>
