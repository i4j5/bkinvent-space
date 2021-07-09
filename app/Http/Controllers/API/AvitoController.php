<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Curl\Curl;
use Illuminate\Support\Facades\Storage;

class AvitoController extends Controller
{

    private $token;
   
    public function __construct()
    {

        $file_name = 'json/avito.json';

        if(!Storage::exists($file_name)) {
            Storage::put($file_name, '');
        }

        $json = json_decode(Storage::get($file_name), true);

        $time = time();

        if (!$json or ($time > $json['expires_in'])) {

            $request = new Curl();
            $res = $request->get("https://api.avito.ru/token/?grant_type=client_credentials&client_id=" . env('AVITO_CLIENT_ID') . "&client_secret=" .env('AVITO_CLIENT_SECRET'));
        
            $json['access_token'] = $res->access_token;
            $json['expires_in'] = $time + $res->expires_in;
            Storage::put($file_name, json_encode($json));
        }

        $this->token = $json['access_token'];

    }

    public function All(Request $request) {

        $intervals = $request->all()['intervals'];

        $request = new Curl('https://api.avito.ru');
        $request->setHeader('Authorization', 'Bearer ' . $this->token);
        $request->setHeader('Content-Type', 'application/json');

        $user_id = $request->get('/core/v1/accounts/self')->id;

        $items = [];
        $ids = [];

        for ($limit=100, $page=1, $run=true; $run; $page++) { 

            $res = $request->get('/core/v1/items', [
                'per_page' => $limit,
                'page' => $page
            ]);

            if ($res) {

                $arr = $res->resources;

                $run = !!(count($arr) == $limit);

                $items = array_merge($items, $arr);

            } else {
                $run = false;
            }
        }

        // dd($items);


        $data = [];
        foreach ($items as $item) {
    
            $stats = [];
            $id = $item->id;
            foreach ($intervals as $interval) {
                $res = $request->post("/stats/v1/accounts/$user_id/items", [
                    'itemIds' => [  $item->id ],
                    'dateFrom' => $interval['from'],
                    'dateTo' => $interval['to'],
                    'fields' => [
                        'uniqViews',
                        'uniqContacts',
                        'uniqFavorites'
                    ]
                ]);

                $uniqViews = 0;
                $uniqContacts = 0;
                $uniqFavorites = 0;

                if (isset($res->result) and isset($res->result->items)) {
                    foreach ($res->result->items[0]->stats as $stat) {
                        $uniqViews += $stat->uniqViews;
                        $uniqContacts += $stat->uniqContacts;
                        $uniqFavorites += $stat->uniqFavorites;
                    }
                }

                $stats[] = [
                    'interval' => $interval['from'] . ' - ' . $interval['to'],
                    'uniqViews' => $uniqViews,
                    'uniqContacts' => $uniqContacts,
                    'uniqFavorites' => $uniqFavorites,
                ];

                // sleep(1);
            }

            $data[] = [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'stats' => $stats,
            ];
        }

        return $data;

    }

    // Получить все объявления
    public function GetAds(Request $request) {

        $request = new Curl('https://api.avito.ru');
        $request->setHeader('Authorization', 'Bearer ' . $this->token);
        $request->setHeader('Content-Type', 'application/json');

        $items = [];

        for ($limit=100, $page=1, $run=true; $run; $page++) { 

            $res = $request->get('/core/v1/items', [
                'per_page' => $limit,
                'page' => $page
            ]);

            if ($res) {

                $arr = $res->resources;

                $run = !!(count($arr) == $limit);

                $items = array_merge($items, $arr);

            } else {
                $run = false;
            }
        }

        return $items;
    }


    public function AdStats(Request $request) {

        $id = (int) $request->input('id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

        $request = new Curl('https://api.avito.ru');
        $request->setHeader('Authorization', 'Bearer ' . $this->token);
        $request->setHeader('Content-Type', 'application/json');
        $user_id = $request->get('/core/v1/accounts/self')->id;

        sleep(1);

        $res = $request->post("/stats/v1/accounts/$user_id/items", [
            'itemIds' => [$id],
            'dateFrom' => $date_from,
            'dateTo' => $date_to,
            'fields' => [
                'uniqViews',
                'uniqContacts',
                'uniqFavorites'
            ]
        ]);

        $uniqViews = 0;
        $uniqContacts = 0;
        $uniqFavorites = 0;

        if (isset($res->result) and isset($res->result->items)) {
            foreach ($res->result->items[0]->stats as $item) {
                $uniqViews += $item->uniqViews;
                $uniqContacts += $item->uniqContacts;
                $uniqFavorites += $item->uniqFavorites;
            }
        }

        sleep(2);

        return [
            'uniqViews' => $uniqViews,
            'uniqContacts' => $uniqContacts,
            'uniqFavorites' => $uniqFavorites,
        ];
    }
}