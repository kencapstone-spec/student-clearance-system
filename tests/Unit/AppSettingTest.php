<?php

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('it can set and get an app setting', function () {
    AppSetting::set('test_key', 'test_value');

    $value = AppSetting::get('test_key');

    expect($value)->toBe('test_value');
});

test('it returns default value if setting does not exist', function () {
    $value = AppSetting::get('non_existent_key', 'default_value');

    expect($value)->toBe('default_value');
});

test('it updates existing setting when set is called again', function () {
    AppSetting::set('test_key2', 'initial_value');
    AppSetting::set('test_key2', 'updated_value');

    $value = AppSetting::get('test_key2');

    expect($value)->toBe('updated_value');
});
