<?php


namespace Tests\Integration\Users;

use App\Models\User;
use Tests\MyTestCase;

class GetUserTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/users/{id}';
    }


    /**
     * @testdox gets any user if has view-user-any permission
     */
    public function testGetsAnyUserIfHasViewUserAnyPermission()
    {
        $user = $this->actAsUserWithPermission('view-user-any');
        $anotherUser = User::factory()->create();
        $response = $this->rget(['id', $anotherUser->id]);
        $response->assertOk();
    }

    /**
     * @testdox returns 403 if a user with view-user-own tries to get another user
     */
    public function testReturns403IfAUserWithViewUserOwnTriesToGetAnotherUser()
    {
        $user = $this->actAsUserWithPermission('view-user-own');
        $anotherUser = User::factory()->create();
        $response = $this->rget(['id', $anotherUser->id]);
        $response->assertForbidden();
    }

    /**
     * @testdox returns 403 if user has nor view-user-own neither view-user-any
     */
    public function testReturns403IfUserHasNorViewUserOwnNeitherViewUserAny()
    {
        $user = $this->actAsUser();
        $anotherUser = User::factory()->create();
        $response = $this->rget(['id', $anotherUser->id]);
        $response->assertForbidden();
    }

    /**
     * @testdox returns 200 if a user with view-user-own tries to get him/her-self
     */
    public function testReturns200IfAUserWithViewUserOwnTriesToGetHimHerSelf()
    {
        $user = $this->actAsUserWithPermission('view-user-own');
        $response = $this->rget(['id', $user->id]);
        $response->assertOk();
        expect($response->baseResponse->content())->json()
            ->id->toEqual($user->id);
    }

    /**
     * @testdox returns 401 if user not signed in
     */
    public function testReturns401IfUserNotSignedIn()
    {
        $anotherUser = User::factory()->create();
        $response = $this->rget(['id', $anotherUser->id]);
        $response->assertUnauthorized();
    }
}
