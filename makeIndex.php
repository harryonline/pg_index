<?php

// Structure in which the index will be build
$index = array();
// The fields that will be extracted
$fields = array( 'creator', 'title', 'language' );
// Instances of required classes
$zip = new ZipArchive;
$xml = new DOMDocument;

// Get files in folder
$files = scandir( '.' );
foreach( $files as $file ) {
	// Index .epub files only
	if( preg_match( '/\.epub$/', $file )) {
		if ($zip->open( $file ) == TRUE) {
			// load container.xml in xml structure
			if( $xml->loadXML( $zip->getFromName( 'META-INF/container.xml' ))) {
				// Get full-path attribute of rootfile tag
				$path = $xml->getElementsByTagName( 'rootfile')->item(0)->getAttribute( 'full-path' );
				if( $xml->loadXML( $zip->getFromName( $path ))) {
					// Get node values from selected field tags
					foreach( $fields as $field ) {
						$index[$file][$field] = $xml->getElementsByTagName( $field )->item(0)->nodeValue;
					}
				}
			}
			$zip->close();
		}
	}
}
// Optional, do some sorting
// or not, just use search in browser

// Create <dl> for each file
$dl = '';
foreach( $index as $file => $info ) {
	$dl .= sprintf( '<dt><a href="%s">%s</a></dt><dd>%s: %s [%s]<dd>', 
		$file, $file, $info['creator'], $info['title'], $info['language'] );
}

// Barebone html document with <dl>
$title = 'Project Gutenberg index';
$html = <<<EOD
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>$title</title>
    </head>
    <body>
		<h1>$title</h1>
		<dl>$dl</dl>
    </body>
</html>
EOD;

file_put_contents( 'index.html', $html );

?>