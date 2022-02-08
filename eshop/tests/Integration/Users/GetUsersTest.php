<?php


namespace Tests\Integration\Users;

use App\Helpers;
use App\Models\User;
use Tests\MyTestCase;

class GetUsersTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/users';
    }


    /**
     * @testdox returns 401 if user is not logged in
     */
    public function testReturns401IfUserIsNotLoggedIn()
    {
        $users = User::factory()->count(5)->create();
        $user = $this->actAsUserWithPermission('view-user-any');
        $response = $this->rget();
        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertIsArray($data);
        $this->assertCount(6, $data);
    }

    /**
     * @testdox returns 403 if user has no permission
     */
    public function testReturns403IfUserHasNoPermission()
    {
        $users = User::factory()->count(5)->create();
        $user = $this->actAsUserWithPermission('view-user-own');
        $response = $this->rget();
        $response->assertOk();

        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertIsArray($data);
        $this->assertEquals($user->id, $data['id']);
    }

    /**
     * @testdox returns own user with view-user-own permission
     */
    public function testReturnsOwnUserWithViewUserOwnPermission()
    {
        $users = User::factory()->count(5)->create();
        $user = $this->actAsUser();
        $response = $this->rget();
        $response->assertForbidden();
    }

    /**
     * @testdox returns all users for user with view-user-any permission
     */
    public function testReturnsAllUsersForUserWithViewUserAnyPermission()
    {
        $response = $this->rget();
        $response->assertUnauthorized();
    }
}
