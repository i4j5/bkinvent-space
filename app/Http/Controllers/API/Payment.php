<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Payment extends Controller
{



    public function index(Request $request)
    {

        $deal_id = $request->query('deal_id') ? $request->query('deal_id') : 0;

        if ($deal_id) {
            return \App\Models\Payment::orderBy('date')->where('deal_id', $deal_id)->get()->toJson(JSON_PRETTY_PRINT);
        }

        return \App\Models\Payment::all()->toJson(JSON_PRETTY_PRINT);

    }


    /**
     * Создание платежа
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dd = \App\Models\Payment::create([
            'type' => $request->type,
            'deal_id' => $request->deal_id,
            'sum' => $request->sum,
            'date' => Carbon::createFromFormat('d.m.Y H:i', $request->date,),
            'description' => $request->description
        ]);
    }

    /**
     * Получить платежи сделки
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $dd = \App\Models\Payment::create([
        //     'type' => 123,
        //     'deal_id' => 123,
        //     'sum' => 123,
        //     'date' => Carbon::createFromFormat('d.m.Y H:i', '01.03.2021 12:50'),
        //     'description' => 123
        // ]);

        return \App\Models\Payment::find($id);

        //return \App\Models\Payment::orderBy('created_at')->where('deal_id', $deal_id)->get()->toJson(JSON_PRETTY_PRINT);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return \App\Models\Payment::destroy($id);
    }
}
