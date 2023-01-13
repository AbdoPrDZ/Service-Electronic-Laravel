<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Template;
use Illuminate\Http\Request;
use Validator;

class TemplateController extends Controller {

  public function create(Request $request) {
    $request->merge(['args' => $this->tryDecodeArray($request->args)]);
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'type' => 'required|in:mail,export',
      'source_code' => 'required|string',
      'args' => 'required|array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    if(Template::where('name', '=', $request->name)->first()) {
      return $this->apiErrorResponse('This Name already used', [
        'errors' => [
          'name' => 'This Name already used',
        ]
      ]);
    }

    Template::create([
      'name' => $request->name,
      'type' => $request->type,
      'content' => $request->source_code,
      'args' => $request->args,
      'unreades' => Admin::unreades($request->user()->id),
    ]);

    return $this->apiSuccessResponse('Successfully creating template');
  }

  public function edit(Request $request, Template $template) {
    $request->merge(['args' => $this->tryDecodeArray($request->args)]);
    $validator = Validator::make($request->all(), [
      'type' => 'in:mail,export',
      'source_code' => 'string',
      'args' => 'array',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, [
        'errors' => $validator->errors(),
      ]);
    }

    $template->name = $request->name ?? $template->name;
    $template->type = $request->type ?? $template->type;
    $template->content = $request->source_code ?? $template->content;
    $template->args = $request->args ?? $template->args;
    $template->unreades = Admin::unreades($request->user()->id);
    $template->save();

    return $this->apiSuccessResponse('Successfully editing template');
  }

  public function delete(Request $request, Template $template) {
    $template->preDelete();
    return $this->apiSuccessResponse('Successfully deleting template');
  }

}
