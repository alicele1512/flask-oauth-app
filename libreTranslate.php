<?php
function libreTranslate($text, $source_lang, $target_lang) {
    $endpoint = 'https://libretranslate.de/translate';
    $data = array(
        'q' => $text,
        'source' => $source_lang,
        'target' => $target_lang,
        'format' => 'text'
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($endpoint, false, $context);

    if ($result === FALSE) {
        return $text; // Return original text if translation fails
    }

    $response = json_decode($result, true);
    return $response['translatedText'] ?? $text;
}
?>
