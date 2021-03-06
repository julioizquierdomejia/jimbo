<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PruebasModel;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\Item;
use App\Models\User;
use App\Models\UserTicket;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpParser\JsonDecoder;


class PaymentController extends Controller
{
    use ApiResponse;


    public function paymentCreate(Request $requet)
    {
        try {

            $data = request()->json()->all();

            $car = $data['car'];
            $price = $data['price'];
            $iduser = $data['iduser'];


            $user = User::where('id', $iduser)->get()->first();

            if ($user == null) {
                return $this->errorResponse('No se encontro el usuario' . ' ' . $user, 400);
            }

            foreach ($car as $key) {

                $raffle = Raffle::where('id', $key['raffle_id'])->get()->first();
                
                if ($raffle == null) {
                    return $this->errorResponse('No se encontro la rifa' . ' ' . $key['raffle_id'], 400);
                }
            }

            //************************************************************************************************************
            //
            // Author : {julio.izquierdo.mejia}
            //
            // Codigo recibo las compras Success y genera la cantidad de tikects comprados
            //
            //************************************************************************************************************

         

            $price = $price * 100;

            //$order_id = Str::uuid();
            $res =[];
            $trans =[];

            $order_id = Str::random(10);
            $username = "44623003";
            $password = "EP2x9duNcrlrK98x";


            if ($price) {

            } else {
                return $this->errorResponse('Algun valor en la api no se envio correctamente.', 400);
            }
            
            foreach ($car as $key) {
                $sell = new UserTicket();
                $sell->user_id = $iduser;
                $sell->raffles_id = $key['raffle_id'];
                $sell->quantity = $key['amount'];
                
                $sell->status = 'INITIALIZED';
                $sell->oreder_id = $order_id;
                $sell->save();

                // luego de que se registre la compra como registro unico en user ticket
                // vamos a registrar la cantidad de tickets que el usuario compro

                // 1ero vamos a obtener el costo de la rifa
                // para ello necesitamos el Id del Item

                $raffle = Raffle::where('id', $key['raffle_id'])->first();
                $item = Item::where('id', $raffle->item_id)->first();
                $precio = $item->price;
                $cantidad_tickets = $key['amount'] / $precio;

                
                for ($i=0; $i < $cantidad_tickets; $i++) { 
                    $ticket = new Ticket();
                    //$ticket->raffle_id = $raffle->id;
                    $ticket->raffle_id = $sell->id;
                    $ticket->quantity = 1;
                    $ticket->price = $precio;

                    $ticket->save();
                }
            }


        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse([
            'status' => 201,
            'message' => 'Proceder pago',
            //'data' => $res,
            'order_id' => $order_id,
        ]);
    }

    public function PyamentVerificated(Request $requet){
    /*
        $data = request()->all();
        $prueba = new PruebasModel();
            $prueba->content = json_encode($data) ;
        $prueba->save();
        return $this->successResponse([
            'status' => 201,
            'message' => 'Validaci??n completada',
        ]);*/
        try {
        $data = request()->all();

        if (isset($data['vads_trans_status'])  ) {


            $ticket = UserTicket::where('oreder_id',$data['vads_order_info'])->get();
            foreach ($ticket as $key ) {

                if ($key != null) {
                    if ($data['vads_trans_status']== 'AUTHORISED') {
                        $key->status == "Success";
                    }elseif ($data['vads_trans_status']== 'REFUSED') {
                        $key->status == "Refused";
                    }else {
                        $key->status == "Error";
                    }

                    $key->update();
                }
            }

            return $this->successResponse([
                'status' => 201,
                'message' => 'Validaci??n completada',
            ]);
        }else{
            return $this->errorResponse("No se encontraron los tickets a validar.", 400);
        }
    } catch (\Exception $exception) {
        return $this->errorResponse($exception->getMessage(), 400);
    }

    }



    public function PaymentValidate($order_id)
    {
        try{
            $ticket = UserTicket::where('oreder_id',$order_id)->get()->first();
            if ($ticket == null) {
                return $this->errorResponse("Orden no encontrada.", 400);
            }else{
                if ($ticket->status == 'INITIALIZED') {
                    return $this->errorResponse('Proceso no terminado.', 400);
                }
                elseif ($ticket->status == 'Success') {
                    return $this->successResponse([
                        'status' => 201,
                        'message' => $ticket->status,
                    ]);
                }else {
                    return $this->errorResponse('Proceso fallido.', 400);
                }

            }

        }catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }
    }

    public function curl($postfield)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://secure.micuentaweb.pe/vads-payment/entry.silentInit.a");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        return $server_output;
    }


}
