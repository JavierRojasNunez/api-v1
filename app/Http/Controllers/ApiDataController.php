<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\Models\Anounces;

class ApiDataController extends Controller
{

    protected $apiStatusCode = 1000;
    protected $message_;
    protected $url = '/public/anounces/';//declarar una constante o una variable de ambiente
    protected $urlExample = '/public/anounces/(user_id)/(imageName)';//declarar una constante o una variable de ambiente
    protected $adds = [];

    public function getAnounces(Request $request)
	{

        if (!$request->hasAny(['id', 'province', 'sort_by']))
        {

            $this->message_= 'Bad params';
            $this->apiStatusCode = 1004;

            return $this->setResponse(true);

        }

        if ($request->has('id'))
        {

            $id_ = $request->get('id');

            $adds = Anounces::find((int)$id_);
            $adds->load('user');
            $adds->load('imagen');

            $this->adds = $adds;
            return $this->setResponse(false);

        }
        elseif($request->has('province'))
        {

            if ($request->has('sort_by'))
            {
                $params = $request->get('sort_by');
                $params = explode(':', $params);
                dd($params);
            }


            $adds = Anounces::select([
                'id', 'user_id' ,'type_rent', 'price', 'cauntry_rent', 'available_date',
                'cauntry_rent', 'province_rent', 'city_rent', 'titulo', 'descripcion'
                ])->where('province_rent', $request->get('province'));

            $adds = $adds->paginate(10);

            if ($adds == null || !$adds || empty($adds) || @count($adds) == 0)
            {
                $this->apiStatusCode = 1004;
                $this->message_ = 'No data found';
                $this->adds = [];
                $error_ = true;
                return $this->setResponse($error_);
            }

            $adds->load('user');
            $adds->load('imagen');
            $this->adds = $adds;
            $error_ = false;
            return $this->setResponse($error_);

        }
        else
        {
            $adds = Anounces::select([
                'id', 'user_id' ,'type_rent', 'price', 'cauntry_rent', 'available_date',
                'cauntry_rent', 'province_rent', 'city_rent', 'titulo', 'descripcion'
                ])->paginate(10);
        }


        if ($adds == null || !$adds || empty($adds) || @count($adds) == 0)
        {
            return response()->json(['apiCode'=>1004, 'results' => 0 ], 200);
        }

        $adds->load('user');
        $adds->load('imagen');



	}

    public function setResponse($error = true)
    {
        if ($error)
        {
            return response()->json([
                'apiCode'=>$this->apiStatusCode,
                'message'=> $this->message_,
                'allowedParams'=> [
                    'id' =>'integer',
                    'province'=>'string',
                    'sort_by'=>'string | price:asc | price:desc'
                ],
                'results' => $this->adds,
            ], 200);
        }
        else
        {
            $this->urlExample = $_SERVER['HTTP_HOST'] . $this->urlExample;
            $this->url = $_SERVER['HTTP_HOST'] . $this->url;

            return response()->json([
                'apiCode'=>$this->apiStatusCode,
                'urlExample'=> $this->urlExample ,
                'imageUrl'=> $this->url,
                'results'=> $this->adds],
                 200);
        }

    }
}
