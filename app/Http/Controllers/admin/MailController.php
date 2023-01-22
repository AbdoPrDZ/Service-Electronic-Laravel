<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\SocketBridge\SocketClient;
use App\Models\Admin;
use App\Models\Mail;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;
use Log;
use Validator;

class MailController extends Controller {

  static function all(Request $request) {
    $items = Mail::all();
    $mails = [];
    foreach ($items as $item) {
      $item->linking();
      $mails[$item->id] = $item;
    }
    $items = Template::where('is_deleted', '=', 0)->get();
    $templates = [];
    foreach ($items as $item) {
      $templates[$item->name] = $item;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'mails' => $mails,
        'templates' => $templates,
      ],
    ]);
  }

  static function news($admin_id) {
    return count(Mail::news($admin_id)) + count(Template::news($admin_id));
  }

  static function readNews($admin_id) {
    Mail::readNews($admin_id);
    Template::readNews($admin_id);
  }

  public function create(Request $request) {
    $request->merge(['targets' => $this->tryDecodeArray($request->targets)]);
    $validator = Validator::make($request->all(), [
      'title' => 'required|string',
      'template_id' => 'required|integer',
      'data' => 'required|string',
      'targets' => 'required|array'
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
        'all' => $request->all(),
      ]);
    }

    $data = $this->tryDecodeArray($request->data);
    if(!$data) {
      return $this->apiErrorResponse('Invalid data', [
        'errors' => ['data' => 'Ivalid data'],
      ]);
    }

    $template = Template::find($request->template_id);
    if (!$template) {
      return $this->apiErrorResponse('Invalid template Id', [
        'errors' => ['template_id' => 'Invalid Template id']
      ]);
    }
    $emails = [];
    foreach ($request->targets as $target) {
      $user = User::find($target);
      if(!$user) {
        return $this->apiErrorResponse('Invalid target id', [
          'errors' => ['targets' => "Invalid target id ($target)"],
        ]);
      }
      $emails[] = $user->email;
    }

    Mail::create([
      'title' => $request->title,
      'template_id' => $template->id,
      'data' => $request->data,
      'targets' => $emails,
      'unreads' => Admin::unreades($request->user()->id),
    ]);

    return $this->apiSuccessResponse('Successfully sending emails');
  }

  public function deleteItems(Request $request) {
    $request->merge(['ids' => $this->tryDecodeArray($request->ids)]);
    $validator = Validator::make($request->all(), [
      'ids' => 'required|array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
        'all' => $request->all(),
      ]);
    }
    $ids = $request->ids;
    $admin = $request->user();
    if ($ids <= 10) {
      $this->deleteMails($admin, $ids);
    } else {
      async(function () use ($admin, $ids) {
        $this->deleteMails($admin, $ids);
      })->start()->catch(function (\Throwable $th) {
        Log::error($th);
      });
    }

    return $this->apiSuccessResponse('deleting mails in progress');

  }

  public function deleteMails($admin, $ids) {
    foreach ($ids as $id) {
      $mail = Mail::find($id);
      if ($mail) {
        $mail->delete();
      } else {
        Log::error("Undefined Mail Id $id");
      }
    }
    $client = new SocketClient($admin->id, 'admin');
    $client->emit('emails-deleted', []);
  }

}
