<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\Mail;
use App\Models\Notification;
use App\Models\OfferRequest;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Template;
use App\Models\Transfer;
use App\Models\User;
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
      'platformCurrencyId' => Setting::platformCurrency()?->id,
      'displayCurrencyId' => Setting::displayCurrency()?->id,
      'commission' => Setting::commission(),
      'emailVerificationTemplateId' => Setting::emailVerificationTemplateId(),
      'userRechargeTemplateId' => Setting::userRechargeEmailTemplateId(),
      'userWithdrawTemplateId' => Setting::userWithdrawEmailTemplateId(),
      'userCreditReceiveTemplateId' => Setting::userCreditReceiveEmailTemplateId(),
      'userIdentityConfirmTemplateId' => Setting::userIdentityConfirmEmailTemplateId(),
      'servicesStatus' => Setting::servicesStatus(),
      'currencies' => $currencies,
      'templates' => $templates,
    ]);
  }

  public function edit(Request $request) {
    $validator = Validator::make($request->all(), [
      'name' => 'required|in:platform_currency,'.
                'display_currency,'.
                'email_verification_template,'.
                'user_recharge_template,'.
                'user_withdraw_template,'.
                'user_credit_receive_template,'.
                'user_identity_confirm_template,'.
                'commission,'.
                'services_status,'.
                'clear_data',
      'value' => 'required',
    ]);
    if ($validator->fails()) {
      return $this->apiErrorResponse(null, ['errors' => $validator->errors()]);
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
      case 'services_status':
        try {
          $statuses = json_decode($request->value);
        } catch (\Throwable $th) {
          return $this->apiErrorResponse('Invalid commission value');
        }
        $setting = Setting::find('services_status');
        $setting->value = $statuses;
        $setting->save();
        User::notify([
          'name' => 'service-status-updated',
          'title' => 'Serives status updated',
          'message' => "App services has been updated",
          'data' => [
            'event_name' => 'service-status-updated',
            'data' => json_encode([
              'services_status' => $statuses,
            ])
          ],
          'image_id' => 'logo',
          'type' => 'emitOrNotify',
        ]);
        return $this->apiSuccessResponse('Successfully edditing services status');
      case 'clear_data':
        switch ($request->value) {
          case 'transfers':
            Transfer::clearCache();
            Exchange::clearUsersExchanges();
            break;
          case 'messages':
            Notification::clearCache();
            break;
          case 'mails':
            Mail::clearCache();
            break;
          case 'purchases':
            Purchase::clearCache();
          case 'offers_requests':
            OfferRequest::clearCache();
            break;
          default:
            return $this->apiErrorResponse("Invalid target ($request->value)", ['value' => "Ivalid Target ($request->value)"]);
        }
        return $this->apiSuccessResponse('Successfully clearing data');
      default:
        return $this->apiErrorResponse('Some things worng', ['all' => $request->all()]);
    }

  }

}
