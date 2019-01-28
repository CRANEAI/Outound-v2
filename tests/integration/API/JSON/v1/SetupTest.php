<?php
namespace MailPoet\Test\API\JSON\v1;

use Codeception\Stub;
use MailPoet\Models\Setting;
use MailPoet\API\JSON\v1\Setup;
use MailPoet\WP\Functions as WPFunctions;
use Helper\WordPressHooks as WPHooksHelper;
use MailPoet\API\JSON\Response as APIResponse;

class SetupTest extends \MailPoetTest {
  function _before() {
    Setting::setValue('signup_confirmation.enabled', false);
  }

  function testItCanReinstall() {
    $wp = Stub::make(new WPFunctions, [
      'doAction' => asCallable([WPHooksHelper::class, 'doAction'])
    ]);

    $router = new Setup($wp);
    $response = $router->reset();
    expect($response->status)->equals(APIResponse::STATUS_OK);

    $signup_confirmation = Setting::getValue('signup_confirmation.enabled');
    expect($signup_confirmation)->true();

    $hook_name = 'mailpoet_setup_reset';
    expect(WPHooksHelper::isActionDone($hook_name))->true();
  }

  function _after() {
    Setting::deleteMany();
  }
}
