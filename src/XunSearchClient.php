<?php

namespace Eriodesign\Scout;

class XunSearchClient
{
    protected $xunsearch;
    protected $_indexName;
    
    public function __construct($name = null)
    {
        if(empty($name) && $this->_indexName != $name){
            $this->_indexName = $name;
            $this->newIndex();
        }
    }

    public function createIndex($name,$options = [])
    {
        $this->_indexName = $name;
        $this->newIndex();
        return $this;
    }

    public function task($name)
    {
        if($this->_indexName != $name){
            $this->_indexName = $name;
            $this->newIndex();
        }
        return $this;
    }

    public function refresh($name)
    {
        $this->_indexName = $name;
        $this->newIndex();
        return $this;
    }

    /**
     * 切换文件
     */
    public function newIndex()
    {
        $path = config('plugin.eriodesign.scout.app.xunsearch.path');
        $file = $path.$this->_indexName.'.ini';
        if (!is_dir($path) && !is_file($file)) {
            return throw new \Exception('xunsearch ini not found');
        }
        $this->xunsearch = new \XS($file);
        return $this;
    }
    
    
    /**
     * Dynamically call the MeiliSearch client instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->xunsearch->$method(...$parameters);
    }
    
}