<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mail;
use App\Models\Template;
use Illuminate\Http\Request;

class MailController extends Controller {

  public function __construct() {
    $this->middleware('multi.auth:admin');
  }

  public function getNextId() {
    $id = 0;
    $last = Mail::orderBy('id','desc')->first();
    if(!is_null($last)) {
      $id = $last->id;
    }
    return $id + 1;
  }

  static function all(Request $request) {
    $items = Mail::all();
    $mails = [];
    foreach ($items as $value) {
      $mails[$value->id] = $value;
    }
    $items = Template::all();
    $templates = [];
    foreach ($items as $value) {
      $templates[$value->id] = $value;
    }
    return Controller::apiSuccessResponse('Success', [
      'data' => [
        'mails' => $mails,
        'templates' => $templates,
      ],
    ]);
  }

  static function news(Request $request) {
    $admin_id = $request->user()->id;
    return [
      'count' => count(Mail::news($admin_id)) + count(Template::news($admin_id)),
    ];
  }

  static function readNews(Request $request) {
    Mail::readNews($request->user()->id);
    Template::readNews($request->user()->id);
    return Controller::apiSuccessResponse('successfully reading news');
  }
}
