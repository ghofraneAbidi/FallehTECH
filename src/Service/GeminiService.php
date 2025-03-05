<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getRecipes(string $ingredient): array
{
    $prompt = "Give me 3 French recipes using $ingredient.
Each recipe should include:
- A direct image URL that is public and accessible without authentication.
- Ensure the image is hosted on a reliable source (e.g., unsplash.com, pexels.com, allrecipes.com).
- Return data as JSON:
[
   {
      'title': 'Recipe Title',
      'ingredients': 'List of ingredients.',
      'instructions': 'Recipe instructions.',
      'image': 'Valid image URL (ensure the URL is direct)'
   }
]";

    

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent?key=" . $this->apiKey;

    $response = $this->client->request('POST', $url, [
        'json' => [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ],
    ]);

    $data = $response->toArray();
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // ✅ Remove triple backticks & decode JSON properly
    $json = preg_replace('/^```json\n|\n```$/', '', $text);
    $recipes = json_decode($json, true) ?? [];
   

    
    if ($response->getStatusCode() !== 200) {
        $imageUrl = '/img/default.jpg'; // Fallback if CORS blocks it
    }
    

    return $recipes;
}



}
