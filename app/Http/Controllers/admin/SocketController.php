<?php

namespace App\Http\Controllers\Admin;

use App\Http\Middleware\MultiAuth;
use App\Http\Controllers\Controller;
use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\Notification;
use Illuminate\Http\Request;
use Log;

class SocketController extends Controller {

  public function onClientConnect($request, $client) {
    return [$request, $request->user()->id];
  }

  public function news(SocketClient $client) {
    $client->emit('news-updated', [
      'users' => UserController::news($client->clientId),
      'sellers' => SellerController::news($client->clientId),
      'transfers' => TransferController::news($client->clientId),
      'currencies' => CurrencyController::news($client->clientId),
      'products' => ProductController::news($client->clientId),
      'purchases' => PurchaseController::news($client->clientId),
      'offers' => OfferController::news($client->clientId),
      'mails' => MailController::news($client->clientId),
    ]);
    return $this->apiSuccessResponse('Successfully getting news');
  }

  public function readNews(SocketClient $client, $tabName) {
    $tabNames = [
      'users' => UserController::class,
      'sellers' => SellerController::class,
      'transfers' => TransferController::class,
      'currencies' => CurrencyController::class,
      'products' => ProductController::class,
      'purchases' => PurchaseController::class,
      'offers' => OfferController::class,
      'mails' => MailController::class,
    ];
    if(array_key_exists($tabName, $tabNames)) {
      // try {
        $res = $tabNames[$tabName]::readNews($client->clientId);
        if($res->getData()->success) {
          $client->emit('news-readed', ['tabName' => $tabName]);
        }
        return $res;
      // } catch (\Throwable $th) {
      //   print_r($th);
      //   return $this->apiErrorResponse("cannot read news of tabName [$tabName]");
      // }
    } else {
      return $this->apiErrorResponse("Invalid tab name [$tabName]");
    }
  }

}
