<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PruebasModel;
use App\Models\Raffle;
use App\Models\Ticket;
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

            $price = $price * 100;

            $order_id = Str::uuid();
            $username = "44623003";
            $password = "2fKXe4j8dmS6n8hy";




            if ($price) {
                http_response_code(200);
                $key = $password;  //LLAVE DEL COMERCIO
                $vads_action_mode = "INTERACTIVE";
                $vads_amount = $price;
                $vads_ctx_mode = "TEST";
                $vads_currency = "604";
                $vads_cust_email = $user->email;
                $vads_page_action = "ASK_REGISTER_PAY";
                $vads_payment_config = "SINGLE";
                $vads_site_id = $username;  // IDENTIFICADOR DEL COMERCIO
                $vads_trans_date = gmdate("YmdGis");
                $vads_trans_id = rand(100000, 999999);
                $vads_version = "V2";
                $vads_order_info  = $order_id;
                $vads_url_success = "http://127.0.0.1:8080/api/verify";
                $vads_url_refused = "http://127.0.0.1:8080/api/refused";
                $vads_url_cancel = "http://127.0.0.1:8080/api/cancel";
                $vads_url_error = "http://127.0.0.1:8080/api/error";
                $signature = "";
                $postfield = "";
                $parameters_args = array(
                    'vads_action_mode' => $vads_action_mode,
                    'vads_amount' => $vads_amount,
                    'vads_ctx_mode' => $vads_ctx_mode,
                    'vads_currency' => $vads_currency,
                    'vads_cust_email' => $vads_cust_email,
                    'vads_order_info' => $vads_order_info,
                    'vads_page_action' => $vads_page_action,
                    'vads_payment_config' => $vads_payment_config,
                    'vads_site_id' => $vads_site_id,
                    'vads_trans_date' => $vads_trans_date,
                    'vads_trans_id' => $vads_trans_id,
                    'vads_url_cancel' => $vads_url_cancel,
                    'vads_url_error' => $vads_url_error,
                    'vads_url_refused' => $vads_url_refused,
                    'vads_url_success' => $vads_url_success,
                    'vads_version' => $vads_version
                );
                foreach ($parameters_args as $params => $value) {
                    $signature .= $value . '+';
                    $postfield .= $params . "=" . $value . "&";
                }
                $signature = $signature . $key;
                $signature = base64_encode(hash_hmac('sha256', $signature, $key, true));
                $parameters_args['signature'] = $signature;
                $postfield = $postfield . "signature=" . $signature;
                $response = $this->curl(utf8_decode($postfield));
                $res = json_decode($response) ;
                /* echo $response; */
            } else {
                return $this->errorResponse('Algun valor en la api no se envio correctamente.', 400);
            }
            if ($res->status != "ERROR") {
                foreach ($car as $key) {

                    $sell = new UserTicket();
                    $sell->user_id = $iduser;
                    $sell->raffles_id = $key['raffle_id'];
                    $sell->quantity = $key['amount'];
                    if ($res->status == "ERROR") {
                        $sell->status = 'failed';
                        $sell->oreder_id = $order_id;
                        $sell->save();
                    } else {
                        $sell->status = 'INITIALIZED';
                        $sell->oreder_id = $order_id;
                        $sell->save();
                    }
                }
            }else{
                return $this->successResponse([
                    'status' => 401,
                    'message' => 'Error en la pasarela.',
                    'data' => $res,
                ]);
            }


        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse([
            'status' => 201,
            'message' => 'Registro de pago completo',
            'data' => $res,
        ]);
    }

    public function PyamentValidate(){

        $data = request()->json()->all();

        $prueba = new PruebasModel();
        $prueba->content = $data;
        $prueba->save();

        /* try {
            $data = request()->json()->all();

            if (isset($data['order_id'])) {


                $ticket = UserTicket::where('order_id',$data['order_id'])->get();
                foreach ($ticket as $key ) {

                    if ($key != null) {

                        $key->status == "Success";
                        $key->update();
                    }
                }

                return $this->successResponse([
                    'status' => 201,
                    'message' => 'Validación completada',
                ]);
            }else{
                return $this->errorResponse("No se encontraron los tickets a validar.", 400);
            }
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        } */

    }
    public function Pyamentrefused(){
        $data = request()->json()->all();

        $prueba = new PruebasModel();
        $prueba->content = $data;
        $prueba->save();

        /* try {
            $data = request()->json()->all();

            if (isset($data['order_id'])) {


                $ticket = UserTicket::where('order_id',$data['order_id'])->get();
                foreach ($ticket as $key ) {

                    if ($key != null) {

                        $key->status == "Refused";
                        $key->update();
                    }
                }

                return $this->successResponse([
                    'status' => 201,
                    'message' => 'Validación completada',
                ]);
            }else{
                return $this->errorResponse("No se encontraron los tickets a validar.", 400);
            }
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }
 */
    }
    public function Pyamentcancel(){
        $data = request()->json()->all();

        $prueba = new PruebasModel();
        $prueba->content = $data;
        $prueba->save();

        /* try {
            $data = request()->json()->all();

            if (isset($data['order_id'])) {


                $ticket = UserTicket::where('order_id',$data['order_id'])->get();
                foreach ($ticket as $key ) {

                    if ($key != null) {

                        $key->status == "Cancel";
                        $key->update();
                    }
                }

                return $this->successResponse([
                    'status' => 201,
                    'message' => 'Validación completada',
                ]);
            }else{
                return $this->errorResponse("No se encontraron los tickets a validar.", 400);
            }
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        } */

    }
    public function Pyamenterror(){
        $data = request()->json()->all();

        $prueba = new PruebasModel();
        $prueba->content = $data;
        $prueba->save();

        /* try {
            $data = request()->json()->all();

            if (isset($data['order_id'])) {


                $ticket = UserTicket::where('order_id',$data['order_id'])->get();
                foreach ($ticket as $key ) {

                    if ($key != null) {

                        $key->status == "Error";
                        $key->update();
                    }
                }

                return $this->successResponse([
                    'status' => 201,
                    'message' => 'Validación completada',
                ]);
            }else{
                return $this->errorResponse("No se encontraron los tickets a validar.", 400);
            }
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }
 */
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
