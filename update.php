<?php
/**
 * CartelFetch - Enhanced Stealth M3U Fetcher
 */

$sources = [
    "playlist.m3u" => "https://yowaimo.in/StreamFlexTv/master.php?name=SF9EEJVS&token=165561166922b8141128e14f",
];

foreach ($sources as $fileName => $url) {
    echo "[*] Fetching: $fileName\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Longer timeout for large lists
    
    // CRITICAL: This handles the GZIP compression many panels use
    curl_setopt($ch, CURLOPT_ENCODING, ""); 

    // CRITICAL: Mimicking a premium OTT Navigator app exactly
    $headers = [
        'User-Agent: OTTNavigator/1.7.1.2 (Linux; Android 11; M2007J20CG Build/RKQ1.200826.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/114.0.5735.196 Mobile Safari/537.36',
        'Accept: */*',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'X-Requested-With: com.loitp.ottnavigator',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($content)) {
        // Check if the content actually contains channels (#EXTINF)
        // If it only contains #EXTM3U, it failed to get the list.
        $hasChannels = (stripos($content, '#EXTINF') !== false);
        
        if ($hasChannels) {
            file_put_contents($fileName, $content);
            echo "[+] Success! Fetched " . substr_count($content, '#EXTINF') . " channels.\n";
            echo "[+] Saved to $fileName\n";
        } else {
            echo "[-] Error: Received Header but NO channels. The server is blocking the GitHub IP or requires a new token.\n";
            // Check if the server sent an error message in the text
            if (strlen($content) < 500) {
                echo "[Debug] Server Response: " . strip_tags($content) . "\n";
            }
        }
    } else {
        echo "[-] HTTP Error: $httpCode\n";
    }
}
?>
