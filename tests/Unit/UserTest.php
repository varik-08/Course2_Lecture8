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

    public function setUp()
    {
        parent::setUp();
        $this->userFirst = factory(User::class)->create();
        $this->userSecond = factory(User::class)->create();
    }

    public function testSetActiveLastInactivePostUser()
    {
        $lastButOnePost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $lastPost = factory(Post::class)->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->makeActiveLastInactivePost();
        $lastButOnePostInDb = Post::find($lastButOnePost->id)->first();
        $lastPostInDb = Post::find($lastPost->id)->first();
        $test = $lastButOnePostInDb->isNotActive() && $lastPostInDb->isActive() ? true : false;
        $this->assertTrue($test);
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
        factory(Post::class, 10)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, 10)->create(['user_id' => $this->userSecond->id]);
        $this->userFirst->makeActiveLastInactivePost();
        $countInactivePostSecondUser = User::find($this->userSecond->id)->posts()->inactive()->count();
        $trueValue = 10;
        $this->assertEquals($countInactivePostSecondUser, $trueValue);
    }

    public function testInfluenceMethodDeleteInactivePostOnAnotherUser()
    {
        factory(Post::class, 10)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, 10)->create(['user_id' => $this->userSecond->id]);
        $this->userFirst->deleteInactivePost();
        $countInactivePostSecondUser = User::find($this->userSecond->id)->posts()->inactive()->count();
        $trueValue = 10;
        $this->assertEquals($countInactivePostSecondUser, $trueValue);
    }

    public function testDeleteInactivePost()
    {
        factory(Post::class, 10)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, 10)->states('active')->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->deleteInactivePost();
        $countInactivePostSecondUser = User::find($this->userFirst->id)->posts()->inactive()->count();
        $trueValue = 0;
        $this->assertEquals($countInactivePostSecondUser, $trueValue);
    }

    public function testSaveActivePost()
    {
        factory(Post::class, 10)->create(['user_id' => $this->userFirst->id]);
        factory(Post::class, 10)->states('active')->create(['user_id' => $this->userFirst->id]);
        $this->userFirst->deleteInactivePost();
        $countInactivePostSecondUser = User::find($this->userFirst->id)->posts()->active()->count();
        $trueValue = 10;
        $this->assertEquals($countInactivePostSecondUser, $trueValue);
    }
}
