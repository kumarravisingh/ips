<?php

namespace App\Repositories;

use App\Contracts\InfusionsoftContract;
use App\Tag;

class TagRepository
{

    /**
     * return tags getting it from db or infusionsoft
     *
     * @param InfusionsoftContract $infusionsoftContract
     * @return Tag[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */

    public function getTags(InfusionsoftContract $infusionsoftContract){

        $tags = Tag::all();

        if($tags->isEmpty()){
            $tags = $this->getTagFromInfusionSoft($infusionsoftContract);
            $this->saveTags($tags);
        }

        return $tags;
    }

    /**
     * @param InfusionsoftContract $infusionsoftContract
     * @return \Illuminate\Support\Collection
     */
    public function getTagFromInfusionSoft(InfusionsoftContract $infusionsoftContract){
        return $infusionsoftContract->getTagsFromInfusionsoftApi();
    }

    /**
     * save tags into database
     * @param $tags
     */
    public function saveTags($tags){
        foreach ($tags as $tag){
            Tag::updateOrCreate(
                [
                    'id'    =>  $tag->id
                ],
                [
                    'id'            =>  $tag->id,
                    'name'          =>  $tag->name,
                    'description'   =>  $tag->description,
                    'category'      =>  json_encode($tag->category)
                // encoding for future use and due to complications since not
                // being used by the feature which we are creating
                ]);
        }
    }
}
