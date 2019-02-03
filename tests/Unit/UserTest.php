<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Post;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $userFirst;
    protected $userSecond;
    protected $countPostFirstUser;
    protected $countPostSecondUser;

    public function setUp()
    {
        parent::setUp();
        $this->userFirst = factory(User::class)->create();
        $this->userSecond = factory(User::class)->create();
        $this->countPostFirstUser = 10;
        $this->countPostSecondUser = 10;
    }

    public function testSetActiveLastInactivePostUser()
    {
        $lastButOnePost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $lastPost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->makeActiveLastInactivePost();
        $lastPostInDb = Post::find($lastPost->id)->first();
        $this->assertTrue($lastPostInDb->isActive());
    }

    public function testInactiveLastButOnePostUser()
    {
        $lastButOnePost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $lastPost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->makeActiveLastInactivePost();
        $lastButOnePostInDb = Post::find($lastButOnePost->id)->first();
        $this->assertTrue($lastButOnePostInDb->isNotActive());
    }

    public function testActivateLastButOnePostUserWithLastActivePost()
    {
        $lastButOnePost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $lastPost = factory(Post::class)->states('active')->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->makeActiveLastInactivePost();
        $lastButOnePostInDb = Post::find($lastButOnePost->id)->first();
        $this->assertTrue($lastButOnePostInDb->isActive());
    }

    public function testInfluenceMethodMakeActiveLastInactivePostOnAnotherUser()
    {
        factory(Post::class, $this->countPostFirstUser)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, $this->countPostSecondUser)->create(['user_id' => $this->userSecond->id]);
        $this->userFirst->makeActiveLastInactivePost();
        $countInactivePostSecondUser = User::find($this->userSecond->id)->posts()->inactive()->count();
        $this->assertEquals($countInactivePostSecondUser, $this->countPostSecondUser);
    }

    public function testInfluenceMethodDeleteInactivePostOnAnotherUser()
    {
        factory(Post::class, $this->countPostFirstUser)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, $this->countPostSecondUser)->create(['user_id' => $this->userSecond->id]);
        $this->userFirst->deleteInactivePost();
        $countInactivePostSecondUser = User::find($this->userSecond->id)->posts()->inactive()->count();
        $this->assertEquals($countInactivePostSecondUser, $this->countPostSecondUser);
    }

    public function testDeleteInactivePost()
    {
        factory(Post::class, $this->countPostFirstUser)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, $this->countPostSecondUser)->states('active')->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->deleteInactivePost();
        $countInactivePostSecondUser = User::find($this->userFirst->id)->posts()->inactive()->count();
        $trueValue = 0;
        $this->assertEquals($countInactivePostSecondUser, $trueValue);
    }

    public function testSaveActivePost()
    {
        factory(Post::class, $this->countPostFirstUser)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, $this->countPostSecondUser)->states('active')->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->deleteInactivePost();
        $countInactivePostSecondUser = User::find($this->userFirst->id)->posts()->active()->count();
        $this->assertEquals($countInactivePostSecondUser, $this->countPostSecondUser);
    }
}
