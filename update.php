<?php
/**
 * Configuration: Add your playlist or EPG links here.
 * Format: "Output_Filename.m3u" => "Source_URL"
 */
$sources = [
    "playlist.m3u"   => "https://server.lrl45.workers.dev/channel/raw?=m3u",
    "secondary.m3u"  => "https://example.com/another_playlist.m3u",
    "guide.xml"      => "https://example.com/epg.xml", // Example for EPG
];

foreach ($sources as $fileName => $url) {
    echo "Processing: $fileName from $url...\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Using the User-Agent you provided to bypass simple blocks
    curl_setopt($ch, CURLOPT_USERAGENT, 'OTTNavigator/1.6.5.1 (Linux;Android 11) ExoPlayerLib/2.14.2');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: */*',
        'Connection: keep-alive'
    ]);

    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($content)) {
        // Basic validation: Check if it's a playlist or XML
        $isM3u = (strpos($content, '#EXTM3U') !== false);
        $isXml = (strpos($content, '<?xml') !== false);

        if ($isM3u || $isXml || strlen($content) > 100) {
            file_put_contents($fileName, $content);
            echo "Successfully saved to $fileName\n";
        } else {
            echo "Warning: Content for $fileName looks invalid. Skipping.\n";
        }
    } else {
        echo "Error: Failed to fetch $url (HTTP $httpCode)\n";
    }
}
?>
