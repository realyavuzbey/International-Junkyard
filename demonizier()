<?php

/**
 * Demonizier function to clean text based on strength and optionally search the web for related content.
 * 
 * @param string $text The input text to be cleaned.
 * @param int $strength The strength of the cleaning process (0-1000).
 * @param bool $search Whether to perform a web search for the input text.
 * @return string The cleaned text or web search results.
 */
function demonizier($text, $strength = 500, $search = false) {
    // Ensure strength is within the valid range
    $strength = max(0, min(1000, $strength));

    // Step 1: Basic HTML entity decoding
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // Step 2: Remove all HTML tags and special characters
    $text = strip_tags($text);

    // Step 3: Normalize Unicode characters
    $text = normalizer_normalize($text, Normalizer::FORM_C);

    // Step 4: Remove control characters
    $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

    // Step 5: Remove potential harmful scripts
    $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);
    $text = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $text);
    $text = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $text);
    $text = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $text);
    $text = preg_replace('#<applet(.*?)>(.*?)</applet>#is', '', $text);
    $text = preg_replace('#<meta(.*?)>#is', '', $text);
    $text = preg_replace('#<link(.*?)>#is', '', $text);
    $text = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $text);

    // Step 6: Escape special HTML characters
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Step 7: Remove excess whitespace
    $text = preg_replace('/\s+/', ' ', $text);

    // Step 8: Additional cleaning based on strength
    for ($i = 0; $i < $strength; $i++) {
        // Remove common offensive words (example list)
        $offensiveWords = ['badword1', 'badword2', 'badword3']; 
        $text = str_ireplace($offensiveWords, '', $text);

        // Remove special characters
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
    }

    // Optional: Perform web search
    if ($search) {
        $searchResults = performWebSearch($text);
        return $searchResults;
    }

    return $text;
}

/**
 * Function to perform a web search using Google Custom Search API.
 * 
 * @param string $query The search query.
 * @return string The search results.
 */
function performWebSearch($query) {
    $apiKey = 'YOUR_GOOGLE_API_KEY';
    $searchEngineId = 'YOUR_SEARCH_ENGINE_ID';
    $url = 'https://www.googleapis.com/customsearch/v1?q=' . urlencode($query) . '&key=' . $apiKey . '&cx=' . $searchEngineId;

    $response = file_get_contents($url);
    $results = json_decode($response, true);

    $output = '';
    if (isset($results['items'])) {
        foreach ($results['items'] as $item) {
            $output .= 'Title: ' . htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . "\n";
            $output .= 'Link: ' . htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') . "\n";
            $output .= 'Snippet: ' . htmlspecialchars($item['snippet'], ENT_QUOTES, 'UTF-8') . "\n\n";
        }
    }

    return $output;
}

// Example usage
$text = "Example text with <b>HTML</b> tags, <script>alert('bad')</script>, and offensive words like badword1.";
$cleanedText = demonizier($text, 700, true);
echo $cleanedText;

?>
