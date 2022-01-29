<?php

use function Pest\Laravel\get;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\getResponseBodyAsArray;

use App\Models\User;
use function Tests\helpers\u;

use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/users';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});
Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('returns all users for user with view-user-any permission', function() use ($url) {
    $users = User::factory()->count(5)->create();
    $user = actAsUserWithPermission('view-user-any');
    $response = get($url);
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect(is_array($body))->toBeTrue();
    expect(count($body))->toEqual(6);
});

it('returns own user with view-user-own permission', function() use ($url) {
    $users = User::factory()->count(5)->create();
    $user = actAsUserWithPermission('view-user-own');
    $response = get($url);
    $response->assertOk();
    $body = getResponseBodyAsArray($response);
    expect(is_array($body))->toBeFalse();
    expect($body->id)->toEqual($user->id);
});

it('returns 403 if user has no permission', function() use ($url) {
    $users = User::factory()->count(5)->create();
    $user = actAsUser();
    $response = get($url);
    $response->assertForbidden();
});

it('returns 401 if user is not logged in', function() use ($url) {
    $response = get($url);
    $response->assertUnauthorized();
});