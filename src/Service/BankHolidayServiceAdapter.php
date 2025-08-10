<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class BankHolidayServiceAdapter
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiUrl
    ) {
    }

    public function fetchHolidays(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl);
            
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new RuntimeException(
                    sprintf('Bank holiday API returned status %d: %s', 
                        $response->getStatusCode(), 
                        $response->getContent(false)
                    )
                );
            }
            
            $data = $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Failed to connect to bank holiday API: ' . $e->getMessage(), 0, $e);
        } catch (DecodingExceptionInterface $e) {
            throw new RuntimeException('Invalid JSON response from bank holiday API: ' . $e->getMessage(), 0, $e);
        }
        
        if (!isset($data['england-and-wales'])) {
            throw new RuntimeException('Bank holiday API response missing england-and-wales section');
        }
        
        if (!isset($data['england-and-wales']['events'])) {
            throw new RuntimeException('Bank holiday API response missing events in england-and-wales section');
        }
        
        $englandWales = $data['england-and-wales']['events'];
        
        if (!is_array($englandWales) || empty($englandWales)) {
            throw new RuntimeException('Bank holiday API returned empty or invalid events array for england-and-wales');
        }
        
        $holidays = [];
        foreach ($englandWales as $event) {
            if (!isset($event['date']) || !isset($event['title'])) {
                throw new RuntimeException('Bank holiday event missing required date or title field');
            }
            
            $holidays[] = [
                'date' => $event['date'],
                'title' => $event['title'],
            ];
        }
        
        return $holidays;
    }
}
