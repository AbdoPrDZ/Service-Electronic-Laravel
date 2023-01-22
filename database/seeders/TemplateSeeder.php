<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Template;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $appUrl = env('APP_URL');
    $emailVerificationContent = <<<HTML
      <div style="
        font: 15px cursive;
        background-image: linear-gradient(315deg, #12a5ff, #51d456);
        width: 100%;
        height: -webkit-fill-available;
        padding: 42px 0px;
        color: rgb(42, 42, 42);">
      <div style="display: grid;">
      <div style="
            width: fit-content;
            display: inline-flex;
            align-items: center;
            margin: auto;
            gap: 1rem;">
        <img src="$appUrl/storage/defaults/logo.png" style="width: 8.5vw;">
        <h2 style="color:#f1f1f1">Service Electronic</h2>
      </div>
      <div style="
            width: 70vw;
            margin: 30px auto;
            background-color: #ffffffed;
            border-radius: 5px;
            padding: 20px 30px;">
        <h1 style="font-size: 24px; margin: 0.5rem 2rem 1rem 0;">
          Email Verification
        </h1>
        <div>
          <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
          <div style="margin: 10px 20px;">
            Hi <-user-><br>
            Welcome to Services electronic<br>
            Use the information below to access your account.<br>
            <div style="
                  font-weight: bold;
                  font-size: 13px;
                  margin: 10px 0 10px 10px;
                  display: flex;
                  align-items: center;">
              Code verification: <span style="font-weight: normal; margin-left: 10px; color: #ffb700; font-size: 30px;"><-code-></span><br>
            </div>
            Security reminderTo keep your money safe, use a unique password and don't share your account login details with anyone. service.electroniccustomer support agents may ask you about your customer ID or email address, but will never ask you about your password. When logging in from your browser, make sure you are on before entering your password.
          </div>
          <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
        </div>
        <p style="
              width: fit-content;
              margin: 15px auto;
              font-size: 12px;
              text-align: center;">
          Service Electronic<br>Powred by Abdo Pr
        </p>
      </div>
      <div style="display: grid;">
        <div style="
                width: fit-content;
                display: inline-flex;
                align-items: center;
                margin: auto;">
          <a href="https://t.me/+pwKpK4YhXHEyYjRk?fbclid=IwAR3lWzl_bZtLedJU4pbkLNgt42G-bEVDSAYrvG9o1GK_vWV0Rxwn-Vz0xsY"
              style="
              display: grid;
              margin: 0 10px;
              text-decoration: none;
              color: #282828;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Telegram_Messenger.png/1020px-Telegram_Messenger.png"
                  style="
                  background-color: #fff;
                  border-radius: 50px;
                  padding: 3px;
                  margin: auto;
                  width: 6vw;">
            <h2 style="font-size: 16px;">Telegram</h2>
          </a>
          <a href="https://www.facebook.com/ChihabR94"
              style="
              display: grid;
              margin: 0 10px;
              text-decoration: none;
              color: #282828;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1024px-Facebook_Logo_%282019%29.png"
                  style="
                  background-color: #fff;
                  border-radius: 50px;
                  padding: 3px;
                  margin: auto;
                  width: 6vw;">
            <h2 style="font-size: 16px;">Facebook</h2>
          </a>
        </div>
      </div>
      </div>
      </div>
    HTML;
    Template::create([
      'name' => 'email-verification',
      'content' => $emailVerificationContent,
      'args' => [
        ["name" => '<-user->', "type" => "text"],
        ["name" => '<-code->', "type" => "number"],
      ],
      'type' => 'mail',
      'unreades' => Admin::unreades(),
    ]);
    $userRechargeContent = <<<HTML
      <div style="
          font: 15px cursive;
          background-image: linear-gradient(315deg, #12a5ff, #51d456);
          width: 100%;
          height: -webkit-fill-available;
          padding: 42px 0px;
          color: rgb(42, 42, 42);">
      <div style="display: grid;">
        <div style="
              width: fit-content;
              display: inline-flex;
              align-items: center;
              margin: auto;
              gap: 1rem;">
          <img src="$appUrl/storage/defaults/logo.png" style="width: 8.5vw;">
          <h2 style="color:#f1f1f1">Service Electronic</h2>
        </div>
        <div style="
              width: 70vw;
              margin: 30px auto;
              background-color: #ffffffed;
              border-radius: 5px;
              padding: 20px 30px;">
          <h1 style="font-size: 24px; margin: 0.5rem 2rem 1rem 0;">
            Account Withdraw <-answer->
          </h1>
          <div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
            <div style="margin: 10px 20px;">
              You Request to withdraw from your account balance (<span style="color: rgb(204, 0, 0);"><-sended_balance-></span>) as (<span style="color: #0c0;"><-received_balance-></span>) at <span style="color: #06c;"><-withdraw_date->.</span> has been <-answer->
              <div style="font-weight: bold; font-size: 13px; margin: 10px 0 10px 10px;">
                Request Id: <span style="font-weight: normal;">#<-request_id->.</span><br>
                Sended Currency: <span style="font-weight: normal;"><-sended_currency->.</span><br>
                Sended Balance: <span style="font-weight: normal;"><-sended_balance->.</span><br>
                Received Currency: <span style="font-weight: normal;"><-received_currency->.</span><br>
                Received Balance: <span style="font-weight: normal;"><-received_balance->.</span><br>
                Withdrawed At: <span style="font-weight: normal;"><-withdraw_date->.</span><br>
                Wallet: <span style="font-weight: normal;"><-wallet->.</span><br>
              </div>
            </div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
          </div>
          <p style="
                width: fit-content;
                margin: 15px auto;
                font-size: 12px;
                text-align: center;">
            Service Electronic<br>Powred by Abdo Pr
          </p>
        </div>
        <div style="display: grid;">
          <div style="
                  width: fit-content;
                  display: inline-flex;
                  align-items: center;
                  margin: auto;">
            <a href="https://t.me/+pwKpK4YhXHEyYjRk?fbclid=IwAR3lWzl_bZtLedJU4pbkLNgt42G-bEVDSAYrvG9o1GK_vWV0Rxwn-Vz0xsY"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Telegram_Messenger.png/1020px-Telegram_Messenger.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Telegram</h2>
            </a>
            <a href="https://www.facebook.com/ChihabR94"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1024px-Facebook_Logo_%282019%29.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Facebook</h2>
            </a>
          </div>
        </div>
      </div>
    </div>
    HTML;
    Template::create([
      'name' => 'user-recharge',
      'content' => $userRechargeContent,
      'args' => [
        ["name" => '<-request_id->', "type" => "text"],
        ["name" => '<-sended_currency->', "type" => "text"],
        ["name" => '<-sended_balance->', "type" => "number"],
        ["name" => '<-received_currency->', "type" => "text"],
        ["name" => '<-received_balance->', "type" => "number"],
        ["name" => '<-recharge_date->', "type" => "datetime"],
        ["name" => '<-wallet->', "type" => "text"],
        ["name" => '<-answer->', "type" => "text"],
      ],
      'type' => 'mail',
      'unreades' => Admin::unreades(),
    ]);
    $userWithdrawContent = <<<HTML
      <div style="
          font: 15px cursive;
          background-image: linear-gradient(315deg, #12a5ff, #51d456);
          width: 100%;
          height: -webkit-fill-available;
          padding: 42px 0px;
          color: rgb(42, 42, 42);">
      <div style="display: grid;">
        <div style="
              width: fit-content;
              display: inline-flex;
              align-items: center;
              margin: auto;
              gap: 1rem;">
          <img src="$appUrl/storage/defaults/logo.png" style="width: 8.5vw;">
          <h2 style="color:#f1f1f1">Service Electronic</h2>
        </div>
        <div style="
              width: 70vw;
              margin: 30px auto;
              background-color: #ffffffed;
              border-radius: 5px;
              padding: 20px 30px;">
          <h1 style="font-size: 24px; margin: 0.5rem 2rem 1rem 0;">
            Account Withdraw <-answer->
          </h1>
          <div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
            <div style="margin: 10px 20px;">
              You Request to withdraw from your account balance (<span style="color: rgb(204, 0, 0);"><-sended_balance-></span>) as (<span style="color: #0c0;"><-received_balance-></span>) at <span style="color: #06c;"><-withdraw_date->.</span> has been <-answer->
              <div style="font-weight: bold; font-size: 13px; margin: 10px 0 10px 10px;">
                Request Id: <span style="font-weight: normal;">#<-request_id->.</span><br>
                Sended Currency: <span style="font-weight: normal;"><-sended_currency->.</span><br>
                Sended Balance: <span style="font-weight: normal;"><-sended_balance->.</span><br>
                Received Currency: <span style="font-weight: normal;"><-received_currency->.</span><br>
                Received Balance: <span style="font-weight: normal;"><-received_balance->.</span><br>
                Withdrawed At: <span style="font-weight: normal;"><-withdraw_date->.</span><br>
                To Wallet: <span style="font-weight: normal;"><-to_wallet->.</span><br>
              </div>
            </div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
          </div>
          <p style="
                width: fit-content;
                margin: 15px auto;
                font-size: 12px;
                text-align: center;">
            Service Electronic<br>Powred by Abdo Pr
          </p>
        </div>
        <div style="display: grid;">
          <div style="
                  width: fit-content;
                  display: inline-flex;
                  align-items: center;
                  margin: auto;">
            <a href="https://t.me/+pwKpK4YhXHEyYjRk?fbclid=IwAR3lWzl_bZtLedJU4pbkLNgt42G-bEVDSAYrvG9o1GK_vWV0Rxwn-Vz0xsY"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Telegram_Messenger.png/1020px-Telegram_Messenger.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Telegram</h2>
            </a>
            <a href="https://www.facebook.com/ChihabR94"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1024px-Facebook_Logo_%282019%29.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Facebook</h2>
            </a>
          </div>
        </div>
      </div>
    </div>
    HTML;
    Template::create([
      'name' => 'user-withdraw',
      'content' => $userWithdrawContent,
      'args' => [
        ["name" => '<-request_id->', "type" => "text"],
        ["name" => '<-sended_currency->', "type" => "text"],
        ["name" => '<-sended_balance->', "type" => "number"],
        ["name" => '<-received_currency->', "type" => "text"],
        ["name" => '<-received_balance->', "type" => "number"],
        ["name" => '<-withdraw_date->', "type" => "datetime"],
        ["name" => '<-to_wallet->', "type" => "text"],
        ["name" => '<-answer->', "type" => "text"],
      ],
      'type' => 'mail',
      'unreades' => Admin::unreades(),
    ]);
    $creditReceiveContent = <<<HTML
      <div style="
          font: 15px cursive;
          background-image: linear-gradient(315deg, #12a5ff, #51d456);
          width: 100%;
          height: -webkit-fill-available;
          padding: 42px 0px;
          color: rgb(42, 42, 42);">
      <div style="display: grid;">
        <div style="
              width: fit-content;
              display: inline-flex;
              align-items: center;
              margin: auto;
              gap: 1rem;">
          <img src="$appUrl/storage/defaults/logo.png" style="width: 8.5vw;">
          <h2 style="color:#f1f1f1">Service Electronic</h2>
        </div>
        <div style="
              width: 70vw;
              margin: 30px auto;
              background-color: #ffffffed;
              border-radius: 5px;
              padding: 20px 30px;">
          <h1 style="font-size: 24px; margin: 0.5rem 2rem 1rem 0;">
            Credit Received
          </h1>
          <div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
            <div style="margin: 10px 20px;">
              You have received credit from (<span style="color: rgb(204, 0, 0);"><-from-></span>)
              with the value of (<span style="color: #0c0;"><-balance-></span>)
              at <span style="color: #06c;"><-datetime->.</span>
              <div style="font-weight: bold; font-size: 13px; margin: 10px 0 10px 10px;">
                Exchange Id: <span style="font-weight: normal;">#<-exchange_id->.</span><br>
                From: <span style="font-weight: normal;"><-from->.</span><br>
                Balance: <span style="font-weight: normal;"><-balance->.</span><br>
                Sended At: <span style="font-weight: normal;"><-datime->.</span><br>
              </div>
            </div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
          </div>
          <p style="
                width: fit-content;
                margin: 15px auto;
                font-size: 12px;
                text-align: center;">
            Service Electronic<br>Powred by Abdo Pr
          </p>
        </div>
        <div style="display: grid;">
          <div style="
                  width: fit-content;
                  display: inline-flex;
                  align-items: center;
                  margin: auto;">
            <a href="https://t.me/+pwKpK4YhXHEyYjRk?fbclid=IwAR3lWzl_bZtLedJU4pbkLNgt42G-bEVDSAYrvG9o1GK_vWV0Rxwn-Vz0xsY"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Telegram_Messenger.png/1020px-Telegram_Messenger.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Telegram</h2>
            </a>
            <a href="https://www.facebook.com/ChihabR94"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1024px-Facebook_Logo_%282019%29.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Facebook</h2>
            </a>
          </div>
        </div>
      </div>
    </div>
    HTML;
    Template::create([
      'name' => 'credit-receive',
      'content' => $creditReceiveContent,
      'args' => [
        ["name" => '<-exchange_id->', "type" => "text"],
        ["name" => '<-from->', "type" => "text"],
        ["name" => '<-balance->', "type" => "number"],
        ["name" => '<-datetime->', "type" => "datetime"],
      ],
      'type' => 'mail',
      'unreades' => Admin::unreades(),
    ]);
    $identityConfirmContent = <<<HTML
      <div style="
          font: 15px cursive;
          background-image: linear-gradient(315deg, #12a5ff, #51d456);
          width: 100%;
          height: -webkit-fill-available;
          padding: 42px 0px;
          color: rgb(42, 42, 42);">
      <div style="display: grid;">
        <div style="
              width: fit-content;
              display: inline-flex;
              align-items: center;
              margin: auto;
              gap: 1rem;">
          <img src="$appUrl/storage/defaults/logo.png" style="width: 8.5vw;">
          <h2 style="color:#f1f1f1">Service Electronic</h2>
        </div>
        <div style="
              width: 70vw;
              margin: 30px auto;
              background-color: #ffffffed;
              border-radius: 5px;
              padding: 20px 30px;">
          <h1 style="font-size: 24px; margin: 0.5rem 2rem 1rem 0;">
            Identity Confirmation <-answer->
          </h1>
          <div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
            <div style="margin: 10px 20px;">
              Your request to confirm your identity has been <-answer->
              at <span style="color: #06c;"><-datetime->.</span>
              <div style="font-weight: bold; font-size: 13px; margin: 10px 0 10px 10px;">
                <-answer_description->
              </div>
            </div>
            <div style="height: 0.1px; width: 98%; background-color: #ccc; margin: auto;"> </div>
          </div>
          <p style="
                width: fit-content;
                margin: 15px auto;
                font-size: 12px;
                text-align: center;">
            Service Electronic<br>Powred by Abdo Pr
          </p>
        </div>
        <div style="display: grid;">
          <div style="
                  width: fit-content;
                  display: inline-flex;
                  align-items: center;
                  margin: auto;">
            <a href="https://t.me/+pwKpK4YhXHEyYjRk?fbclid=IwAR3lWzl_bZtLedJU4pbkLNgt42G-bEVDSAYrvG9o1GK_vWV0Rxwn-Vz0xsY"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Telegram_Messenger.png/1020px-Telegram_Messenger.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Telegram</h2>
            </a>
            <a href="https://www.facebook.com/ChihabR94"
                style="
                display: grid;
                margin: 0 10px;
                text-decoration: none;
                color: #282828;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1024px-Facebook_Logo_%282019%29.png"
                    style="
                    background-color: #fff;
                    border-radius: 50px;
                    padding: 3px;
                    margin: auto;
                    width: 6vw;">
              <h2 style="font-size: 16px;">Facebook</h2>
            </a>
          </div>
        </div>
      </div>
    </div>
    HTML;
    Template::create([
      'name' => 'identity-confirm',
      'content' => $identityConfirmContent,
      'args' => [
        ["name" => '<-datetime->', "type" => "datetime"],
        ["name" => '<-answer_description->', "type" => "text"],
        ["name" => '<-answer->', "type" => "text"],
      ],
      'type' => 'mail',
      'unreades' => Admin::unreades(),
    ]);
  }
}
