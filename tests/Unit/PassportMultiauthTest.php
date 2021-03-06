<?php

namespace SMartins\PassportMultiauth\Tests\Unit;

use Exception;
use SMartins\PassportMultiauth\Tests\TestCase;
use SMartins\PassportMultiauth\PassportMultiauth;
use SMartins\PassportMultiauth\Tests\Fixtures\Models\User;
use SMartins\PassportMultiauth\Tests\Fixtures\Models\Customer;

class PassportMultiauthTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'passport']);

        $this->artisan('migrate');

        $this->withFactories(__DIR__.'/../Fixtures/factories');

        $this->setAuthConfigs();
    }

    public function testActingAsWithScopes()
    {
        $user = factory(User::class)->create();

        $scopes = ['check-scopes', 'test-packages'];
        PassportMultiauth::actingAs($user, $scopes);

        foreach ($scopes as $scope) {
            $this->assertTrue($user->tokenCan($scope));
        }
    }

    public function testGetUserProviderWithModelNotExistentOnProviders()
    {
        $model = new Customer;

        $provider = PassportMultiauth::getUserProvider($model);

        $this->assertNull($provider);
    }

    public function testActingAsWithUserThatNotUsesHasApiTokens()
    {
        $this->expectException(Exception::class);

        PassportMultiauth::actingAs(new Customer);
    }

    public function testGetProviderGuardWithNotPassportDriver()
    {
        config(['auth.guards.customer.driver' => 'token']);
        config(['auth.guards.customer.provider' => 'customers']);

        config(['auth.providers.customers.driver' => 'eloquent']);
        config(['auth.providers.customers.model' => Customer::class]);

        $guard = PassportMultiauth::getProviderGuard('customers');

        $this->assertNull($guard);
    }
}
