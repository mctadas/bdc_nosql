<?php

namespace ViewModel\Content;

use \MongoId;
use BDC\Models\BaseModel;

class Content extends BaseModel {

    public function save(array $data)
    {
        $db = $this->get_connection();
        $coll = 'content';
        
        $db->$coll->insert($data);
    }

    public function get_by_category($category)
    {
        $db = $this->get_connection();
        $coll = 'content';
        
        return $db->$coll->find(array(
                'category' => $category
        ));
    }

    public function get_by_id($id)
    {
        $db = $this->get_connection();
        $coll = 'content';
        
        $doc = $db->$coll->findOne(array(
                '_id' => new MongoId($id)
        ));
        return $doc;
    }

    public function get_by_uri($uri)
    {
        $db = $this->get_connection();
        $coll = 'content';
        
        $doc = $db->$coll->findOne(array(
                'uri' => array(
                        '$regex' => $uri
                )
        ));
        return $doc;
    }

    public function update($content, $uri, $category)
    {
        $db = $this->get_connection();
        $coll = 'content';
        $push = array(
                '$set' => array(
                        'body' => $content,
                        'uri' => $uri,
                        'date' => time(),
                        'category' => $category,
                )
                
        );
        $a = $db->$coll->update(array(
                'uri' => array(
                        '$regex' => $uri
                )
        ), $push, array(
                "upsert" => true
        ));
    }
}
