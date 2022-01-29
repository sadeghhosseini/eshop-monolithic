<?php

use function Pest\Laravel\patch;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\u;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);
$url = '/api/users/{id}';
beforeAll(function () use ($url) {
    printEndpoint('PATCH', $url);
});


Tests\helpers\setupAuthorization(fn($closure) => beforeEach($closure));


it('updates own', function() use ($url) {
    $user = actAsUserWithPermission('edit-user(name)-own');
    $newName = User::factory()->make()->name;
    $response = patch(u($url, 'id', $user->id), ['name' => $newName]);
    $response->assertOk();
    expect(User::find($user->id)->name)->toEqual($newName);
});

it('returns 403 if user is not the owner', function() use ($url) {
    $user = actAsUserWithPermission('edit-user(name)-own');
    $anotherUser = User::factory()->create();
    $newName = User::factory()->make()->name;
    $response = patch(u($url, 'id', $anotherUser->id), ['name' => $newName]);
    $response->assertForbidden();
});

it('returns 403 if user does not have the right permission', function() use ($url) {
    $user = actAsUser();
    $newName = User::factory()->make()->name;
    $response = patch(u($url, 'id', $user->id), ['name' => $newName]);
    $response->assertForbidden();
});

it('returns 401 if user is not logged in', function() use ($url) {
    $user = User::factory()->create();
    $newName = User::factory()->make()->name;
    $response = patch(u($url, 'id', $user->id), ['name' => $newName]);
    $response->assertUnauthorized();
});