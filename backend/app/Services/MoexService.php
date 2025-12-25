<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MoexService
{
    private const MOEX_API_BASE_URL = 'https://iss.moex.com/iss';
    private const CACHE_TTL = 60;
    private Client $client;
    private array $cache = [];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 15,
            'verify' => true
        ]);
    }

    private function remember(string $key, int $ttl, callable $callback)
    {
        if (isset($this->cache[$key])) {
            $cached = $this->cache[$key];
            if (time() - $cached['time'] < $ttl) {
                return $cached['data'];
            }
        }

        $data = $callback();
        $this->cache[$key] = [
            'data' => $data,
            'time' => time()
        ];

        return $data;
    }

    public function getStocks(): array
    {
        return $this->remember('moex_stocks', self::CACHE_TTL, function () {
            try {
                $response = $this->client->get(self::MOEX_API_BASE_URL . '/engines/stock/markets/shares/boards/TQBR/securities.json', [
                    'query' => [
                        'iss.meta' => 'off',
                        'securities.columns' => 'SECID,SECNAME,SHORTNAME,BOARDID',
                        'marketdata.columns' => 'SECID,LAST,CHANGE,OPEN,HIGH,LOW,VOLUME,PREVPRICE'
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (!$data) {
                    return [];
                }

                $securitiesColumns = $data['securities']['columns'] ?? [];
                $securitiesData = $data['securities']['data'] ?? [];
                $marketdataColumns = $data['marketdata']['columns'] ?? [];
                $marketdataData = $data['marketdata']['data'] ?? [];

                $secidIndex = array_search('SECID', $securitiesColumns);
                $secnameIndex = array_search('SECNAME', $securitiesColumns);
                $shortnameIndex = array_search('SHORTNAME', $securitiesColumns);
                $boardidIndex = array_search('BOARDID', $securitiesColumns);

                $mdSecidIndex = array_search('SECID', $marketdataColumns);
                $lastIndex = array_search('LAST', $marketdataColumns);
                $changeIndex = array_search('CHANGE', $marketdataColumns);
                $openIndex = array_search('OPEN', $marketdataColumns);
                $highIndex = array_search('HIGH', $marketdataColumns);
                $lowIndex = array_search('LOW', $marketdataColumns);
                $volumeIndex = array_search('VOLUME', $marketdataColumns);
                $prevpriceIndex = array_search('PREVPRICE', $marketdataColumns);

                $quotes = [];
                foreach ($marketdataData as $quote) {
                    if (isset($quote[$mdSecidIndex])) {
                        $quotes[$quote[$mdSecidIndex]] = $quote;
                    }
                }

                $stocks = [];
                foreach ($securitiesData as $security) {
                    $secid = $security[$secidIndex] ?? null;
                    if (!$secid) continue;

                    $quote = $quotes[$secid] ?? null;
                    if (!$quote) continue;

                    $last = $quote[$lastIndex] ?? null;
                    if ($last === null) continue;

                    $secname = $security[$secnameIndex] ?? '';
                    $shortname = $security[$shortnameIndex] ?? '';
                    $boardid = $security[$boardidIndex] ?? '';

                    $last = (float)$last;
                    $change = (float)($quote[$changeIndex] ?? 0);
                    $open = (float)($quote[$openIndex] ?? 0);
                    $high = (float)($quote[$highIndex] ?? 0);
                    $low = (float)($quote[$lowIndex] ?? 0);
                    $volume = (float)($quote[$volumeIndex] ?? 0);
                    $prevprice = (float)($quote[$prevpriceIndex] ?? 0);

                    $changePercent = $prevprice > 0 ? ($change / $prevprice) * 100 : 0;

                    $stocks[] = [
                        'secid' => $secid,
                        'name' => $secname,
                        'shortname' => $shortname,
                        'boardid' => $boardid,
                        'last' => $last,
                        'change' => $change,
                        'changePercent' => round($changePercent, 2),
                        'open' => $open,
                        'high' => $high,
                        'low' => $low,
                        'volume' => $volume,
                        'prevprice' => $prevprice,
                    ];
                }

                usort($stocks, function ($a, $b) {
                    return $b['volume'] <=> $a['volume'];
                });

                return $stocks;
            } catch (\Exception $e) {
                error_log('Error fetching MOEX stocks: ' . $e->getMessage());
                return [];
            }
        });
    }

    public function getStockDetails(string $secid): ?array
    {
        try {
            $response = $this->client->get(self::MOEX_API_BASE_URL . '/engines/stock/markets/shares/boards/TQBR/securities/' . $secid . '.json', [
                'query' => [
                    'iss.meta' => 'off'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        } catch (\Exception $e) {
            error_log('Error fetching MOEX stock details: ' . $e->getMessage());
            return null;
        }
    }

    public function getStockHistory(string $secid, int $days = 30): array
    {
        $cacheKey = "moex_stock_history_{$secid}_{$days}";

        return $this->remember($cacheKey, 300, function () use ($secid, $days) {
            try {
                $from = date('Y-m-d', strtotime("-{$days} days"));
                $till = date('Y-m-d');

                $response = $this->client->get(self::MOEX_API_BASE_URL . '/engines/stock/markets/shares/securities/' . $secid . '/candles.json', [
                    'query' => [
                        'iss.meta' => 'off',
                        'from' => $from,
                        'till' => $till,
                        'interval' => 24,
                        'candles.columns' => 'begin,open,close,high,low,value,volume'
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $candlesData = $data['candles']['data'] ?? [];
                $candlesColumns = $data['candles']['columns'] ?? [];

                $beginIndex = array_search('begin', $candlesColumns);
                $openIndex = array_search('open', $candlesColumns);
                $closePricesIndex = array_search('close', $candlesColumns);
                $highIndex = array_search('high', $candlesColumns);
                $lowIndex = array_search('low', $candlesColumns);
                $valueIndex = array_search('value', $candlesColumns);
                $volumeIndex = array_search('volume', $candlesColumns);

                $history = [];
                foreach ($candlesData as $candle) {
                    if (!isset($candle[$beginIndex]) || !isset($candle[$closePricesIndex])) {
                        continue;
                    }

                    $begin = $candle[$beginIndex];
                    $open = (float)($candle[$openIndex] ?? 0);
                    $closePrices = (float)($candle[$closePricesIndex] ?? 0);
                    $high = (float)($candle[$highIndex] ?? 0);
                    $low = (float)($candle[$lowIndex] ?? 0);
                    $volume = (float)($candle[$volumeIndex] ?? 0);

                    $timestamp = strtotime($begin) * 1000;

                    $history[] = [
                        'x' => $timestamp,
                        'y' => [$open, $high, $low, $closePrices],
                        'volume' => $volume,
                        'date' => $begin
                    ];
                }

                usort($history, function ($a, $b) {
                    return $a['x'] <=> $b['x'];
                });

                return $history;
            } catch (\Exception $e) {
                error_log('Error fetching MOEX stock history: ' . $e->getMessage());
                return [];
            }
        });
    }

    public function getStockMiniHistory(string $secid): array
    {
        return $this->getStockHistory($secid, 7);
    }

}
