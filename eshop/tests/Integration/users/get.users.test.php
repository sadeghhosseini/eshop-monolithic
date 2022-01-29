<?php

use function Pest\Laravel\get;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use App\Models\User;
use function Tests\helpers\u;

use function Tests\helpers\printEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/users/{id}';
beforeAll(function () use ($url) {
    printEndpoint('GET', $url);
});
Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));

it('gets any user if has view-user-any permission', function() use ($url) {
    $user = actAsUserWithPermission('view-user-any');
    $anotherUser = User::factory()->create();
    $response = get(u($url, 'id', $anotherUser->id));
    $response->assertOk();
});
it('returns 403 if a user with view-user-own tries to get another user', function() use ($url) {
    $user = actAsUserWithPermission('view-user-own');
    $anotherUser = User::factory()->create();
    $response = get(u($url, 'id', $anotherUser->id));
    $response->assertForbidden();
});
it('returns 403 if user has nor view-user-own neither view-user-any', function() use ($url) {
    $user = actAsUser();
    $anotherUser = User::factory()->create();
    $response = get(u($url, 'id', $anotherUser->id));
    $response->assertForbidden();
});
it('returns 200 if a user with view-user-own tries to get him/her-self', function() use ($url) {
    $user = actAsUserWithPermission('view-user-own');
    $response = get(u($url, 'id', $user->id));
    $response->assertOk();
    expect($response->baseResponse->content())->json()
        ->id->toEqual($user->id);
});

it('returns 401 if user not signed in', function() use ($url) {
    $anotherUser = User::factory()->create();
    $response = get(u($url, 'id', $anotherUser->id));
    $response->assertUnauthorized();
});