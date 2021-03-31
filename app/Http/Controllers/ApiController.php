<?php

namespace App\Http\Controllers;

use App\Models\Anounces;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\FuncCall;

class ApiController extends Controller
{

    protected $data;
    public $HttpstatusCode = 200;
    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */


  


     public function create(Request $request){

        
        if ($request->isJson())
        {
                            
            $data = $request->all();

            $dataToBeSaved = $data['data'][0];
            
            $userExists = User::where("id", $dataToBeSaved['user_id'])->exists();
            
            if (!$userExists){

                $anuncio = false;

                return response()->json(['status'=>'Not created. Bad user credentials', $anuncio], 400);

            }else{

                $anuncio = Anounces::create($dataToBeSaved);

                return response()->json(['status'=>'Created.', $anuncio], 201);

            }           

        }else{
            return response()->json(['error' => 'No valid JSON'], 406);
        }

     }

	public function getAll()
	{

       /*$data = Anounces::get()->each(function($data){

            $data->user;
            $data->imagen;
            $data->imageUrl = $_SERVER['HTTP_HOST'] . '/public/anounces/'. $data->user->id . '/';
            
        });*/
          
       /* $data = DB::table('anounces')
                ->offset($offSet)
                ->limit($limit)
                ->get();*/

        $data = [];
        $data = Anounces::paginate(10);
        $data->load('user');
        $data->load('imagen'); 
        $url = $_SERVER['HTTP_HOST']. '/public/anounces/';
        $urlExample =  $_SERVER['HTTP_HOST']. '/public/anounces/[user_id]/[imageName]/';
        $numAdds = count($data);

        if ($numAdds == 0){

            
            return response()->json(['status'=> 'No data found.', 'results' => $numAdds ], 204);

        }
       

        /*foreach($data as $anuncio){
            
            $user = User::where('id', '=', $anuncio->user_id)->first();

            if($user){
             
                $anuncio->userData[] = [
                    'userName' => $user->name,
                    'userSurname' => $user->surname,
                    'userEmail' => $user->email,
                ];   

                unset($anuncio->user_id);
                
            }
        }*/
        
        return response()->json(['status'=>'ok', 'urlExample'=> $urlExample , 'imageUrl'=> $url , 'results' => $numAdds ,'collection'=>$data], $this->HttpstatusCode);

	}



    public function getOne($id = false){

        
        if(!$id){

            return response()->json(['error'=>'Parameter passed 0 expects exactly 1 | integer'], 406); 
        }

        $data = Anounces::find((integer)$id);
            
        if($data == NULL){
            
            return response()->json(['status'=>'Not data found'], 200);   
                
        }
        
        $data->user;
        $data->imagen;
        $data->imageUrlExample = $_SERVER['HTTP_HOST'] . '/public/anounces/' . $data->user->id . '/[imageName]';
        $data->imageUrl = $_SERVER['HTTP_HOST'] . '/public/anounces/' . $data->user->id . '/';
        $data->currency = '€';
        unset($data->user_id);
        unset($data->user->id);
        unset($data->id);
        for($i = 0; $i < count($data->imagen); $i++){
            unset($data->imagen[$i]->id);
            unset($data->imagen[$i]->anounces_id); 
        }
        
        return response()->json(['status'=>'Data found','data'=>$data], $this->HttpstatusCode);

    }



    public function getBasics($id_ = false){

        $data = [];

        if ( !is_numeric($id_) && $id_  ){
            return response()->json(['error'=>'Parameters must to be integers'], 406);   
        }

        if($id_){
            $data = DB::table('anounces')
            ->select(['titulo', 'descripcion as description', 'cauntry_rent as country', 'province_rent as province', 'city_rent as city', 'phone'])
            ->where('id' , '=', (integer)$id_)
            ->get();
        }else{
           $data = DB::table('anounces')
           ->select(['titulo as title', 'descripcion as description', 'cauntry_rent as country', 'province_rent as province', 'city_rent as city', 'phone'])
           ->paginate(10); 
        }

        $numData = count($data);
        if($numData < 1){

            return response()->json(['status'=>'No data found'], 200);   
                
        }

        return response()->json(['status'=>'Data found','data'=> $data], $this->HttpstatusCode);

    }


    //sepuede pasar dos parametros separados por espacio para tene primero limite y segundo offset
   // vamos a poner dos variablesa recibir por la funcion
    public function getResumeWithImages($limit_ = 1000, $id_ = false){

        $limit_ = ($limit_ > 5000) ? 5000 : $limit_;

        $offSet = false;

        $limits = explode(' ', $limit_);

           if( $limits[0] == 'id' && $id_ ){

            $limit = false;
            $offSet = false;

            
        }else{

           
            if ((isset($limits[0] ) && !is_numeric($limits[0])) ||  ( isset($limits[1]) && !is_numeric($limits[1]) ) ){
                return response()->json(['error'=>'Parameters must to be integers'], 406);
            }
            
            $limit = (integer)$limits[0] ;
            
            $limit = ($limit == 0) ? $limit = 10 : (integer)$limit;
            
            $offSet = isset($limits[1]) ? (integer)$limits[1] : 0;

        }


        $dataAnounce = [];
        //$dataImages = [];
        $data = [];     

        
        $dataAnounce = DB::table('anounces')
        ->select([ 'id as reference', 'user_id', 'type_rent', 'price',  'payment_period', 
        'meter2', 'num_rooms', 'cauntry_rent', 'province_rent', 'phone', 'city_rent', 
        'street_rent as type_street', 'adress_rent', 'num_street_rent', 'flat_street_rent',
        'cp_rent as ZIP code', 'observations']);
                                              
        if ($id_) $dataAnounce->where('id', '=', $id_);
                
        if ($limit) $dataAnounce->orderBy('id', 'desc');

        if ($offSet) $dataAnounce->offset($offSet);

        if ($limit) $dataAnounce = $dataAnounce->limit($limit);
        
         $dataAnounce = $dataAnounce->get();                          

        $totalResults = count($dataAnounce);



        
       /* foreach ( $dataAnounce as  $anounce ){

            $anounce->imagen; 
            $imageUrl = $_SERVER['HTTP_HOST'] . '/public/' . $anounce->user_id . '/' ;
            $anounce->image_url = $imageUrl;
            unset ($anounce->user_id);
        }  */


        if ($totalResults < 1){
            

            return response()->json(['status'=>'No data found'], $this->HttpstatusCode);
            
        }  
        
        $url = $_SERVER['HTTP_HOST'];
        foreach ( $dataAnounce as  $anounce ){
            
                  $dataImages =  DB::table('images')
                          ->where('anounces_id', '=', $anounce->reference)
                          ->select(['user_id', 'imageName', 'created_at', 'updated_at'])
                          ->get(); 
                  
                  $dataUser =  DB::table('users')
                          ->where('id', '=', $anounce->user_id)
                          ->select(['name', 'surname', 'email'])
                          ->get(); 
      
                 foreach($dataImages as $image){
                     //dd($image);
                     $image->imageName = $url . '/alquilados/public/anounces/' . $image->user_id. '/' . $image->imageName;
                 }       
                          
                 
                  
                  $anounce->currency = '€';
                  $anounce->userData = $dataUser;
                  $anounce->images = $dataImages;      
                  $anounce->imageUrl = $_SERVER['HTTP_HOST'] . '/public/anounces/' . $anounce->user_id . '/' ;       
                  unset($anounce->user_id);  
                        
              } 
        return response()->json(['status'=>'200', 'totalResults'=>$totalResults , 'anuncios'=>$dataAnounce], $this->HttpstatusCode);

    }


    public function getBy($arga = false, $argb = false, $argc = false){

        

        if ($arga && !$argb && !$argc){
           
            $dataAnounce = Anounces::find((integer)$arga);
            
            $url = $_SERVER['SERVER_NAME'] . '/public';

            
            if (!$dataAnounce){

                $data = false;
                return response()->json(['status'=>'204','data'=>$data], 204);
                
            } 
             
            $dataAnounce->imagen;     
            
            return response()->json(['status'=>'200', 'url'=>$url ,'anuncio'=>$dataAnounce], $this->HttpstatusCode);
            
        }

        if ($arga && $argb && !$argc){
            
           $url = $_SERVER['SERVER_NAME'] . '/public';
 
            if (!empty($arga)){
             
                if($arga == 'funiture' ){
                    if($argb == 'yes') {
                        $argb = true;
                    }else{
                        $argb = false;
                    }
                }
                
           
                $dataAnounce = Anounces::where($arga, '=', $argb)->paginate(10);

                
                if (count($dataAnounce) == 0){

                    $data = false;
                    return response()->json(['status'=>'No data found','data'=>$data], 204);
                    
                }  
                
                foreach ($dataAnounce as $anounce){
                   $anounce->imagen; 
                   $anounce->url = $url . '/public/' .$anounce->user_id . '/';
                }               
                
            }
                           
    
            return response()->json(['status'=>'200', 'url'=>$url ,'anuncio'=> $dataAnounce], $this->HttpstatusCode);
            
        }

        if ($arga && $argb && $argc){

            $url = $_SERVER['SERVER_NAME'];

            if ($arga == 'price'){
                
                $dataAnounce = Anounces::whereBetween('price', [(float)$argb, (float)$argc])->paginate(10);

                foreach ($dataAnounce as $anounce){
                   $anounce->imagen; 
                   $anounce->urlImageExample = basename(public_path()) . '/anounces/' .$anounce->user_id . '/[imageName]';
                   $anounce->urlImage = $url . '/public/anounces/' .$anounce->user_id . '/';
                                     
                   foreach($anounce->imagen as $imagen){
                       $imagen->imagenUrl = $url . '/alquilados/public/anounces/' .$anounce->user_id . '/' . $imagen->imageName;
                       unset($imagen->id);
                       unset($imagen->anounces_id);
                   }
                   unset($anounce->id);
                   unset($anounce->user_id);
                }               
                
            }
            
            

            
            if (!$dataAnounce){

                $data = false;
                return response()->json(['status'=>'204','data'=>$data], 204);
                
            }  
                
    
            return response()->json(['status'=>'200' ,'anuncio'=>$dataAnounce], $this->HttpstatusCode);
            
        }


    }


}
