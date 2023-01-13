<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Template;
use Illuminate\Http\Request;
use Validator;

class SettingsController extends Controller {

  public function index(Request $request) {
    $items = Currency::all();
    $currencies = [];
    foreach ($items as $currency) {
      $currency->linking();
      $currencies[$currency->id] = $currency;
    }
    $items = Template::all();
    $templates = [];
    foreach ($items as $template) {
      $templates[$template->name] = $template;
    }
    return view('admin.settings', [
      'admin' => $request->user(),
      'platformCurrencyId' => Setting::find('platform_currency_id')->value[0],
      'displayCurrencyId' => Setting::find('display_currency_id')->value[0],
      'commission' => Setting::find('commission')->value[0],
      'emailVerificationTemplateId' => Setting::emailVerificationTemplateId(),
      'userRechargeTemplateId' => Setting::userRechargeEmailTemplateId(),
      'userWithdrawTemplateId' => Setting::userWithdrawEmailTemplateId(),
      'userCreditReceiveTemplateId' => Setting::userCreditReceiveEmailTemplateId(),
      'userIdentityConfirmTemplateId' => Setting::userIdentityConfirmEmailTemplateId(),
      'currencies' => $currencies,
      'templates' => $templates,
    ]);
  }

  public function edit(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required|in:platform_currency,display_currency,email_verification_template,user_recharge_template,user_withdraw_template,user_credit_receive_template,user_identity_confirm_template,commission',
      'value' => 'required',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' =>$validator->errors()]);
    }

    switch ($request->name) {
      case 'platform_currency':
        if (!Currency::find($request->value)) return $this->apiErrorResponse('Invalid Currency id');
        $setting = Setting::find('platform_currency_id');
        $setting->value = [$request->value];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing platform currency');
      case 'display_currency':
        if (!Currency::find($request->value)) return $this->apiErrorResponse('Invalid Currency id');
        $setting = Setting::find('display_currency_id');
        $setting->value = [$request->value];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing display currency');
      case 'email_verification_template':
        if (!Template::find($request->value)) return $this->apiErrorResponse('Invalid Template id');
        $setting = Setting::find('email_verification_template_id');
        $setting->value = [
          $request->value
        ];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing email verification template');
      case 'user_recharge_template':
        if (!Template::find($request->value)) return $this->apiErrorResponse('Invalid Template id');
        $setting = Setting::find('user_recharge_email_template_id');
        $setting->value = [
          $request->value
        ];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing user recharge template');
      case 'user_withdraw_template':
        if (!Template::find($request->value)) return $this->apiErrorResponse('Invalid Template id');
        $setting = Setting::find('user_withdraw_email_template_id');
        $setting->value = [
          $request->value
        ];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing user withdraw template');
      case 'user_credit_receive_template':
        if (!Template::find($request->value)) return $this->apiErrorResponse('Invalid Template id');
        $setting = Setting::find('user_credit_receive_email_template_id');
        $setting->value = [
          $request->value
        ];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing user credit receive template');
      case 'user_identity_confirm_template':
        if (!Template::find($request->value)) return $this->apiErrorResponse('Invalid Template id');
        $setting = Setting::find('user_identity_confirm_email_template_id');
        $setting->value = [
          $request->value
        ];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing user identity confirm template');
      case 'commission':
        try {
          floatVal($request->value);
        } catch (\Throwable $th) {
          return $this->apiErrorResponse('Invalid commission value');
        }
        $setting = Setting::find('commission');
        $setting->value = [floatVal($request->value)];
        $setting->save();
        return $this->apiSuccessResponse('Successfully edditing commission');
      default:
        return $this->apiErrorResponse('Some things worng');
    }

  }

}
