<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $apiKey = 'f9dc6fb51bae374ad0545e06d4c84c23'; // 🔴 Remplace par TA clé API OpenWeatherMap

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // 🔹 Affichage de la page météo
    #[Route('/weather', name: 'app_weather')]
    public function weather(): Response
    {
        return $this->render('post/weather.html.twig');
    }

    // 🔹 Récupération des données météo en JSON
    #[Route('/weather/data', name: 'app_weather_data')]
    public function fetchWeatherData(): JsonResponse
    {
        $cities = [
            ['name' => 'Tunis', 'lat' => 36.8065, 'lon' => 10.1815],
            ['name' => 'Sfax', 'lat' => 34.7390, 'lon' => 10.7603],
            ['name' => 'Sousse', 'lat' => 35.8256, 'lon' => 10.6084],
            ['name' => 'Gabès', 'lat' => 33.8833, 'lon' => 10.1167],
            ['name' => 'Bizerte', 'lat' => 37.2744, 'lon' => 9.8739],
            ['name' => 'Gafsa', 'lat' => 34.4250, 'lon' => 8.7842],
            ['name' => 'Kairouan', 'lat' => 35.6781, 'lon' => 10.0963],
            ['name' => 'Tozeur', 'lat' => 33.9197, 'lon' => 8.1336]
        ];

        $weatherData = [];

        foreach ($cities as $city) {
            $url = "https://api.openweathermap.org/data/2.5/weather?lat={$city['lat']}&lon={$city['lon']}&appid={$this->apiKey}&units=metric&lang=fr";
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            $weatherData[] = [
                'name' => $city['name'],
                'lat' => $city['lat'],
                'lon' => $city['lon'],
                'temp' => $data['main']['temp'],
                'weather' => $data['weather'][0]['description'],
                'icon' => $data['weather'][0]['icon']
            ];
        }

        return new JsonResponse($weatherData);
    }
}
