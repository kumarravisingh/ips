<?php

namespace Tests\Unit;

use App\Http\Controllers\ApiController;
use App\Repositories\TagRepository;
use App\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetTagsFromDatabaseIfExistTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Saving tags to the database.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testSavingTagToDatabase(){

        $tags = Tag::all();
        $this->assertEquals(0,$tags->count());

        $tagRepositoryObject = new TagRepository();
        $multipleDummyTags = factory(Tag::class, 3)->create();

        $tagRepositoryObject->saveTags($multipleDummyTags);
        $tags = Tag::all();

        $this->assertNotEquals(0,$tags->count());
    }

    /**
     * Test  method is returning proper expected values with mocked method value.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGettingTagsFromInfusinSoftMethodReturnsDataAsExpected(){
        $tags = Tag::all();
        $this->assertEquals(0,$tags->count());
        $tagRepositoryObject = new TagRepository();

        $infusionsoftRepositoryMock = $this->createMock('App\Infusionsoft\InfusionsoftTag', 'App\Contracts\InfusionsoftTagContract');

        $dummyTagsForMatching = factory(Tag::class, 3)->make();
        $infusionsoftRepositoryMock->method('getTagsFromInfusionsoftApi')
            ->willReturn($dummyTagsForMatching);

        $tagsFromInfusionsoft = $tagRepositoryObject->getTagFromInfusionSoft($infusionsoftRepositoryMock);

        $this->assertNotEquals(0,$tagsFromInfusionsoft->count());
        $this->assertEquals($dummyTagsForMatching,$tagsFromInfusionsoft);
    }

    /**
     * Test saving infusionsoft tag data into database.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGettingTagsFromInfusionsoftAndSavingIntoDatabase(){

        $tags = Tag::all();
        $this->assertEquals(0,$tags->count());
        $tagRepositoryObject = new TagRepository();

        $infusionsoftRepositoryMock = $this->createMock('App\Infusionsoft\InfusionsoftTag', 'App\Contracts\InfusionsoftTagContract');

        $infusionsoftRepositoryMock->method('getTagsFromInfusionsoftApi')
            ->willReturn(factory(Tag::class, 3)->make());


        $tagRepositoryObject->getTags($infusionsoftRepositoryMock);
        $tags = Tag::all();
        $this->assertNotEquals(0,$tags->count());
        $this->assertDatabaseHas('tags',['id'=>1]);
    }

}
