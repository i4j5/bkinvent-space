<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Curl\Curl;

class AvitoController extends Controller
{

    private $token;
   
    public function __construct()
    {
        $request = new Curl();

        $res = $request->get("https://api.avito.ru/token/?grant_type=client_credentials&client_id=" . env('AVITO_CLIENT_ID') . "&client_secret=" .env('AVITO_CLIENT_SECRET'));

        $this->token = $res->access_token;
    

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

        $id = $request->input('id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

        $request = new Curl('https://api.avito.ru');
        $request->setHeader('Authorization', 'Bearer ' . $this->token);
        $request->setHeader('Content-Type', 'application/json');
        $user_id = $request->get('/core/v1/accounts/self')->id;

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

        foreach ($res->result->items[0]->stats as $item) {
            $uniqViews += $item->uniqViews;
            $uniqContacts += $item->uniqContacts;
            $uniqFavorites += $item->uniqFavorites;
        }

        return [
            'uniqViews' => $uniqViews,
            'uniqContacts' => $uniqContacts,
            'uniqFavorites' => $uniqFavorites,
        ];
    }
}