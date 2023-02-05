<?php

namespace App\Models;

use App\Events\SettingUpdatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Setting
 *
 * @property int $name
 * @property array $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereValue($value)
 * @mixin \Eloquent
 */
class Setting extends Model {

  use HasFactory;

  public $incrementing = false;

  protected $primaryKey = 'name';

  protected $fillable = [
    'name',
    'value',
  ];

  protected $casts = [
    'value' => 'array',
  ];

  protected $dispatchesEvents = [
    'updated' => SettingUpdatedEvent::class,
  ];

  /**
   * Getting platformCurrency
   * @return Currency
   */
  static function platformCurrency() {
    $currency = Currency::find(Setting::find('platform_currency_id')->value[0]);
    $currency->linking();
    return $currency;
  }

  /**
   * Getting emailVerificationTemplateId
   * @return string
   */
  static function emailVerificationTemplateId() {
    return Setting::find('email_verification_template_id')->value[0];
  }

  /**
   * Getting userRechargeEmailTemplateId
   * @return string
   */
  static function userRechargeEmailTemplateId() {
    return Setting::find('user_recharge_template_id')->value[0];
  }

  /**
   * Getting userWithdrawEmailTemplateId
   * @return string
   */
  static function userWithdrawEmailTemplateId() {
    return Setting::find('user_withdraw_template_id')->value[0];
  }

  /**
   * Getting userCreditReceiveEmailTemplateId
   * @return string
   */
  static function userCreditReceiveEmailTemplateId() {
    return Setting::find('user_credit_receive_template_id')->value[0];
  }

  /**
   * Getting userIdentityConfirmEmailTemplateId
   * @return string
   */
  static function userIdentityConfirmEmailTemplateId() {
    return Setting::find('user_identity_confirm_template_id')->value[0];
  }

  static function servicesStatus() {
    return Setting::find('services_status')->value;
  }

  static function serviceIsActive($service) {
    $servicesStatus = Setting::servicesStatus();
    return $servicesStatus[$service] == 'active';
  }

}
