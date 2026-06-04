<?php
/**
 * CartelFetch - Enhanced M3U Fetcher
 */

$sources = [
    "playlist.m3u"   => "https://yowaimo.in/StreamFlexTv/master.php?name=SF9EEJVS&token=165561166922b8141128e14f",
    // Add more sources here
];

// Create a temp cookie file for session-based panels
$cookieFile = tempnam(sys_get_temp_dir(), 'cookies');

foreach ($sources as $fileName => $url) {
    echo "[*] Processing: $fileName\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increased timeout
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Handle compressed responses
    
    // Maintain cookies for redirects
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

    // Advanced Headers to mimic a real IPTV app
    curl_setopt($ch, CURLOPT_USERAGENT, 'OTTNavigator/1.6.5.1 (Linux;Android 11) ExoPlayerLib/2.14.2');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: */*',
        'Connection: keep-alive',
        'Accept-Language: en-US,en;q=0.9',
    ]);

    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    if (curl_errno($ch)) {
        echo "[-] cURL Error: " . curl_error($ch) . "\n";
    }

    curl_close($ch);

    if ($httpCode === 200 && !empty($content)) {
        // Validation Logic
        $isM3u = (stripos($content, '#EXTM3U') !== false);
        $isXml = (stripos($content, '<?xml') !== false);
        
        // Prevent saving "Login Failed" or "Expired" HTML pages
        if (($isM3u || $isXml) && strlen($content) > 200) {
            file_put_contents($fileName, $content);
            echo "[+] Success: Saved " . strlen($content) . " bytes to $fileName\n";
        } else {
            echo "[-] Error: Response received but content is not a valid Playlist/XML.\n";
            // Debug: print first 50 chars to see what the server sent
            echo "[Debug] Start of content: " . substr(strip_tags($content), 0, 50) . "...\n";
        }
    } else {
        echo "[-] Error: Failed to fetch $url (HTTP Status: $httpCode)\n";
    }
}

// Cleanup
if (file_exists($cookieFile)) unlink($cookieFile);
?>
