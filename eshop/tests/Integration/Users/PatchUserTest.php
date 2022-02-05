<?php


namespace Tests\Integration\Users;

use App\Models\User;
use Tests\MyTestCase;

class PatchUserTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/users/{id}';
    }


    /**
     * @testdox updates own
     */
    public function testUpdatesOwn()
    {
        $user = $this->actAsUserWithPermission('edit-user(name)-own');
        $newName = User::factory()->make()->name;
        $response = $this->rpatch(['id', $user->id], ['name' => $newName]);
        $response->assertOk();
        expect(User::find($user->id)->name)->toEqual($newName);
    }

    /**
     * @testdox returns 403 if user is not the owner
     */
    public function testReturns403IfUserIsNotTheOwner()
    {
        $user = $this->actAsUserWithPermission('edit-user(name)-own');
        $anotherUser = User::factory()->create();
        $newName = User::factory()->make()->name;
        $response = $this->rpatch(['id', $anotherUser->id], ['name' => $newName]);
        $response->assertForbidden();
    }

    /**
     * @testdox returns 403 if user does not have the right permission
     */
    public function testReturns403IfUserDoesNotHaveTheRightPermission()
    {
        $user = $this->actAsUser();
        $newName = User::factory()->make()->name;
        $response = $this->rpatch(['id', $user->id], ['name' => $newName]);
        $response->assertForbidden();
    }

    /**
     * @testdox returns 401 if user is not logged in
     */
    public function testReturns401IfUserIsNotLoggedIn()
    {
        $user = User::factory()->create();
        $newName = User::factory()->make()->name;
        $response = $this->rpatch(['id', $user->id], ['name' => $newName]);
        $response->assertUnauthorized();
    }
}
